from ultralytics import YOLO
import cv2
import numpy as np
from config import model1_path, model2_path, logger

# Load YOLOv8 models
try:
    model1 = YOLO(model1_path)
    logger.info("Mô hình 1 (ThaiLan) đã được tải thành công.")
    model2 = YOLO(model2_path)
    logger.info("Mô hình 2 (VN) đã được tải thành công.")
except Exception as e:
    raise RuntimeError(f"Lỗi khi tải mô hình YOLO: {e}")

def process_image_with_yolo(image: np.ndarray, retry=False):
    conf_threshold = 0.5 if not retry else 0.3
    iou_threshold = 0.5

    results1 = model1(image, conf=conf_threshold, iou=iou_threshold)
    results2 = model2(image, conf=conf_threshold, iou=iou_threshold)
    
    combined_detections = []
    
    if results1 and results1[0].boxes:
        for r in results1[0].boxes:
            class_name = results1[0].names[int(r.cls)]
            confidence = float(r.conf)
            box = r.xyxy[0].tolist()
            combined_detections.append({"class": class_name, "confidence": confidence, "box": box, "model": "ThaiLan"})
    
    if results2 and results2[0].boxes:
        for r in results2[0].boxes:
            class_name = results2[0].names[int(r.cls)]
            confidence = float(r.conf)
            box = r.xyxy[0].tolist()
            combined_detections.append({"class": class_name, "confidence": confidence, "box": box, "model": "VN"})
    
    annotated_image = image.copy()
    for detection in combined_detections:
        x1, y1, x2, y2 = map(int, detection["box"])
        label = f"{detection['model']}: {detection['class']} ({detection['confidence']:.2f})"
        color = (0, 255, 0) if detection["model"] == "ThaiLan" else (255, 0, 0)
        cv2.rectangle(annotated_image, (x1, y1), (x2, y2), color, 2)
        cv2.putText(annotated_image, label, (x1, y1 - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.5, color, 2)
    
    if not combined_detections and not retry:
        logger.info("Không phát hiện được tờ tiền, thử lại với ngưỡng thấp hơn.")
        return process_image_with_yolo(image, retry=True)
    
    return annotated_image, combined_detections