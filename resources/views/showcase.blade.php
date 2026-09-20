<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>FingerSync Expo 2026</title>
<link rel="icon" href="{{ asset('logo.png') }}?v=2" type="image/png">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/split-type@0.3.4/umd/index.min.js"></script>

@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;user-select:none;-webkit-user-select:none}
:root{
  --bg:#F5F4F0;
  --white:#FFFFFF;
  --ink:#111111;
  --mid:#555550;
  --line:#E2E0DA;
  --blue:#1D4ED8;
  --green:#15803D;
  --orange:#C2410C;
  --purple:#6D28D9;
  --yellow:#CA8A04;
  --blue-bg:#EFF6FF;
  --green-bg:#F0FDF4;
  --orange-bg:#FFF7ED;
  --purple-bg:#F5F3FF;
}
html,body{width:100%;height:100%;overflow:hidden;background:var(--bg)}
body{font-family:'Inter',sans-serif;color:var(--ink)}

#bg-canvas{position:fixed;inset:0;z-index:0;pointer-events:none;opacity:.5}

#root{position:relative;z-index:1;width:100vw;height:100vh;display:flex;flex-direction:column;overflow:hidden}

/* TOPBAR */
#topbar{
  height:52px;flex-shrink:0;
  display:flex;align-items:center;justify-content:space-between;
  padding:0 28px;
  border-bottom:1.5px solid var(--line);
  background:rgba(245,244,240,.9);
  backdrop-filter:blur(16px);
}
.brand{display:flex;align-items:center;gap:9px}
.brand-logo{width:26px;height:26px;border-radius:7px;border:1.5px solid var(--line);object-fit:contain;padding:3px;background:white}
.brand-name{font-size:13px;font-weight:800;letter-spacing:.1em;color:var(--ink)}
.brand-badge{font-family:'DM Mono',monospace;font-size:9px;padding:2px 7px;border-radius:100px;border:1.5px solid var(--blue);color:var(--blue);letter-spacing:.08em}
.controls{display:flex;align-items:center;gap:6px}
.ctrl{
  font-family:'DM Mono',monospace;font-size:10px;
  padding:5px 11px;border-radius:6px;
  border:1.5px solid var(--line);background:white;
  color:var(--mid);cursor:pointer;
  transition:all .15s;
  text-decoration:none;display:inline-flex;align-items:center;gap:4px;
}
.ctrl:hover{border-color:var(--ink);color:var(--ink);background:var(--ink);color:white}
.ctrl.hi{border-color:var(--blue);color:var(--blue)}
.ctrl.hi:hover{background:var(--blue);color:white}

/* STAGE */
#stage{flex:1;position:relative;overflow:hidden}
.scene{
  position:absolute;inset:0;
  display:flex;flex-direction:column;
  align-items:center;justify-content:center;
  padding:28px 44px;
  visibility:hidden;
}
.scene.active{visibility:visible}
.si{width:100%;max-width:940px;display:flex;flex-direction:column;align-items:center}

/* FOOTER */
#footer{
  height:38px;flex-shrink:0;
  display:flex;align-items:center;justify-content:space-between;
  padding:0 28px;
  border-top:1.5px solid var(--line);
  background:rgba(245,244,240,.9);
  backdrop-filter:blur(16px);
}
.fl,.fr{display:flex;align-items:center;gap:8px;font-family:'DM Mono',monospace;font-size:9px;color:var(--mid)}
.live-dot{width:5px;height:5px;border-radius:50%;background:var(--green);box-shadow:0 0 5px var(--green);animation:lpulse 2s ease-in-out infinite}
@keyframes lpulse{0%,100%{opacity:1}50%{opacity:.3}}
.pips{display:flex;gap:4px}
.pip{width:18px;height:2px;border-radius:1px;background:var(--line);transition:all .3s}
.pip.on{background:var(--blue)}

/* TYPOGRAPHY */
.eyebrow{
  font-family:'DM Mono',monospace;font-size:10px;
  letter-spacing:.12em;text-transform:uppercase;
  color:var(--blue);margin-bottom:16px;opacity:0;
}
.big-title{
  font-size:clamp(46px,8.5vw,88px);font-weight:900;
  line-height:1;letter-spacing:-.03em;text-align:center;
  margin-bottom:18px;color:var(--ink);
}
.big-title .char{display:inline-block;will-change:transform,opacity}
.sub{
  font-size:clamp(13px,1.6vw,16px);color:var(--mid);
  text-align:center;max-width:580px;line-height:1.7;
  margin-bottom:26px;opacity:0;font-weight:400;
}

