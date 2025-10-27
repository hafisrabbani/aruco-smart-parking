import cv2
import tkinter as tk
from tkinter import Label, Button, OptionMenu, StringVar
from PIL import Image, ImageTk
import threading
import requests
import json
from http.server import BaseHTTPRequestHandler, HTTPServer


# =====================================
# KONFIGURASI
# =====================================
API_HOST = "http://127.0.0.1:8000"
API_DEVICE = "http://192.168.4.1"
API_ENTRY_GATE = f"{API_HOST}/api/entry-gate"
API_EXIT_GATE = f"{API_HOST}/api/exit-gate"
API_CAPACITY = f"{API_HOST}/api/capacity"
HTTP_SERVER_PORT = 8081  # Port untuk menerima request dari alat


# =====================================
# BAGIAN HTTP SERVER INTERNAL
# =====================================
class SimpleRequestHandler(BaseHTTPRequestHandler):
    def do_POST(self):
        path = self.path
        content_length = int(self.headers.get("Content-Length", 0))
        post_data = self.rfile.read(content_length or 0)

        # ✅ Pastikan data selalu terdefinisi
        data = {}
        tag_id = None

        try:
            if post_data:
                try:
                    data = json.loads(post_data)
                    tag_id = data.get("id")
                except json.JSONDecodeError:
                    print("[HTTP] Body bukan JSON valid, abaikan.")

            if path == "/":
                print(f"[HTTP] Diterima ID dari alat: {tag_id}")
                if hasattr(self.server, "app"):
                    self.server.app.root.after(0, self.server.app.handle_external_request, tag_id)
                self._send_json({"status": "ok", "action": "received_id"})

            elif path == "/active":
                print("[HTTP] Perintah mengaktifkan kamera diterima.")
                if hasattr(self.server, "app"):
                    self.server.app.root.after(0, self.server.app.activate_camera_from_api)
                self._send_json({"status": "ok", "action": "camera_activated"})

            elif path == "/capacity":
                print("[HTTP] Meneruskan request ke API kapasitas...")
                try:
                    # ✅ Ganti GET ke POST (lebih cocok untuk kirim JSON)
                    api_response = requests.get(f"{API_HOST}/api/capacity", json=data, timeout=5)
                    response_json = api_response.json()
                    print("[CAPACITY] Response:", response_json)
                    self._send_json(response_json, api_response.status_code)
                except Exception as e:
                    print("[CAPACITY] Gagal menghubungi API:", e)
                    self._send_json({"error": str(e)}, 500)

            else:
                self._send_json({"error": f"Endpoint {path} tidak dikenal"}, 404)

        except Exception as e:
            self._send_json({"error": str(e)}, 400)


    def _send_json(self, data, code=200):
        self.send_response(code)
        self.send_header("Content-Type", "application/json")
        self.end_headers()
        self.wfile.write(json.dumps(data).encode())


def start_http_server(app, port=HTTP_SERVER_PORT):
    server = HTTPServer(("0.0.0.0", port), SimpleRequestHandler)
    server.app = app
    print(f"[HTTP] Server aktif di port {port}")
    try:
        server.serve_forever()
    except Exception as e:
        print("[HTTP] Server berhenti:", e)
    finally:
        server.server_close()


