#include <WiFi.h>
#include <WebServer.h>
#include <ESP32Servo.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// ===========================================
// KONFIGURASI WIFI (ESP32 SEBAGAI ACCESS POINT)
// ===========================================
const char* AP_SSID = "SMART_PARKING_AP";
const char* AP_PASS = "12345678";  // Minimal 8 karakter

String HOST_URL = "http://192.168.4.2:8081";  // IP laptop/host (cek via ipconfig)

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
// VARIABEL DATA KAPASITAS
// ===========================================
int maxCapacity = 0;
int currentCapacity = 0;
int availableCapacity = 0;
unsigned long lastRefresh = 0;
const unsigned long REFRESH_INTERVAL = 2000;  // 30 detik

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
// FUNGSI DISPLAY
// ===========================================
void displayCapacity() {
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 0);
  display.println("SMART PARKING");
  display.setTextSize(2);
  display.setCursor(0, 20);
  display.printf("Tersedia: %d", availableCapacity);
  display.display();
}

void displayError(const char* msg) {
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 20);
  display.println(msg);
  display.display();
}

// ===========================================
// FUNGSI AMBIL DATA KAPASITAS (PAKAI JSON PARSING)
// ===========================================
bool fetchCapacityOnce() {
  HTTPClient http;
  String url = HOST_URL + "/capacity";
  http.begin(url);
  http.addHeader("Content-Type", "application/json");

  String body = "{\"parking_lot\": \"A1\"}";
  int httpCode = http.POST(body);

  if (httpCode == 200) {
    String payload = http.getString();
    Serial.println("[CAPACITY] Response: " + payload);

    StaticJsonDocument<256> doc;
    DeserializationError err = deserializeJson(doc, payload);
    if (!err) {
      JsonObject data = doc["data"];
      maxCapacity = data["max_capacity"] | 0;
      currentCapacity = data["current_capacity"] | 0;
      availableCapacity = data["available_capacity"] | 0;

      Serial.printf("[PARSE] Max: %d, Current: %d, Available: %d\n",
                    maxCapacity, currentCapacity, availableCapacity);

      displayCapacity();
      http.end();
      return true;
    } else {
      Serial.println("[JSON] Gagal parsing JSON");
    }
  } else {
    Serial.printf("[CAPACITY] Gagal request (%d)\n", httpCode);
  }

  http.end();
  return false;
}

void fetchCapacityWithRetry() {
  while (true) {
    if (fetchCapacityOnce()) break;  // Berhasil, keluar dari loop
    displayError("Gagal ambil data!");
    delay(1000);  // Coba lagi tiap 1 detik
  }
}

// ===========================================
// FUNGSI BUKA / TUTUP PALANG
// ===========================================
void openGate(Servo& servo, int trigPin, int echoPin, bool isEntrance) {
  Serial.println(isEntrance ? "[MASUK] Membuka palang..." : "[KELUAR] Membuka palang...");
  moveServo(servo, OPEN_ANGLE);

  bool wasDetected = false;
  unsigned long detectionStart = millis();
  unsigned long timeout = detectionStart + 20000;

  while (true) {
    long distance = measureDistance(trigPin, echoPin);
    if (distance < DETECTION_DISTANCE_CM) {
      wasDetected = true;
    } else if (wasDetected) {
      Serial.println("[INFO] Objek menjauh, menutup palang...");

      // Kirim HTTP POST ke API /active
      HTTPClient http;
      String url = HOST_URL + "/active";
      http.begin(url);
      http.addHeader("Content-Type", "application/json");
      int httpCode = http.POST("{}");
      Serial.printf("[HTTP] Code: %d\n", httpCode);
      http.end();

      // ✅ Segera fetch ulang data kapasitas setelah kendaraan lewat
      fetchCapacityWithRetry();
      break;
    }

    if (millis() > timeout) break;
    server.handleClient();
    fetchCapacityWithRetry();
    delay(100);
  }

  fetchCapacityWithRetry();
  moveServo(servo, CLOSE_ANGLE);
  // ✅ Tampilkan data terbaru setelah menutup palang
  displayCapacity();
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
  String json = "{\"status\":\"ready\",\"info\":\"ESP32 sebagai Access Point\"}";
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

  Serial.println("Membuat Access Point...");
  WiFi.softAP(AP_SSID, AP_PASS);

  IPAddress IP = WiFi.softAPIP();
  Serial.print("Access Point aktif. IP ESP32: ");
  Serial.println(IP);

  Wire.begin(OLED_SDA, OLED_SCL);
  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println("Gagal inisialisasi OLED");
    while (true)
      ;
  }

  gateInServo.attach(SERVO_IN_PIN);
  gateOutServo.attach(SERVO_OUT_PIN);
  gateInServo.write(CLOSE_ANGLE);
  gateOutServo.write(CLOSE_ANGLE);

  // Ambil kapasitas pertama kali (auto retry sampai sukses)
  fetchCapacityWithRetry();
  lastRefresh = millis();

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

  unsigned long now = millis();
  if (now - lastRefresh > REFRESH_INTERVAL) {
    if (!fetchCapacityOnce()) {
      Serial.println("[INFO] Gagal refresh periodik, skip dan tunggu 30 detik berikutnya");
    }
    lastRefresh = now;
  }
}
