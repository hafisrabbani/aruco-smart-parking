import cv2

def detect_cameras(max_test=5):
    """Deteksi kamera aktif dan kembalikan list (index, name)."""
    cams = []
    for i in range(max_test):
        cap = cv2.VideoCapture(i, cv2.CAP_DSHOW)
        if cap.isOpened():
            name = f"Camera {i}"
            backend_name = cap.getBackendName()
            name = backend_name if backend_name else name
            cams.append((str(i), name))
            cap.release()
    return cams

def open_camera(index):
    """Buka kamera berdasarkan index."""
    cap = cv2.VideoCapture(index, cv2.CAP_DSHOW)
    return cap if cap.isOpened() else None
