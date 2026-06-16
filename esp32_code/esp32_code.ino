#include <Adafruit_Fingerprint.h>
#include <LiquidCrystal_I2C.h>
#include <Wire.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h> // <-- TAMBAHAN WAJIB UNTUK HTTPS/NGROK
#include <ArduinoJson.h>

const char* ssid = "Matahary 2.4G";
const char* password = "kebunanggur";
const char* hostURL = "https://fingersync.satcloud.tech"; // Bisa diganti URL ngrok kapanpun

String idDevice = "TKJ1"; 

LiquidCrystal_I2C lcd(0x27, 20, 4);
#define RXD2 16
#define TXD2 17
#define BUZZER_PIN 15
#define SWITCH_PIN 4  

HardwareSerial mySerial(2);
Adafruit_Fingerprint finger = Adafruit_Fingerprint(&mySerial);

bool modeRegistrasi = false;
unsigned long waktuTekan = 0;
const int DURASI_RESET = 5000; 

unsigned long lastPollingTime = 0;

void setup() {
  Serial.begin(115200);
  Serial.println("\n\n--- FINGERSYNC DEVICE STARTUP ---");
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(SWITCH_PIN, INPUT_PULLUP); 
  
  // --- DIUBAH MENJADI lcd.init() UNTUK VERSI LIBRARY INI ---
  lcd.init();
  lcd.backlight();

  // --- SETUP WIFI ---
  Serial.print("[WIFI] Mencoba koneksi ke: ");
  Serial.println(ssid);
  lcd.setCursor(0, 0);
  lcd.print("Koneksi WiFi...");
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\n[WIFI] Berhasil Terhubung!");
  Serial.print("[WIFI] IP Address: ");
  Serial.println(WiFi.localIP());
  
  lcd.clear();
  lcd.print("WiFi Terhubung!");
  delay(1000);
  lcd.clear();
  
  Serial.println("[FINGERPRINT] Inisialisasi Sensor...");
  mySerial.begin(57600, SERIAL_8N1, RXD2, TXD2);
  finger.begin(57600);

  if (finger.verifyPassword()) {
    Serial.println("[FINGERPRINT] Sensor Ditemukan dan Siap!");
    lcd.print("Sensor Ready!");
  } else {
    Serial.println("[FINGERPRINT] ERROR: Sensor TIDAK Ditemukan!");
    lcd.print("Sensor Error!");
    while (1) { delay(1); }
  }
  delay(1000);
  lcd.clear();
  Serial.println("--- SETUP SELESAI ---");
}

void loop() {
  if (digitalRead(SWITCH_PIN) == LOW) {
    waktuTekan = millis();
    bool resetTriggered = false;
    Serial.println("[TOMBOL] Ditekan, memproses aksi...");

    while (digitalRead(SWITCH_PIN) == LOW) {
      unsigned long durasi = millis() - waktuTekan;
      if (durasi > DURASI_RESET && !resetTriggered) {
        Serial.println("[TOMBOL] Tahan 5 detik terpenuhi. Memulai RESET!");
        hapusSemuaData();
        resetTriggered = true;
      } else if (durasi > 500 && !resetTriggered) {
        lcd.setCursor(0, 3);
        lcd.print("TAHAN UNTUK RESET...");
      }
    }

    if (!resetTriggered && (millis() - waktuTekan) < 2000) {
      modeRegistrasi = !modeRegistrasi;
      Serial.print("[TOMBOL] Ganti Mode ke: ");
      Serial.println(modeRegistrasi ? "REGISTRASI" : "VALIDASI");
      beep(2);
      lcd.clear();
      if(modeRegistrasi) {
        lcd.setCursor(0, 0);
        lcd.print(">> MODE REGISTRASI <<");
        delay(1500);
      }
    }
    lcd.clear();
  }

  if (!modeRegistrasi && (millis() - lastPollingTime > 5000)) {
    cekTugasDariServer();
    lastPollingTime = millis();
  }

  if (modeRegistrasi) {
    jalankanRegistrasi();
  } else {
    jalankanValidasi();
  }
}

void jalankanValidasi() {
  lcd.setCursor(0, 0);
  lcd.print("   MODE VALIDASI    ");
  lcd.setCursor(0, 1);
  lcd.print("SILAHKAN TAP JARI...");

  int result = finger.getImage();
  if (result == FINGERPRINT_OK) {
    Serial.println("[VALIDASI] Sidik Jari terdeteksi di sensor!");
    beep(1);
    result = finger.image2Tz();
    if (result == FINGERPRINT_OK) {
      result = finger.fingerFastSearch();
      lcd.clear();
      
      if (result == FINGERPRINT_OK) {
        Serial.print("[VALIDASI] Jari Dikenal! ID Sensor: ");
        Serial.println(finger.fingerID);
        lcd.print("Memeriksa Server...");
        lcd.setCursor(0,1);
        lcd.print("ID: "); lcd.print(finger.fingerID);
        
        kirimAbsensi(finger.fingerID);
        
      } else {
        Serial.println("[VALIDASI] Akses Ditolak! Jari tidak terdaftar di memori alat.");
        lcd.print("AKSES DITOLAK!");
        lcd.setCursor(0,1);
        lcd.print("JARI TIDAK DIKENAL");
        beep(3);
      }
      delay(2500);
      lcd.clear();
    }
  }
}

