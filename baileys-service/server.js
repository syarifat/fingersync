const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const pino = require('pino');
const path = require('path');
const fs = require('fs');
const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore,
    Browsers
} = require('@whiskeysockets/baileys');

const app = express();
const PORT = process.env.PORT || 3000;
const AUTH_DIR = path.join(__dirname, 'auth_info_baileys');

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

const logger = pino({ level: 'silent' });

let sock = null;
let connectionState = 'disconnected'; // 'disconnected' | 'connecting' | 'connected'
let qrCodeDataUrl = null;
let rawQr = null;
let pairingCode = null;
let userInfo = {
    id: null,
    name: '-',
    phone: '-'
};

// Ensure auth dir exists
if (!fs.existsSync(AUTH_DIR)) {
    fs.mkdirSync(AUTH_DIR, { recursive: true });
}

async function initWhatsApp(isRestart = false) {
    try {
        const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
        const { version, isLatest } = await fetchLatestBaileysVersion();
        
        console.log(`[Baileys] Menggunakan WA v${version.join('.')}, isLatest: ${isLatest}`);

        sock = makeWASocket({
            version,
            logger,
            printQRInTerminal: false,
            auth: {
                creds: state.creds,
                keys: makeCacheableSignalKeyStore(state.keys, logger)
            },
            browser: Browsers.ubuntu('Chrome'),
            generateHighQualityLinkPreview: true,
            syncFullHistory: false
        });

        connectionState = 'connecting';

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                rawQr = qr;
                try {
                    qrCodeDataUrl = await qrcode.toDataURL(qr, {
                        margin: 2,
                        scale: 8,
                        color: {
                            dark: '#1e293b',
                            light: '#ffffff'
                        }
                    });
                    console.log('[Baileys] QR code baru telah digenerate');
                } catch (err) {
                    console.error('[Baileys] Gagal generate QR data URL:', err);
                }
            }

            if (connection === 'close') {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
                
                console.log(`[Baileys] Koneksi terputus. Status Code: ${statusCode}, Reconnect: ${shouldReconnect}`);
                
                connectionState = 'disconnected';
                qrCodeDataUrl = null;
                rawQr = null;
                pairingCode = null;
                userInfo = { id: null, name: '-', phone: '-' };

                if (statusCode === DisconnectReason.loggedOut) {
                    console.log('[Baileys] Sesi telah logout. Menghapus folder auth...');
                    try {
                        fs.rmSync(AUTH_DIR, { recursive: true, force: true });
                    } catch (e) {
                        console.error('[Baileys] Gagal hapus auth dir:', e);
                    }
                    setTimeout(() => initWhatsApp(), 2000);
                } else if (shouldReconnect) {
                    setTimeout(() => initWhatsApp(), 3000);
                }
            } else if (connection === 'open') {
                connectionState = 'connected';
                qrCodeDataUrl = null;
                rawQr = null;
                pairingCode = null;

                const userJid = sock.user?.id || '';
                const phone = userJid ? userJid.split(':')[0].replace(/[^0-9]/g, '') : '-';
                const name = sock.user?.name || sock.user?.notify || 'FingerSync Device';

                userInfo = {
                    id: userJid,
                    name: name,
                    phone: phone
                };

                console.log(`[Baileys] TERHUBUNG! Device: ${name} (${phone})`);
            }
        });

        sock.ev.on('creds.update', saveCreds);

    } catch (error) {
        console.error('[Baileys] Error inisialisasi WhatsApp:', error);
        connectionState = 'disconnected';
        setTimeout(() => initWhatsApp(), 5000);
    }
}

// ==========================================
// REST API ENDPOINTS
// ==========================================

// 1. Cek Status Koneksi & Device
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        status: connectionState, // 'connected' | 'disconnected' | 'connecting'
        name: userInfo.name,
        phone: userInfo.phone,
        qr: qrCodeDataUrl,
        pairingCode: pairingCode
    });
});

// 2. Request Pairing Code (8 digit)
app.post('/api/pair-code', async (req, res) => {
    let { phone } = req.body;
    if (!phone) {
        return res.status(400).json({ success: false, message: 'Nomor telepon harus diisi' });
    }

    if (connectionState === 'connected') {
        return res.status(400).json({ success: false, message: 'WhatsApp sudah dalam kondisi terhubung' });
    }

    try {
        // Normalisasi nomor (contoh 08123... -> 628123...)
        phone = phone.replace(/[^0-9]/g, '');
        if (phone.startsWith('0')) {
            phone = '62' + phone.substring(1);
        }

        if (!sock) {
            await initWhatsApp();
        }

        // Tunggu socket ready jika sedang connecting
        let attempts = 0;
        while ((!sock || !sock.requestPairingCode) && attempts < 10) {
            await new Promise((r) => setTimeout(r, 500));
            attempts++;
        }

        const code = await sock.requestPairingCode(phone);
        // Format code ABCD-EFGH for better readability
        const formattedCode = code?.match(/.{1,4}/g)?.join('-') || code;
        pairingCode = formattedCode;

        console.log(`[Baileys] Pairing Code untuk ${phone}: ${formattedCode}`);

        return res.json({
            success: true,
            code: formattedCode,
            phone: phone
        });
    } catch (err) {
        console.error('[Baileys] Gagal membuat pairing code:', err);
        return res.status(500).json({
            success: false,
            message: 'Gagal membuat pairing code: ' + (err.message || err)
        });
    }
});

