from ultralytics import YOLO
import cv2

# Load mô hình YOLO đã huấn luyện
model = YOLO('runs/train/train_money/weights/best.pt')

# Mở camera
cap = cv2.VideoCapture(0)

if not cap.isOpened():
    print("Không thể mở camera.")
    exit()

while True:
    ret, frame = cap.read()
    if not ret:
        print("Không thể đọc video từ camera.")
        break
    
    # Thực hiện nhận diện
    results = model(frame)
    
    # Vẽ bounding boxes lên ảnh
    frame_with_boxes = results[0].plot()

    # Hiển thị kết quả
    cv2.imshow('Detected Money', frame_with_boxes)

    # Nhấn 'q' để thoát
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

cap.release()
cv2.destroyAllWindows()