void jalankanRegistrasi() {
  int id = cariIDKosong();
  Serial.print("[REGISTRASI] Bersiap mendaftarkan ID kosong ke-");
  Serial.println(id);
  
  lcd.setCursor(0, 0);
  lcd.print("DAFTAR ID: "); lcd.print(id);
  lcd.setCursor(0, 1);
  lcd.print("TEMPELKAN JARI...   ");

  if (ambilTemplate(1) == FINGERPRINT_OK) {
    Serial.println("[REGISTRASI] Scan 1 Sukses. Meminta angkat jari...");
    beep(1);
    lcd.setCursor(0, 2);
    lcd.print("ANGKAT JARI...      ");
    delay(2000);
    while (finger.getImage() != FINGERPRINT_NOFINGER);
    
    Serial.println("[REGISTRASI] Meminta tap ulang jari...");
    lcd.setCursor(0, 2);
    lcd.print("TEMPEL LAGI...      ");
    if (ambilTemplate(2) == FINGERPRINT_OK) {
      Serial.println("[REGISTRASI] Scan 2 Sukses. Membuat model sidik jari...");
      if (finger.createModel() == FINGERPRINT_OK) {
        if (finger.storeModel(id) == FINGERPRINT_OK) {
          Serial.println("[REGISTRASI] SUKSES! Tersimpan di memori sensor.");
          lcd.clear();
          lcd.print("SUKSES DISIMPAN!");
          beep(2);
          
          lcd.setCursor(0, 1);
          lcd.print("Lapor Server...");
          Serial.println("[API] Mengirim laporan ID baru ke Inbox Server...");
          kirimIDBaru(id);
          
          delay(2000);
          modeRegistrasi = false; 
          lcd.clear();
        } else {
          Serial.println("[REGISTRASI] Gagal menyimpan ke memori sensor!");
        }
      } else {
        Serial.println("[REGISTRASI] Jari 1 dan 2 tidak cocok!");
      }
    }
  }
}

void jalankanRegistrasiTarget(int idTarget, int taskId) {
  Serial.print("[SINKRONISASI] Memulai tugas sinkronisasi. Target ID: ");
  Serial.println(idTarget);
  
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print("SINKRONISASI ALAT");
  lcd.setCursor(0, 1); lcd.print("TARGET ID: "); lcd.print(idTarget);
  delay(2000);

  lcd.clear();
  lcd.setCursor(0, 0); lcd.print("TEMPELKAN JARI...");

  if (ambilTemplate(1) == FINGERPRINT_OK) {
    Serial.println("[SINKRONISASI] Scan 1 Sukses.");
    beep(1);
    lcd.setCursor(0, 2); lcd.print("ANGKAT JARI...      ");
    delay(2000);
    while (finger.getImage() != FINGERPRINT_NOFINGER);
    
    lcd.setCursor(0, 2); lcd.print("TEMPEL LAGI...      ");
    if (ambilTemplate(2) == FINGERPRINT_OK) {
      Serial.println("[SINKRONISASI] Scan 2 Sukses.");
      if (finger.createModel() == FINGERPRINT_OK) {
        if (finger.storeModel(idTarget) == FINGERPRINT_OK) {
          Serial.println("[SINKRONISASI] SUKSES tersimpan di memori alat!");
          lcd.clear();
          lcd.print("SINKRONISASI SUKSES!");
          beep(2);
          
          Serial.println("[API] Melapor ke server bahwa tugas selesai!");
          laporTugasSelesai(taskId, "done");
          
          delay(2000);
          lcd.clear();
          return;
        }
      }
    }
  }
  Serial.println("[SINKRONISASI] Batal / Gagal mengeksekusi sinkronisasi.");
  lcd.clear();
}

void hapusSemuaData() {
  Serial.println("[SISTEM] Menghapus SEMUA database...");
  lcd.clear();
  lcd.print("MENGHAPUS SEMUA DATA");
  lcd.setCursor(0, 1);
  lcd.print("MOHON TUNGGU...");
  
  finger.emptyDatabase();
  
  digitalWrite(BUZZER_PIN, HIGH); delay(1000); digitalWrite(BUZZER_PIN, LOW);
  
  Serial.println("[SISTEM] Database dibersihkan.");
  lcd.setCursor(0, 2);
  lcd.print("DATA TERHAPUS BERSIH");
  delay(2000);
  modeRegistrasi = false;
}