# =====================================
# BAGIAN GUI + KAMERA ARUCO
# =====================================
class ArucoApp:
    def __init__(self, root):
        self.root = root
        self.root.title("ArUco Detector - Gate System")
        self.root.geometry("900x750")

        # Dropdown kamera
        self.available_cams = self.detect_cameras()
        self.selected_cam = StringVar(value=self.available_cams[0][0] if self.available_cams else "0")
        cam_names = [f"{idx} - {name}" for idx, name in self.available_cams]
        self.cam_menu = OptionMenu(root, self.selected_cam, *cam_names)
        self.cam_menu.pack(pady=5)

        # Dropdown mode (Entry / Exit)
        self.mode_var = StringVar(value="entry")
        mode_menu = OptionMenu(root, self.mode_var, "entry", "exit")
        mode_menu.pack(pady=5)

        self.start_button = Button(root, text="Restart Camera", command=self.start_camera)
        self.start_button.pack(pady=5)

        self.video_label = Label(root)
        self.video_label.pack()

        self.status_label = Label(root, text="Menyalakan kamera...", font=("Arial", 14))
        self.status_label.pack(pady=10)

        self.welcome_label = Label(root, text="", font=("Arial", 18, "bold"), fg="green")
        self.welcome_label.pack(pady=10)

        self.quit_button = Button(root, text="Quit", command=self.close)
        self.quit_button.pack(pady=5)

        # ArUco setup
        self.aruco_dict = cv2.aruco.getPredefinedDictionary(cv2.aruco.DICT_6X6_1000)
        parameters = cv2.aruco.DetectorParameters()
        self.detector = cv2.aruco.ArucoDetector(self.aruco_dict, parameters)

        self.cap = None
        self.last_ids = None
        self.scanning = False

        # Jalankan HTTP server di background
        threading.Thread(target=start_http_server, args=(self,), daemon=True).start()

        # Aktifkan kamera awal
        self.root.after(500, self.start_camera)

    def detect_cameras(self, max_test=5):
        cams = []
        for i in range(max_test):
            cap = cv2.VideoCapture(i, cv2.CAP_DSHOW)
            if cap.isOpened():
                backend_name = cap.getBackendName()
                name = f"{backend_name}" if backend_name else f"Camera {i}"
                cams.append((str(i), name))
                cap.release()
        return cams

    def start_camera(self):
        cam_index = int(self.selected_cam.get().split(" - ")[0]) if " - " in self.selected_cam.get() else int(self.selected_cam.get())
        if self.cap and self.cap.isOpened():
            self.cap.release()

        self.cap = cv2.VideoCapture(cam_index, cv2.CAP_DSHOW)
        if not self.cap.isOpened():
            self.status_label.config(text=f"Kamera {cam_index} tidak bisa diakses")
            return

        self.status_label.config(text=f"Kamera {cam_index} aktif. Mode: {self.mode_var.get().upper()}")
        self.welcome_label.config(text="")
        self.last_ids = None
        self.scanning = True
        self.update_frame()

    def stop_camera(self):
        if self.cap and self.cap.isOpened():
            self.cap.release()
        self.scanning = False
        self.status_label.config(text="Kamera dinonaktifkan sementara")

    def update_frame(self):
        if not self.scanning:
            return

        ret, frame = self.cap.read()
        if not ret:
            self.status_label.config(text="Gagal membaca kamera")
            return

        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        corners, ids, _ = self.detector.detectMarkers(gray)

        if ids is not None:
            cv2.aruco.drawDetectedMarkers(frame, corners, ids)
            tag_id = int(ids.flatten()[0])
            if self.last_ids is None or tag_id != self.last_ids:
                self.last_ids = tag_id
                self.status_label.config(text=f"Marker ID: {tag_id} terdeteksi.")
                print("Marker terdeteksi dengan ID:", tag_id)
                self.scanning = False
                threading.Thread(target=self.handle_marker_detected, args=(tag_id,), daemon=True).start()
        else:
            self.status_label.config(text="Tidak ada marker")
            self.last_ids = None

        rgb_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        img = Image.fromarray(rgb_frame)
        imgtk = ImageTk.PhotoImage(image=img)
        self.video_label.imgtk = imgtk
        self.video_label.configure(image=imgtk)

        if self.scanning:
            self.root.after(10, self.update_frame)

    def handle_marker_detected(self, tag_id):
        """Kirim data marker sesuai mode"""
        mode = self.mode_var.get()
        api_url = API_ENTRY_GATE if mode == "entry" else API_EXIT_GATE
        gate_action = "/buka_masuk" if mode == "entry" else "/buka_keluar"

        try:
            print(f"[API] Mode {mode.upper()} - Kirim ke {api_url} dengan id {tag_id}")
            response = requests.post(api_url, json={"id": tag_id}, timeout=5)
            print("API Response:", response.status_code, response.text)

            # Jika sukses (200)
            if response.status_code == 200:
                data = response.json()
                user_data = data.get("data") or data.get("user") or {}
                name = user_data.get("name", "Tidak diketahui")
                self.welcome_label.config(
                    text=f"{'Selamat datang' if mode == 'entry' else 'Sampai jumpa'}, {name}!"
                )
                self.status_label.config(text=f"User: {name} (Tag ID: {tag_id})")

                gate_client = requests.post(API_DEVICE + gate_action, json={}, timeout=5)
                print("Response buka gate:", gate_client.status_code, gate_client.text)

            # Jika error 400 dengan "User is already parked in"
            elif response.status_code == 400:
                data = response.json()
                msg = data.get("message", "").lower()
                if data.get("status") == "error" and "already parked in" in msg:
                    self.status_label.config(text="❌ User sudah parkir di dalam.")
                    self.welcome_label.config(text="")
                    print("[INFO] User sudah parkir di dalam. Kamera akan aktif lagi.")
                    self.root.after(2000, self.start_camera)  # aktifkan ulang kamera setelah 2 detik
                    return
                else:
                    self.status_label.config(text=f"Error 400: {data.get('message', 'Unknown error')}")
                    self.welcome_label.config(text="")
                    self.root.after(2000, self.start_camera)
                    return

            # Jika parking penuh
            elif mode == "entry" and response.status_code == 403:
                data = response.json()
                message = data.get("message", "Parking is full")
                if data.get("status") == "error" and "full" in message.lower():
                    self.status_label.config(text="Gagal masuk: Parking is full ❌")
                    self.welcome_label.config(text="")
                    print("[INFO] Parking penuh. Tidak lanjut proses.")
                    self.root.after(2000, self.start_camera)
                    return

            else:
                self.status_label.config(text=f"Gagal: HTTP {response.status_code}")
                self.welcome_label.config(text="")
                self.root.after(2000, self.start_camera)

        except Exception as e:
            print("Gagal kirim data marker:", e)
            self.status_label.config(text=f"Error: {e}")
            self.root.after(2000, self.start_camera)

        finally:
            self.root.after(0, self.stop_camera)

    def handle_external_request(self, tag_id):
        self.status_label.config(text=f"Request masuk dari alat: ID {tag_id}")
        self.welcome_label.config(text=f"Selamat datang dari alat, ID {tag_id}")

    def activate_camera_from_api(self):
        self.status_label.config(text="Perintah aktifkan kamera diterima...")
        self.start_camera()

    def close(self):
        if self.cap and self.cap.isOpened():
            self.cap.release()
        self.root.destroy()


# =====================================
# ENTRY POINT
# =====================================
if __name__ == "__main__":
    root = tk.Tk()
    app = ArucoApp(root)
    root.mainloop()