// 3. Kirim Pesan WhatsApp
app.post('/api/send-message', async (req, res) => {
    const { target, message } = req.body;

    if (!target || !message) {
        return res.status(400).json({
            success: false,
            message: 'Target (nomor/grup) dan message wajib diisi'
        });
    }

    // Tunggu sebentar jika socket sedang dalam proses connecting/handshake
    let waitAttempts = 0;
    while (connectionState === 'connecting' && waitAttempts < 12) {
        await new Promise((r) => setTimeout(r, 500));
        waitAttempts++;
    }

    if (connectionState !== 'connected' || !sock) {
        return res.status(503).json({
            success: false,
            message: 'WhatsApp belum terhubung (Status: ' + connectionState + ')'
        });
    }

    try {
        let jid = target.trim();

        // Format target
        if (jid.endsWith('@g.us') || jid.endsWith('@s.whatsapp.net')) {
            // Sudah dalam format JID lengkap
        } else {
            // Nomor pribadi: bersihkan karakter non-digit
            let cleanNumber = jid.replace(/[^0-9]/g, '');
            if (cleanNumber.startsWith('0')) {
                cleanNumber = '62' + cleanNumber.substring(1);
            }
            jid = cleanNumber + '@s.whatsapp.net';
        }

        const result = await sock.sendMessage(jid, { text: message });

        return res.json({
            success: true,
            target: jid,
            messageId: result?.key?.id || null
        });
    } catch (err) {
        console.error(`[Baileys] Gagal mengirim pesan ke ${target}:`, err);
        return res.status(500).json({
            success: false,
            message: err.message || 'Gagal mengirim pesan'
        });
    }
});

// 4. Ambil Daftar Grup WhatsApp
app.get('/api/groups', async (req, res) => {
    if (connectionState !== 'connected' || !sock) {
        return res.json({
            success: false,
            data: [],
            message: 'WhatsApp belum terhubung'
        });
    }

    try {
        const groupsObj = await sock.groupFetchAllParticipating();
        const groups = Object.values(groupsObj).map(g => ({
            id: g.id,
            name: g.subject || 'Tanpa Nama'
        }));

        // Urutkan berdasarkan nama grup
        groups.sort((a, b) => a.name.localeCompare(b.name));

        return res.json({
            success: true,
            data: groups
        });
    } catch (err) {
        console.error('[Baileys] Gagal mengambil daftar grup:', err);
        return res.status(500).json({
            success: false,
            data: [],
            message: err.message || 'Gagal mengambil grup'
        });
    }
});

// 5. Logout & Bersihkan Sesi
app.post('/api/logout', async (req, res) => {
    try {
        if (sock) {
            try {
                await sock.logout();
            } catch (e) {
                // Abaikan error saat logout
            }
        }
        
        try {
            fs.rmSync(AUTH_DIR, { recursive: true, force: true });
        } catch (e) {
            // Abaikan error
        }

        connectionState = 'disconnected';
        qrCodeDataUrl = null;
        rawQr = null;
        pairingCode = null;
        userInfo = { id: null, name: '-', phone: '-' };

        // Restart socket untuk generate QR baru
        setTimeout(() => initWhatsApp(), 1000);

        return res.json({
            success: true,
            message: 'Sesi WhatsApp berhasil dihapus. Silakan scan ulang.'
        });
    } catch (err) {
        return res.status(500).json({
            success: false,
            message: err.message || 'Gagal logout'
        });
    }
});

// 6. Restart Koneksi
app.post('/api/restart', async (req, res) => {
    try {
        if (sock) {
            sock.end(undefined);
        }
        setTimeout(() => initWhatsApp(), 1000);
        return res.json({ success: true, message: 'Koneksi WhatsApp sedang direstart' });
    } catch (err) {
        return res.status(500).json({ success: false, message: err.message });
    }
});

// Jalankan Server & Inisialisasi WhatsApp
app.listen(PORT, () => {
    console.log(`[FingerSync Baileys Gateway] Berjalan pada http://localhost:${PORT}`);
    initWhatsApp();
});