int ambilTemplate(int slot) {
  int p = -1;
  while (p != FINGERPRINT_OK) {
    p = finger.getImage();
    if (digitalRead(SWITCH_PIN) == LOW) {
       Serial.println("[REGISTRASI] Dibatalkan oleh tombol user!");
       while(digitalRead(SWITCH_PIN) == LOW) { delay(10); } 
       modeRegistrasi = false;
       lcd.clear();
       return -1;
    }
  }
  return finger.image2Tz(slot);
}

int cariIDKosong() {
  for (int i = 1; i <= 127; i++) {
    if (finger.loadModel(i) != FINGERPRINT_OK) { return i; }
  }
  return 1;
}

void beep(int times) {
  for (int i = 0; i < times; i++) {
    digitalWrite(BUZZER_PIN, HIGH); delay(100); digitalWrite(BUZZER_PIN, LOW); delay(100);
  }
}

// =========================================================================
// IMPLEMENTASI WiFiClientSecure UNTUK NGROK & CLOUDFLARE
// =========================================================================

void kirimAbsensi(int fingerprintID) {
  if(WiFi.status() == WL_CONNECTED){
    WiFiClientSecure client;
    client.setInsecure(); // WAJIB: Bypass Validasi Sertifikat HTTPS
    
    HTTPClient http;
    String fullUrl = String(hostURL) + "/api/scan";
    Serial.print("[API-POST] Mengirim absen ke: ");
    Serial.println(fullUrl);
    
    http.begin(client, fullUrl); // Masukkan 'client' ke dalam http.begin
    http.addHeader("Content-Type", "application/json");

    StaticJsonDocument<200> doc;
    doc["id_device"] = idDevice;
    doc["fingerprint_id"] = fingerprintID;

    String requestBody;
    serializeJson(doc, requestBody);
    Serial.print("[API-POST] Request Payload: ");
    Serial.println(requestBody);

    int httpResponseCode = http.POST(requestBody);
    Serial.print("[API-POST] HTTP Response Code: ");
    Serial.println(httpResponseCode);
    
    lcd.clear(); 

    if(httpResponseCode > 0){
      String payload = http.getString();
      Serial.print("[API-POST] Server Response: ");
      Serial.println(payload);
      
      StaticJsonDocument<512> responseDoc;
      DeserializationError error = deserializeJson(responseDoc, payload);

      if (error) {
          Serial.println("[API] ERROR: Server mengembalikan format aneh (Bukan JSON)!");
          lcd.setCursor(0,0); lcd.print("ERROR SERVER!");
          lcd.setCursor(0,1); lcd.print("Cek Serial Monitor");
          beep(3);
          http.end();
          return;
      }

      String statusStr = responseDoc["status"] | "";
      
      if (statusStr == "SUCCESS") {
          Serial.println("[API] Absensi Berhasil dicatat server.");
          lcd.setCursor(0,0); lcd.print("ABSENSI BERHASIL!");
          lcd.setCursor(0,1); lcd.print(responseDoc["nama"].as<String>().substring(0, 20));
          lcd.setCursor(0,2); lcd.print("Status: "); lcd.print(responseDoc["stat"].as<String>());
          
          String mapel = responseDoc["mapel"] | "";
          if (mapel != "") {
              lcd.setCursor(0, 3);
              lcd.print(mapel.substring(0, 20));
          }
          beep(1);
      } 
      else if (statusStr == "WARN") {
          Serial.println("[API] Ditolak: Sudah absen sebelumnya.");
          lcd.setCursor(0,0); lcd.print("SUDAH ABSEN!");
          lcd.setCursor(0,1); lcd.print(responseDoc["nama"].as<String>().substring(0, 20));
          beep(2);
      }
      else if (statusStr == "INFO") {
          Serial.println("[API] Ditolak: Tidak ada KBM/Jadwal aktif.");
          lcd.setCursor(0,0); lcd.print("AKSES DITOLAK!");
          
          String msg = responseDoc["message"] | "";
          if (msg != "") {
              lcd.setCursor(0, 1);
              if (msg.length() > 20) {
                  lcd.print(msg.substring(0, 20));
                  lcd.setCursor(0, 2);
                  lcd.print(msg.substring(20, 40));
              } else {
                  lcd.print(msg);
              }
          } else {
              lcd.setCursor(0,1); lcd.print("TIDAK ADA JADWAL");
              lcd.setCursor(0,2); lcd.print("SAAT INI");
          }
          beep(3); 
      }
      else if (statusStr == "ERROR" || statusStr == "FATAL_ERROR") {
          Serial.println("[API] Ditolak: Data tidak valid / Error Server.");
          lcd.setCursor(0,0); lcd.print("AKSES DITOLAK!");
          
          String msg = responseDoc["message"] | "";
          if (msg != "") {
              Serial.println("Pesan dari Server: " + msg);
              
              // --- SINKRONISASI LCD DENGAN PESAN ERROR SERVER ---
              // Jika pesan lebih panjang dari 20 karakter, potong menjadi 2 baris di LCD 20x4
              lcd.setCursor(0, 1);
              if (msg.length() > 20) {
                  lcd.print(msg.substring(0, 20));
                  lcd.setCursor(0, 2);
                  lcd.print(msg.substring(20, 40));
              } else {
                  lcd.print(msg);
              }
          } else {
              lcd.setCursor(0, 1); 
              lcd.print("DATA TIDAK VALID");
          }
          beep(3); 
      } 
      else {
          Serial.println("[API] Server Mengalami Internal Error (Status JSON Tidak Dikenali)!");
          lcd.setCursor(0,0); lcd.print("ERROR SERVER!");
          lcd.setCursor(0,1); lcd.print("Code: "); lcd.print(httpResponseCode);
          beep(3);
      }
      
    } else {
      Serial.println("[API] Gagal Terhubung ke Server HTTP!");
      lcd.setCursor(0,0); lcd.print("KONEKSI GAGAL!");
      lcd.setCursor(0,1); lcd.print("Cek Internet");
      beep(3);
    }
    http.end();
  } else {
    Serial.println("[API] WiFi Terputus! Tidak bisa kirim absensi.");
    lcd.clear();
    lcd.setCursor(0,0); lcd.print("WIFI TERPUTUS!");
    beep(3);
  }
}


