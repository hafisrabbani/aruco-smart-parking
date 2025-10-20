#include <WiFi.h>
#include <WebServer.h>
#include <ESP32Servo.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <HTTPClient.h>


// ===========================================
// KONFIGURASI WIFI DAN HOST
// ===========================================
const char* WIFI_SSID = "SUSILO 7487";
const char* WIFI_PASS = "12345678";
String HOST_URL = "http://10.153.53.66:8081";

// ===========================================
// KONFIGURASI PIN
// ===========================================
const int SERVO_IN_PIN = 16;
const int SERVO_OUT_PIN = 17;

const int TRIG_IN_PIN = 5;
const int ECHO_IN_PIN = 18;

const int TRIG_OUT_PIN = 19;
const int ECHO_OUT_PIN = 21;

// ===========================================
// PARAMETER SISTEM
// ===========================================
const int DETECTION_DISTANCE_CM = 20;
const int OPEN_ANGLE = 90;
const int CLOSE_ANGLE = 0;

// ===========================================
// KONFIGURASI OLED
// ===========================================
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET -1
#define OLED_SDA 26
#define OLED_SCL 27

Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

// ===========================================
// OBJEK GLOBAL
// ===========================================
Servo gateInServo;
Servo gateOutServo;
WebServer server(80);

// ===========================================
// FUNGSI UMUM
// ===========================================
long measureDistance(int trigPin, int echoPin) {
  digitalWrite(trigPin, LOW);
  delayMicroseconds(2);
  digitalWrite(trigPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(trigPin, LOW);

  long duration = pulseIn(echoPin, HIGH, 25000);
  long distance = duration * 0.034 / 2;
  return (distance == 0 || distance > 400) ? 400 : distance;
}

void moveServo(Servo& servo, int angle) {
  servo.write(angle);
  delay(100);
}

// ===========================================
// FUNGSI DISPLAY OLED
// ===========================================
void displayReady(const char* status = "READY") {
  display.clearDisplay();
  display.setTextSize(2);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(10, 10);
  display.println("SMART PARKING");
  display.setTextSize(2);
  display.setCursor(25, 40);
  display.print(status);
  display.display();
}

// ===========================================
// FUNGSI BUKA / TUTUP PALANG BERDASARKAN SENSOR
// ===========================================
void openGate(Servo& servo, int trigPin, int echoPin, bool isEntrance) {
  Serial.println(isEntrance ? "[MASUK] Membuka palang..." : "[KELUAR] Membuka palang...");
  moveServo(servo, OPEN_ANGLE);  // Buka palang

  bool carDetected = false;
  bool wasDetected = false;
  unsigned long detectionStart = millis();
  unsigned long timeout = detectionStart + 20000; // Batas waktu 20 detik

  Serial.println("[INFO] Menunggu objek terdeteksi...");

  // Loop sampai sensor mendeteksi objek
  while (true) {
    long distance = measureDistance(trigPin, echoPin);

    // Log jarak setiap 300ms biar ga terlalu spam
    static unsigned long lastLog = 0;
    if (millis() - lastLog > 300) {
      Serial.print("[DEBUG] Jarak sensor: ");
      Serial.print(distance);
      Serial.println(" cm");
      lastLog = millis();
    }

    // Jika objek terdeteksi
    if (distance < DETECTION_DISTANCE_CM) {
      if (!wasDetected) {
        Serial.println("[DETEKSI] Objek terdeteksi di depan sensor!");
        wasDetected = true;
        carDetected = true;
      }
    } else {
      if (wasDetected) {
        Serial.println("[INFO] Objek sudah menjauh, menutup palang...");

        // ===========================
        // Kirim HTTP POST ke API kamera
        // ===========================
        HTTPClient http;
        String url = HOST_URL + "/active";
        Serial.print("[HTTP] Mengirim POST ke: ");
        Serial.println(url);

        http.begin(url);
        http.addHeader("Content-Type", "application/json");

        int httpCode = http.POST("{}");
        if (httpCode > 0) {
          Serial.printf("[HTTP] Response code: %d\n", httpCode);
          String payload = http.getString();
          Serial.println("[HTTP] Response body: " + payload);
        } else {
          Serial.printf("[HTTP] Gagal kirim: %s\n", http.errorToString(httpCode).c_str());
        }
        http.end();
        // ===========================

        break;
      }
    }

    // Timeout (jaga-jaga kalau sensor rusak / tidak mendeteksi apa pun)
    if (millis() > timeout) {
      Serial.println("[TIMEOUT] Tidak ada deteksi dalam 20 detik, menutup palang otomatis...");
      break;
    }

    // Pastikan server tetap jalan
    server.handleClient();
    delay(100);
  }

  // Tutup palang
  moveServo(servo, CLOSE_ANGLE);
  delay(500);

  // Tampilkan di OLED kalau kamu pakai displayReady()
  displayReady();

  if (carDetected) {
    Serial.println(isEntrance ? "[MASUK] Mobil terdeteksi, palang ditutup." : "[KELUAR] Mobil terdeteksi, palang ditutup.");
  } else {
    Serial.println("[INFO] Tidak ada mobil, palang ditutup otomatis.");
  }
}



// ===========================================
// HANDLER API
// ===========================================
void handleOpenEntrance() {
  server.send(200, "application/json", "{\"status\":\"ok\",\"pesan\":\"Palang masuk dibuka\"}");
  openGate(gateInServo, TRIG_IN_PIN, ECHO_IN_PIN, true);
}

void handleOpenExit() {
  server.send(200, "application/json", "{\"status\":\"ok\",\"pesan\":\"Palang keluar dibuka\"}");
  openGate(gateOutServo, TRIG_OUT_PIN, ECHO_OUT_PIN, false);
}

void handleStatus() {
  String json = "{\"status\":\"ready\",\"info\":\"Sistem aktif, slot tidak terbatas\"}";
  server.send(200, "application/json", json);
}

// ===========================================
// SETUP
// ===========================================
void setup() {
  Serial.begin(115200);

  pinMode(TRIG_IN_PIN, OUTPUT);
  pinMode(ECHO_IN_PIN, INPUT);
  pinMode(TRIG_OUT_PIN, OUTPUT);
  pinMode(ECHO_OUT_PIN, INPUT);

  // WiFi Connect
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  Serial.print("Menghubungkan ke WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nTerhubung ke WiFi!");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());

  // OLED
  Wire.begin(OLED_SDA, OLED_SCL);
  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println("Gagal inisialisasi OLED");
    while (true);
  }

  // Servo
  gateInServo.setPeriodHertz(50);
  gateInServo.attach(SERVO_IN_PIN, 500, 2400);
  gateInServo.write(CLOSE_ANGLE);

  gateOutServo.setPeriodHertz(50);
  gateOutServo.attach(SERVO_OUT_PIN, 500, 2400);
  gateOutServo.write(CLOSE_ANGLE);

  displayReady();

  // Routing
  server.on("/buka_masuk", HTTP_POST, handleOpenEntrance);
  server.on("/buka_keluar", HTTP_POST, handleOpenExit);
  server.on("/status", HTTP_GET, handleStatus);
  server.begin();

  Serial.println("Server HTTP aktif di port 80");
}

// ===========================================
// LOOP
// ===========================================
void loop() {
  server.handleClient();
}