.col-blue{color:var(--blue)}
.col-green{color:var(--green)}
.col-orange{color:var(--orange)}
.col-purple{color:var(--purple)}

/* CHIPS */
.chip-row{display:flex;gap:8px;flex-wrap:wrap;justify-content:center;opacity:0}
.chip{
  display:flex;align-items:center;gap:9px;
  padding:9px 14px;border-radius:11px;
  border:1.5px solid var(--line);background:white;
  font-size:12px;box-shadow:0 1px 3px rgba(0,0,0,.06);
}
.chip-ico{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:14px}
.chip-val{font-weight:700;font-size:12px;color:var(--ink)}
.chip-sub{font-size:10px;color:var(--mid);font-family:'DM Mono',monospace}

/* PILLARS */
.pillars{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;width:100%;max-width:880px;margin-bottom:14px}
.pillar{
  border:1.5px solid var(--line);border-radius:12px;
  padding:14px;background:white;
  position:relative;overflow:hidden;
  opacity:0;transform:translateY(28px);
  box-shadow:0 1px 4px rgba(0,0,0,.05);
}
.pa{position:absolute;top:0;left:0;right:0;height:3px}
.pn{font-family:'DM Mono',monospace;font-size:9px;color:var(--mid);letter-spacing:.08em;margin-bottom:5px}
.pm{font-weight:700;font-size:13px;margin-bottom:3px;color:var(--ink)}
.pd{font-size:11px;color:var(--mid);line-height:1.5}
.warn{
  display:flex;align-items:center;gap:8px;
  padding:9px 14px;border-radius:9px;
  background:#FEF2F2;border:1.5px solid #FECACA;
  font-family:'DM Mono',monospace;font-size:11px;color:#B91C1C;
  width:100%;max-width:880px;opacity:0;
}