void kirimIDBaru(int fingerprintID) {
  if(WiFi.status() == WL_CONNECTED){
    WiFiClientSecure client;
    client.setInsecure();
    
    HTTPClient http;
    String fullUrl = String(hostURL) + "/api/register/new";
    Serial.print("[API-POST] Melaporkan ID baru ke: ");
    Serial.println(fullUrl);
    
    http.begin(client, fullUrl);
    http.addHeader("Content-Type", "application/json");

    StaticJsonDocument<200> doc;
    doc["id_device"] = idDevice;
    doc["fingerprint_id"] = fingerprintID;

    String requestBody;
    serializeJson(doc, requestBody);

    int httpResponseCode = http.POST(requestBody);
    Serial.print("[API-POST] Response Code Lapor ID: ");
    Serial.println(httpResponseCode);
    
    if(httpResponseCode > 0){
      lcd.setCursor(0, 2);
      lcd.print("ID Terekam di Web!  ");
    }
    http.end();
  }
}

void cekTugasDariServer() {
  if(WiFi.status() == WL_CONNECTED){
    WiFiClientSecure client;
    client.setInsecure();
    
    HTTPClient http;
    String url = String(hostURL) + "/api/register/task?id_device=" + idDevice;
    
    http.begin(client, url);
    int httpResponseCode = http.GET();
    
    if(httpResponseCode == 200){
      String payload = http.getString();
      
      StaticJsonDocument<256> doc;
      DeserializationError error = deserializeJson(doc, payload);

      if (!error) {
        const char* status = doc["status"];
        if(status != nullptr) {
          if(strcmp(status, "TASK_AVAILABLE") == 0) {
            Serial.println("\n[POLLING] TUGAS DITEMUKAN! Ada sinkronisasi tertunda dari server!");
            int taskId = doc["task_id"];
            int targetId = doc["target_id"];
            jalankanRegistrasiTarget(targetId, taskId);
          }
        }
      } else {
        Serial.print("\n[POLLING] Gagal parsing JSON JSON: ");
        Serial.println(error.c_str());
      }
    } else {
      Serial.print("\n[POLLING] Cek tugas gagal. HTTP Code: ");
      Serial.println(httpResponseCode);
    }
    http.end();
  }
}

void laporTugasSelesai(int taskId, String statusTask) {
  if(WiFi.status() == WL_CONNECTED){
    WiFiClientSecure client;
    client.setInsecure();
    
    HTTPClient http;
    String fullUrl = String(hostURL) + "/api/register/complete";
    Serial.print("[API-POST] Melaporkan tugas Selesai ke: ");
    Serial.println(fullUrl);
    
    http.begin(client, fullUrl);
    http.addHeader("Content-Type", "application/json");

    StaticJsonDocument<200> doc;
    doc["task_id"] = taskId;
    doc["status"] = statusTask;

    String requestBody;
    serializeJson(doc, requestBody);
    
    int httpResponseCode = http.POST(requestBody);
    Serial.print("[API-POST] Response Code Tugas Selesai: ");
    Serial.println(httpResponseCode);
    
    http.end();
  }
}
