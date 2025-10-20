import tkinter as tk
from tkinter import Label, Button, OptionMenu, StringVar
from PIL import Image, ImageTk
import threading
import cv2
from .camera import detect_cameras, open_camera
from .detector import ArucoDetector
from .network import send_tag_data

class ArucoApp:
    def __init__(self, root):
        self.root = root
        self.root.title("ArUco Detector")
        self.root.geometry("900x700")

        self.available_cams = detect_cameras()
        self.selected_cam = StringVar(value=self.available_cams[0][0] if self.available_cams else "0")
        cam_names = [f"{idx} - {name}" for idx, name in self.available_cams]

        OptionMenu(root, self.selected_cam, *cam_names).pack(pady=5)
        Button(root, text="Start Camera", command=self.start_camera).pack(pady=5)

        self.video_label = Label(root)
        self.video_label.pack()

        self.status_label = Label(root, text="Pilih kamera lalu klik Start Camera", font=("Arial", 14))
        self.status_label.pack(pady=10)

        self.welcome_label = Label(root, text="", font=("Arial", 18, "bold"), fg="green")
        self.welcome_label.pack(pady=10)

        Button(root, text="Quit", command=self.close).pack(pady=5)

        self.detector = ArucoDetector()
        self.cap = None
        self.last_ids = None
        self.scanning = True

    def start_camera(self):
        cam_index = int(self.selected_cam.get().split(" - ")[0])
        self.cap = open_camera(cam_index)
        if not self.cap:
            self.status_label.config(text=f"Kamera {cam_index} tidak bisa diakses")
            return
        self.status_label.config(text=f"Kamera {cam_index} aktif. Cari marker...")
        self.scanning = True
        self.update_frame()

    def update_frame(self):
        if not self.cap or not self.cap.isOpened() or not self.scanning:
            self.root.after(10, self.update_frame)
            return

        ret, frame = self.cap.read()
        if not ret:
            self.status_label.config(text="Gagal membaca kamera")
            return

        ids, frame = self.detector.detect(frame)
        if ids is not None:
            tag_id = int(ids.flatten()[0])
            if self.last_ids is None or tag_id != self.last_ids:
                self.last_ids = tag_id
                self.status_label.config(text=f"Marker ID: {tag_id} terdeteksi, mengirim data...")
                self.scanning = False
                threading.Thread(target=self.handle_marker, args=(tag_id,), daemon=True).start()
        else:
            self.status_label.config(text="Tidak ada marker")
            self.last_ids = None

        rgb_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        img = Image.fromarray(rgb_frame)
        imgtk = ImageTk.PhotoImage(image=img)
        self.video_label.imgtk = imgtk
        self.video_label.configure(image=imgtk)
        self.root.after(10, self.update_frame)

    def handle_marker(self, tag_id):
        success, data = send_tag_data(tag_id)
        if success:
            user_data = data.get("data") or data.get("user") or {}
            name = user_data.get("name", "Tidak diketahui")
            self.welcome_label.config(text=f"Selamat datang, {name}!")
            self.status_label.config(text=f"User: {name} (Tag ID: {tag_id})")
        else:
            self.welcome_label.config(text="")
            self.status_label.config(text=f"Gagal: {data.get('error')}")
        self.root.after(2500, self.resume_scanning)

    def resume_scanning(self):
        self.last_ids = None
        self.scanning = True
        self.welcome_label.config(text="")
        self.status_label.config(text="Siap untuk scan berikutnya...")

    def close(self):
        if self.cap and self.cap.isOpened():
            self.cap.release()
        self.root.destroy()
