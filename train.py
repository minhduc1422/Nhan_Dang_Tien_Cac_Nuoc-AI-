from ultralytics import YOLO

# Tải model YOLOv8 (bản nhỏ, có thể thử với 'yolov8m.pt' hoặc 'yolov8l.pt' nếu máy mạnh)
model = YOLO('yolov8n.pt')  

# Huấn luyện mô hình
model.train(
    data='D:/thuc_tap_tot_nghiep/DATA_MONEY/dataset1/data.yaml',
    epochs=100,
    imgsz=640,
    batch=25,
    save=True,
    project='runs/train',
    name='train_money',
)