/* HW scene */
#scene-2 .si{flex-direction:row;gap:44px;align-items:center}
.hwl{flex:1;display:flex;flex-direction:column;gap:12px;align-items:flex-start}
.hwr{flex-shrink:0;width:260px}
.lcd-wrap{
  background:#181830;border-radius:18px;padding:18px;
  border:2px solid #25253C;
  box-shadow:0 20px 60px rgba(0,0,0,.15),0 0 30px rgba(29,78,216,.08);
  position:relative;
}
.sc{width:7px;height:7px;border-radius:50%;background:#252540;position:absolute}
.sc.tl{top:9px;left:9px}.sc.tr{top:9px;right:9px}
.sc.bl{bottom:9px;left:9px}.sc.br{bottom:9px;right:9px}
.lcd-scr{
  background:#071510;border-radius:9px;
  padding:12px 14px;border:1px solid #0D2B18;
  font-family:'DM Mono',monospace;color:#22c55e;
  font-size:11px;line-height:2;
}
.lr{display:flex;justify-content:space-between}
.ldim{color:#14532d}
.blk{animation:blk 1.1s step-end infinite}
@keyframes blk{50%{opacity:0}}
.llbl{text-align:center;font-family:'DM Mono',monospace;font-size:9px;color:#30305A;margin-top:7px}
.hw-stats{display:flex;gap:20px;opacity:0}
.hs .v{font-size:32px;font-weight:900;line-height:1;letter-spacing:-.02em}
.hs .l{font-size:10px;color:var(--mid);margin-top:2px}
.hw-note{
  display:flex;align-items:center;gap:7px;
  padding:9px 13px;border-radius:9px;
  background:#FFFBEB;border:1.5px solid #FDE68A;
  font-size:11px;color:#92400E;opacity:0;
}

/* WA */
.wa-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;width:100%;max-width:840px}
.wa-card{border:1.5px solid var(--line);border-radius:12px;padding:14px;background:white;opacity:0;box-shadow:0 1px 4px rgba(0,0,0,.05)}
.wa-h{display:flex;justify-content:space-between;align-items:center;margin-bottom:7px}
.wa-nm{font-size:11px;font-weight:700;color:var(--green)}
.wa-t{font-family:'DM Mono',monospace;font-size:9px;color:var(--mid)}
.wa-b{font-size:12px;color:#444;line-height:1.6}
.wa-ck{text-align:right;font-size:9px;color:var(--green);margin-top:6px;font-family:'DM Mono',monospace}
.wa-ft{display:flex;gap:14px;opacity:0;flex-wrap:wrap}
.wa-fi{font-size:12px;color:var(--mid)}
.wa-fi strong{color:var(--ink)}

/* CTA */
.fp-wrap{position:relative;display:inline-flex;align-items:center;justify-content:center;opacity:0;margin:14px 0 20px}
.fp-btn{
  width:90px;height:90px;border-radius:50%;
  background:var(--ink);
  border:none;cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 8px 30px rgba(0,0,0,.2);
  transition:transform .2s,box-shadow .2s;
  position:relative;z-index:1;
}
.fp-btn:hover{transform:scale(1.08);box-shadow:0 12px 40px rgba(0,0,0,.3)}
.fp-btn:active{transform:scale(.95)}
.fp-ring{position:absolute;border-radius:50%;border:1px solid rgba(17,17,17,.15);animation:fpr 2.5s ease-out infinite}
.fp-ring:nth-child(1){width:124px;height:124px;animation-delay:0s}
.fp-ring:nth-child(2){width:158px;height:158px;animation-delay:.7s}
.fp-ring:nth-child(3){width:190px;height:190px;animation-delay:1.3s}
@keyframes fpr{0%{opacity:.5;transform:scale(.85)}100%{opacity:0;transform:scale(1.15)}}
.cta-row{display:flex;gap:9px;justify-content:center;flex-wrap:wrap;opacity:0}
.btn-a{
  display:inline-flex;align-items:center;gap:7px;
  padding:11px 22px;border-radius:10px;
  background:var(--ink);color:white;
  font-size:13px;font-weight:700;
  text-decoration:none;border:none;cursor:pointer;
  transition:background .15s;
}
.btn-a:hover{background:#333}
.btn-b{
  display:inline-flex;align-items:center;gap:7px;
  padding:11px 22px;border-radius:10px;
  background:white;color:var(--ink);
  font-size:13px;font-weight:600;
  text-decoration:none;
  border:1.5px solid var(--line);
  transition:border-color .15s;
}
.btn-b:hover{border-color:var(--ink)}

#scene-1 .si,#scene-3 .si{align-items:flex-start}
#scene-1 .big-title,#scene-3 .big-title{text-align:left}
#scene-1 .sub,#scene-3 .sub{text-align:left}
</style>
</head>
<body>

<canvas id="bg-canvas"></canvas>

<div id="root">

  <div id="topbar">
    <div class="brand">
      <img src="{{ asset('logo.png') }}?v=2" alt="Logo" class="brand-logo">
      <span class="brand-name">FINGERSYNC</span>
      <span class="brand-badge">EXPO 2026</span>
    </div>
    <div class="controls">
      <button class="ctrl hi" onclick="togglePlay()" id="playBtn">Pause</button>
      <button class="ctrl" onclick="cycleSpeed()" id="speedBtn">1x</button>
      <button class="ctrl" onclick="toggleFullscreen()">Fullscreen</button>
      <a href="{{ route('admin.expo.buttons') }}" target="_blank" class="ctrl">Remote</a>
    </div>
  </div>

  <div id="stage">

    <!-- SCENE 1 -->
    <section id="scene-0" class="scene active">
      <div class="si">
        <div class="eyebrow" id="e0">Sistem Presensi IoT Biometrik</div>
        <h1 class="big-title" id="t0">
          Absensi yang <span class="col-blue">tahu konteks,</span><br>bukan cuma jam.
        </h1>
        <p class="sub" id="s0">
          FingerSync menghubungkan sensor sidik jari ESP32 dengan jadwal KBM sekolah secara real-time.
          Tahu siapa siswa, mapel apa, di ruangan mana, dan status gurunya.
        </p>
        <div class="chip-row" id="c0">
          <div class="chip">
            <div class="chip-ico" style="background:#FEF9C3">⚡</div>
            <div><div class="chip-val">0.1 Detik</div><div class="chip-sub">respon sensor</div></div>
          </div>
          <div class="chip">
            <div class="chip-ico" style="background:#DCFCE7">🛡</div>
            <div><div class="chip-val">Anti Titip</div><div class="chip-sub">100% biometrik</div></div>
          </div>
          <div class="chip">
            <div class="chip-ico" style="background:#DBEAFE">📱</div>
            <div><div class="chip-val">WA Otomatis</div><div class="chip-sub">notif orang tua</div></div>
          </div>
          <div class="chip">
            <div class="chip-ico" style="background:#F3E8FF">📊</div>
            <div><div class="chip-val">Dashboard</div><div class="chip-sub">rekap dan laporan</div></div>
          </div>
        </div>
      </div>
    </section>

    <!-- SCENE 2 -->
    <section id="scene-1" class="scene">
      <div class="si">
        <div class="eyebrow" id="e1">Fitur Utama</div>
        <h2 class="big-title" id="t1" style="font-size:clamp(36px,6.5vw,72px)">
          Bukan cuma <span class="col-blue">catat jam masuk.</span>
        </h2>
        <p class="sub" id="s1">Sistem memvalidasi 4 hal sekaligus sebelum absensi diterima.</p>
        <div class="pillars" id="p1">
          <div class="pillar">
            <div class="pa" style="background:var(--blue)"></div>
            <div class="pn">Pilar 01</div>
            <div class="pm">Identitas Siswa</div>
            <div class="pd">Sidik jari terverifikasi di server, bukan cuma di sensor.</div>
          </div>
          <div class="pillar">
            <div class="pa" style="background:var(--green)"></div>
            <div class="pn">Pilar 02</div>
            <div class="pm">Jadwal Aktif</div>
            <div class="pd">Cocokkan jam KBM dan mata pelajaran yang sedang berjalan.</div>
          </div>
          <div class="pillar">
            <div class="pa" style="background:var(--orange)"></div>
            <div class="pn">Pilar 03</div>
            <div class="pm">Ruangan Fisik</div>
            <div class="pd">Salah lab atau kelas? Akses langsung ditolak.</div>
          </div>
          <div class="pillar">
            <div class="pa" style="background:var(--purple)"></div>
            <div class="pn">Pilar 04</div>
            <div class="pm">Status Guru</div>
            <div class="pd">Adaptif jika guru izin, tugas mandiri, atau diganti.</div>
          </div>
        </div>
        <div class="warn" id="w1">
          Contoh: Siswa TKJ tap di Lab RPL <strong style="margin-left:4px;color:#991B1B">Ditolak, salah ruangan (Buzzer 3x)</strong>
        </div>
      </div>
    </section>

    <!-- SCENE 3 -->
    <section id="scene-2" class="scene">
      <div class="si">
        <div class="hwl">
          <div class="eyebrow" id="e2">Hardware dan Audio</div>
          <h2 class="big-title" id="t2" style="font-size:clamp(32px,5.5vw,62px);text-align:left">
            Feedback instan<br><span class="col-orange">langsung di alat.</span>
          </h2>
          <p class="sub" id="s2" style="text-align:left">
            LCD 20x4 tampilkan nama, status, dan mapel. Buzzer beri sinyal audio berbeda tiap kondisi.
          </p>
          <div class="hw-stats" id="hs">
            <div class="hs"><div class="v col-green">1x</div><div class="l">Beep, hadir</div></div>
            <div class="hs"><div class="v col-orange">2x</div><div class="l">Beep, sudah absen</div></div>
            <div class="hs"><div class="v" style="color:#B91C1C">3x</div><div class="l">Beep, ditolak</div></div>
          </div>
          <div class="hw-note" id="hn">
            ESP32 + Sensor R307 + LCD 20x4 I2C dalam satu perangkat mandiri
          </div>
        </div>
        <div class="hwr" id="lcd">
          <div class="lcd-wrap">
            <div class="sc tl"></div><div class="sc tr"></div>
            <div class="sc bl"></div><div class="sc br"></div>
            <div class="lcd-scr">
              <div class="lr"><span>ABSENSI BERHASIL!</span><span class="blk">|</span></div>
              <div class="lr"><span>Titin Yulianti</span></div>
              <div class="lr"><span>Status: Hadir Tepat</span></div>
              <div class="lr ldim"><span>Mapel: Pemrograman</span></div>
            </div>
            <div class="llbl">LCD 20x4 Live Status</div>
          </div>
        </div>
      </div>
    </section>

    <!-- SCENE 4 -->
    <section id="scene-3" class="scene">
      <div class="si">
        <div class="eyebrow" id="e3">WhatsApp Gateway via Fonnte</div>
        <h2 class="big-title" id="t3" style="font-size:clamp(34px,6vw,68px);text-align:left">
          Orang tua tahu<br><span class="col-green">tanpa perlu tanya.</span>
        </h2>
        <div class="wa-grid" id="wg">
          <div class="wa-card">
            <div class="wa-h">
              <span class="wa-nm">FingerSync ke Orang Tua</span>
              <span class="wa-t">06:45 WIB</span>
            </div>
            <div class="wa-b">
              Ananda <strong style="color:var(--ink)">Titin Yulianti</strong> hadir mengikuti pelajaran
              <strong style="color:var(--ink)">Pemrograman Web</strong> pukul <strong style="color:var(--ink)">06:45 WIB</strong>.
            </div>
            <div class="wa-ck">Terkirim</div>
          </div>
          <div class="wa-card">
            <div class="wa-h">
              <span class="wa-nm">Rekap Grup X TKJ 1</span>
              <span class="wa-t">17:30 WIB</span>
            </div>
            <div class="wa-b">
              Rekap Presensi X TKJ 1:<br>
              Hadir 20, Terlambat 5<br>
              Sakit 2, Izin 1, Alpa 2
            </div>
            <div class="wa-ck">Terkirim</div>
          </div>
        </div>
        <div class="wa-ft" id="wf" style="margin-top:12px">
          <div class="wa-fi"><strong>Notif real-time</strong> tiap absen</div>
          <div class="wa-fi"><strong>Rekap harian</strong> otomatis ke grup</div>
          <div class="wa-fi"><strong>Hemat kuota</strong> via batching grup</div>
        </div>
      </div>
    </section>

    <!-- SCENE 5 -->
    <section id="scene-4" class="scene">
      <div class="si" style="align-items:center;text-align:center">
        <div class="eyebrow" id="e4">Live Demo</div>
        <h2 class="big-title" id="t4">
          Tempelkan jari <span class="col-purple">Anda sekarang.</span>
        </h2>
        <p class="sub" id="s4">Sensor ESP32 aktif di depan Anda. Tap jari dan lihat respon sistem secara langsung.</p>
        <div class="fp-wrap" id="fp">
          <div class="fp-ring"></div>
          <div class="fp-ring"></div>
          <div class="fp-ring"></div>
          <button class="fp-btn" onclick="doScan()" id="fpBtn">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004 11m0 0a8 8 0 001.077 3.978m12.44 2.118A11.968 11.968 0 0112 21a11.97 11.97 0 01-5.171-1.168"/>
            </svg>
          </button>
        </div>
        <div class="cta-row" id="cr">
          <a href="{{ route('admin.expo.buttons') }}" target="_blank" class="btn-a">Remote Respon</a>
          <a href="{{ route('dashboard') }}" target="_blank" class="btn-b">Dashboard Web</a>
        </div>
      </div>
    </section>

  </div>

  <div id="footer">
    <div class="fl">
      <span class="live-dot"></span>
      <span>ESP32 TKJ1 Online</span>
    </div>
    <div class="fr">
      <div class="pips" id="pips">
        <div class="pip on" id="pip-0"></div>
        <div class="pip" id="pip-1"></div>
        <div class="pip" id="pip-2"></div>
        <div class="pip" id="pip-3"></div>
        <div class="pip" id="pip-4"></div>
      </div>
      <span id="si">1 / 5</span>
    </div>
  </div>

</div>

<script>
// BG CANVAS
(function(){
  const cv=document.getElementById('bg-canvas');
  const cx=cv.getContext('2d');
  let W,H,pts;
  const N=50;
  function rsz(){W=cv.width=window.innerWidth;H=cv.height=window.innerHeight;init()}
  function init(){
    pts=[];
    for(let i=0;i<N;i++) pts.push({x:Math.random()*W,y:Math.random()*H,vx:(Math.random()-.5)*.25,vy:(Math.random()-.5)*.25,r:Math.random()*1.2+.4});
  }
  function draw(){
    cx.clearRect(0,0,W,H);
    for(let i=0;i<N;i++){
      for(let j=i+1;j<N;j++){
        const dx=pts[i].x-pts[j].x,dy=pts[i].y-pts[j].y,d=Math.sqrt(dx*dx+dy*dy);
        if(d<110){cx.beginPath();cx.moveTo(pts[i].x,pts[i].y);cx.lineTo(pts[j].x,pts[j].y);cx.strokeStyle=`rgba(29,78,216,${(1-d/110)*.08})`;cx.lineWidth=.5;cx.stroke()}
      }
    }
    pts.forEach(p=>{
      p.x+=p.vx;p.y+=p.vy;
      if(p.x<0)p.x=W;if(p.x>W)p.x=0;if(p.y<0)p.y=H;if(p.y>H)p.y=0;
      cx.beginPath();cx.arc(p.x,p.y,p.r,0,Math.PI*2);cx.fillStyle='rgba(29,78,216,.2)';cx.fill();
    });
    requestAnimationFrame(draw);
  }
  window.addEventListener('resize',rsz);rsz();draw();
})();

// SCENE ANIMATIONS
function animateScene(i){
  const tl=gsap.timeline();
  if(i===0){
    const sp=new SplitType('#t0',{types:'chars'});
    gsap.set('#e0,#s0,#c0',{opacity:0,y:16});
    gsap.set(sp.chars,{opacity:0,y:36,rotateX:-35});
    tl.to('#e0',{opacity:1,y:0,duration:.4,ease:'power3.out'})
      .to(sp.chars,{opacity:1,y:0,rotateX:0,duration:.55,ease:'back.out(1.5)',stagger:.016},'-=.15')
      .to('#s0',{opacity:1,y:0,duration:.5,ease:'power3.out'},'-=.25')
      .to('#c0',{opacity:1,y:0,duration:.4,ease:'power3.out'},'-=.25');
  }
  if(i===1){
    const sp=new SplitType('#t1',{types:'chars'});
    gsap.set('#e1,#s1,#w1',{opacity:0,y:14});
    gsap.set(sp.chars,{opacity:0,y:28,rotateX:-28});
    gsap.set('.pillar',{opacity:0,y:26,scale:.97});
    tl.to('#e1',{opacity:1,y:0,duration:.4,ease:'power3.out'})
      .to(sp.chars,{opacity:1,y:0,rotateX:0,duration:.5,ease:'back.out(1.4)',stagger:.018},'-=.1')
      .to('#s1',{opacity:1,y:0,duration:.4,ease:'power3.out'},'-=.2')
      .to('.pillar',{opacity:1,y:0,scale:1,duration:.5,ease:'back.out(1.2)',stagger:.07},'-=.15')
      .to('#w1',{opacity:1,y:0,duration:.35,ease:'power3.out'},'-=.05');
  }
  if(i===2){
    const sp=new SplitType('#t2',{types:'chars'});
    gsap.set('#e2,#s2,#hs,#hn',{opacity:0,x:-18});
    gsap.set(sp.chars,{opacity:0,x:-18});
    gsap.set('#lcd',{opacity:0,x:36,rotateY:-7});
    tl.to('#e2',{opacity:1,x:0,duration:.4,ease:'power3.out'})
      .to(sp.chars,{opacity:1,x:0,duration:.5,ease:'power3.out',stagger:.022},'-=.1')
      .to('#s2',{opacity:1,x:0,duration:.4,ease:'power3.out'},'-=.15')
      .to('#lcd',{opacity:1,x:0,rotateY:0,duration:.65,ease:'back.out(1.1)'},'-=.35')
      .to('#hs',{opacity:1,x:0,duration:.4,ease:'power3.out'},'-=.25')
      .to('#hn',{opacity:1,x:0,duration:.35,ease:'power3.out'},'-=.15');
  }
  if(i===3){
    const sp=new SplitType('#t3',{types:'chars'});
    gsap.set('#e3',{opacity:0,y:14});
    gsap.set(sp.chars,{opacity:0,y:22,rotateX:-22});
    gsap.set('#wg .wa-card:nth-child(1)',{opacity:0,x:-26});
    gsap.set('#wg .wa-card:nth-child(2)',{opacity:0,x:26});
    gsap.set('#wf',{opacity:0,y:14});
    tl.to('#e3',{opacity:1,y:0,duration:.4,ease:'power3.out'})
      .to(sp.chars,{opacity:1,y:0,rotateX:0,duration:.5,ease:'back.out(1.4)',stagger:.018},'-=.1')
      .to('#wg .wa-card:nth-child(1)',{opacity:1,x:0,duration:.5,ease:'back.out(1.2)'},'-=.05')
      .to('#wg .wa-card:nth-child(2)',{opacity:1,x:0,duration:.5,ease:'back.out(1.2)'},'-=.4')
      .to('#wf',{opacity:1,y:0,duration:.35,ease:'power3.out'},'-=.05');
  }
  if(i===4){
    const sp=new SplitType('#t4',{types:'chars'});
    gsap.set('#e4,#s4,#cr',{opacity:0,y:18});
    gsap.set(sp.chars,{opacity:0,scale:.55,rotateZ:gsap.utils.wrap([-7,7])});
    gsap.set('#fp',{opacity:0,scale:.65});
    tl.to('#e4',{opacity:1,y:0,duration:.4,ease:'power3.out'})
      .to(sp.chars,{opacity:1,scale:1,rotateZ:0,duration:.65,ease:'elastic.out(1,.65)',stagger:.038},'-=.1')
      .to('#s4',{opacity:1,y:0,duration:.45,ease:'power3.out'},'-=.2')
      .to('#fp',{opacity:1,scale:1,duration:.55,ease:'back.out(1.6)'},'-=.15')
      .to('#cr',{opacity:1,y:0,duration:.4,ease:'power3.out'},'-=.15');
  }
}

// TIMELINE
const TOTAL=5,MS=5000;
let cur=0,play=true,spd=1.0,el=0,lt=Date.now();

function jump(idx){
  const prev=document.getElementById('scene-'+cur);
  gsap.to(prev,{opacity:0,scale:.97,duration:.3,ease:'power2.in',onComplete:()=>{
    prev.classList.remove('active');gsap.set(prev,{opacity:1,scale:1});
  }});
  cur=((idx%TOTAL)+TOTAL)%TOTAL;el=0;lt=Date.now();
  const next=document.getElementById('scene-'+cur);
  next.classList.add('active');
  gsap.fromTo(next,{opacity:0,scale:1.02},{opacity:1,scale:1,duration:.4,ease:'power2.out'});
  animateScene(cur);
  // pips
  for(let i=0;i<TOTAL;i++){
    const p=document.getElementById('pip-'+i);
    if(p)p.classList.toggle('on',i===cur);
  }
  document.getElementById('si').textContent=(cur+1)+' / '+TOTAL;
}

function tick(){
  const now=Date.now(),d=(now-lt)*spd;lt=now;
  if(play){el+=d;if(el>=MS)jump(cur+1);}
  requestAnimationFrame(tick);
}

function togglePlay(){
  play=!play;lt=Date.now();
  document.getElementById('playBtn').textContent=play?'Pause':'Play';
}
function cycleSpeed(){
  spd=spd===1.0?1.5:spd===1.5?.5:1.0;
  document.getElementById('speedBtn').textContent=spd+'x';
}
function toggleFullscreen(){
  if(!document.fullscreenElement)document.documentElement.requestFullscreen().catch(()=>{});
  else document.exitFullscreen().catch(()=>{});
}

// SCAN
let ac=null;
function beep(f=700,d=.1,t='sine'){
  try{
    if(!ac){const A=window.AudioContext||window.webkitAudioContext;if(A)ac=new A();}
    if(!ac)return;if(ac.state==='suspended')ac.resume();
    const o=ac.createOscillator(),g=ac.createGain();
    o.type=t;o.frequency.setValueAtTime(f,ac.currentTime);
    g.gain.setValueAtTime(.1,ac.currentTime);
    g.gain.exponentialRampToValueAtTime(.001,ac.currentTime+d);
    o.connect(g);g.connect(ac.destination);o.start();o.stop(ac.currentTime+d);
  }catch(e){}
}

async function doScan(){
  const btn=document.getElementById('fpBtn');
  gsap.to(btn,{scale:.92,duration:.1,yoyo:true,repeat:1});
  beep(900,.12,'sine');
  const csrf=document.querySelector('meta[name="csrf-token"]')?.content;
  try{
    const r=await fetch("{{ route('admin.expo.simulate-scan') }}",{
      method:'POST',
      headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf||''},
      body:JSON.stringify({id_device:'TKJ1',id_siswa:'auto'})
    });
    const d=await r.json();
    alert('['+d.response?.status+'] '+(d.response?.nama||d.response?.message||'OK'));
  }catch(e){alert('Scan simulator aktif!');}
}

window.addEventListener('keydown',e=>{
  if(e.code==='Space'){e.preventDefault();togglePlay();}
  if(e.code==='ArrowRight'){e.preventDefault();jump(cur+1);}
  if(e.code==='ArrowLeft'){e.preventDefault();jump(cur-1);}
});

window.addEventListener('load',()=>{
  animateScene(0);
  requestAnimationFrame(tick);
});
</script>
</body>
</html>
