# from ultralytics import YOLO
# import cv2
# import numpy as np
# from config import logger
# import base64
# import os
# from pathlib import Path
# import pytesseract
# from PIL import Image
# import torch
# from torch.nn import Sequential
# from ultralytics.nn.tasks import DetectionModel
# from ultralytics.nn.modules import Conv  # Sửa import cho phiên bản mới

# # Định nghĩa đường dẫn gốc cho mô hình trong container
# MODEL_ROOT = Path(os.getenv("MODEL_ROOT", "/app/run/train"))

# # Đường dẫn tuyệt đối cho 11 mô hình trong container
# model_paths = {
#     "Việt Nam": MODEL_ROOT / "VietNam" / "weights" / "best.pt",
#     "Thái Lan": MODEL_ROOT / "ThaiLan" / "weights" / "best.pt",
#     "Brunei": MODEL_ROOT / "Brunei" / "weights" / "best.pt",
#     "Campuchia": MODEL_ROOT / "Campuchia" / "weights" / "best.pt",
#     "Indonesia": MODEL_ROOT / "INDONESIA" / "weights" / "best.pt",
#     "Lào": MODEL_ROOT / "LAO" / "weights" / "best.pt",
#     "Myanmar": MODEL_ROOT / "Myanmar" / "weights" / "best.pt",
#     "Philippines": MODEL_ROOT / "Philippines" / "Philippines2" / "weights" / "best.pt",
#     "Singapore": MODEL_ROOT / "Singapore" / "weights" / "best.pt",
#     "Đông Timor": MODEL_ROOT / "USD(Dong_timor)" / "weights" / "best.pt",
#     "Malaysia": MODEL_ROOT / "Malaysia" / "weights" / "best.pt",
# }

# # Ngưỡng xác nhận mệnh giá và IoU
# CONFIDENCE_THRESHOLD = 0.5
# IOU_THRESHOLD = 0.4

# # Load tất cả mô hình
# models = {}
# for country, path in model_paths.items():
#     if not path.exists():
#         logger.warning(f"File không tồn tại: {path}. Bỏ qua mô hình {country}.")
#         continue
#     try:
#         models[country] = YOLO(path)
#         logger.info(f"Mô hình {country} đã được tải thành công.")
#         logger.info(f"Các lớp của mô hình {country}: {models[country].names}")
#     except Exception as e:
#         logger.error(f"Lỗi khi tải mô hình {country}: {e}")
#         continue

# # Hàm tiền xử lý ảnh để cải thiện chất lượng
# def preprocess_image(image):
#     gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
#     clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
#     enhanced = clahe.apply(gray)
#     denoised = cv2.fastNlMeansDenoising(enhanced)
#     processed_image = cv2.cvtColor(denoised, cv2.COLOR_GRAY2BGR)
#     return processed_image

# # Hàm tính IoU giữa hai hộp giới hạn
# def calculate_iou(box1, box2):
#     x1, y1, x2, y2 = box1
#     x1_b, y1_b, x2_b, y2_b = box2
#     xi1 = max(x1, x1_b)
#     yi1 = max(y1, y1_b)
#     xi2 = min(x2, x2_b)
#     yi2 = min(y2, y2_b)  # Đã sửa lỗi trước đó
#     inter_area = max(0, xi2 - xi1) * max(0, yi2 - yi1)
#     box1_area = (x2 - x1) * (y2 - y1)
#     box2_area = (x2_b - x1_b) * (y2_b - y1_b)
#     union_area = box1_area + box2_area - inter_area
#     return inter_area / union_area if union_area > 0 else 0

# # Hàm trích xuất văn bản từ ảnh
# def extract_text_from_image(image):
#     try:
#         gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
#         clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
#         enhanced = clahe.apply(gray)
#         denoised = cv2.fastNlMeansDenoising(enhanced)
#         image_pil = Image.fromarray(denoised)
#         text = pytesseract.image_to_string(image_pil, lang='eng+vie')
#         return text.lower()
#     except Exception as e:
#         logger.error(f"Lỗi khi trích xuất văn bản: {e}")
#         return ""

# # Hàm nhận diện từ ảnh
# def process_image_with_yolo(image: np.ndarray):
#     processed_image = preprocess_image(image)
#     annotated_image = image.copy()
#     detections = []
#     text = extract_text_from_image(image)
#     logger.info(f"Văn bản trích xuất: {text}")

#     country_priority = None
#     if "bank negara malaysia" in text or "ringgit" in text:
#         country_priority = "Malaysia"
#     elif "đồng" in text or "việt nam" in text:
#         country_priority = "Việt Nam"
#     elif "baht" in text or "thái lan" in text:
#         country_priority = "Thái Lan"
#     elif "riel" in text or "campuchia" in text:
#         country_priority = "Campuchia"
#     elif "kip" in text or "lào" in text:
#         country_priority = "Lào"
#     elif "kyat" in text or "myanmar" in text:
#         country_priority = "Myanmar"
#     elif "peso" in text or "philippines" in text:
#         country_priority = "Philippines"
#     elif "rupiah" in text or "indonesia" in text:
#         country_priority = "Indonesia"
#     elif "đô la brunei" in text or "brunei" in text:
#         country_priority = "Brunei"
#     elif "đô la singapore" in text or "singapore" in text:
#         country_priority = "Singapore"
#     elif "đô la mỹ" in text or "đông timor" in text:
#         country_priority = "Đông Timor"

#     for country, model in models.items():
#         results = model(processed_image)
#         for r in results[0].boxes:
#             confidence = float(r.conf)
#             if confidence < CONFIDENCE_THRESHOLD:
#                 continue
#             class_name = results[0].names[int(r.cls)]
#             x1, y1, x2, y2 = map(int, r.xyxy[0].tolist())
#             detections.append({
#                 "country": country,
#                 "class_name": class_name,
#                 "confidence": confidence,
#                 "box": (x1, y1, x2, y2)
#             })

#     confirmed_detections = []
#     detections = sorted(detections, key=lambda x: x["confidence"], reverse=True)
#     if country_priority:
#         prioritized_detections = [d for d in detections if d["country"] == country_priority]
#         other_detections = [d for d in detections if d["country"] != country_priority]
#         detections = prioritized_detections + other_detections

#     detected_denomination = None
#     for word in text.split():
#         if word.isdigit():
#             detected_denomination = word
#             break

#     if detected_denomination:
#         filtered_detections = []
#         for detection in detections:
#             if detected_denomination in detection["class_name"]:
#                 filtered_detections.append(detection)
#             elif detection["confidence"] > 0.9:
#                 filtered_detections.append(detection)
#         detections = filtered_detections if filtered_detections else detections

#     for detection in detections:
#         box = detection["box"]
#         is_duplicate = False
#         for confirmed in confirmed_detections:
#             iou = calculate_iou(box, confirmed["box"])
#             if iou > IOU_THRESHOLD:
#                 is_duplicate = True
#                 break
#         if not is_duplicate:
#             confirmed_detections.append(detection)

#     for detection in confirmed_detections:
#         country = detection["country"]
#         class_name = detection["class_name"]
#         confidence = detection["confidence"]
#         x1, y1, x2, y2 = detection["box"]
#         label = f"{country}: {class_name} ({confidence:.2f})"
#         cv2.rectangle(annotated_image, (x1, y1), (x2, y2), (0, 255, 0), 2)
#         cv2.putText(annotated_image, label, (x1, y1 - 10),
#                     cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 255, 0), 2)

#     _, buffer = cv2.imencode('.jpg', annotated_image)
#     image_base64 = base64.b64encode(buffer).decode('utf-8')
#     return image_base64, confirmed_detections
#   ctrl + /


from ultralytics import YOLO
import cv2
import numpy as np
from config import logger, model_paths  # Import model_paths từ config
import base64
from pathlib import Path
import pytesseract
from PIL import Image

# Lấy thư mục chứa tệp hiện tại
BASE_DIR = Path(__file__).resolve().parent

# Ngưỡng xác nhận mệnh giá và IoU
CONFIDENCE_THRESHOLD = 0.5
IOU_THRESHOLD = 0.4

# Load tất cả mô hình
models = []
for model_info in model_paths:
    country = model_info["country"]
    path = model_info["path"]
    source = model_info["source"]
    if not path.exists():
        logger.warning(f"File không tồn tại: {path}. Bỏ qua mô hình {country} ({source}).")
        continue
    try:
        model = YOLO(path)
        models.append({"country": country, "model": model, "source": source})
        logger.info(f"Mô hình {country} ({source}) đã được tải thành công.")
        logger.info(f"Các lớp của mô hình {country} ({source}): {model.names}")
    except Exception as e:
        logger.error(f"Lỗi khi tải mô hình {country} ({source}): {e}")
        continue

# Hàm tiền xử lý ảnh để cải thiện chất lượng
def preprocess_image(image):
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    enhanced = clahe.apply(gray)
    denoised = cv2.fastNlMeansDenoising(enhanced)
    processed_image = cv2.cvtColor(denoised, cv2.COLOR_GRAY2BGR)
    return processed_image

# Hàm tính IoU giữa hai hộp giới hạn
def calculate_iou(box1, box2):
    x1, y1, x2, y2 = box1
    x1_b, y1_b, x2_b, y2_b = box2
    xi1 = max(x1, x1_b)
    yi1 = max(y1, y1_b)
    xi2 = min(x2, x2_b)
    yi2 = min(y2, y2_b)
    inter_area = max(0, xi2 - xi1) * max(0, yi2 - yi1)
    box1_area = (x2 - x1) * (y2 - y1)
    box2_area = (x2_b - x1_b) * (y2_b - y1_b)
    union_area = box1_area + box2_area - inter_area
    return inter_area / union_area if union_area > 0 else 0

# Hàm trích xuất văn bản từ ảnh
def extract_text_from_image(image):
    try:
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
        enhanced = clahe.apply(gray)
        denoised = cv2.fastNlMeansDenoising(enhanced)
        image_pil = Image.fromarray(denoised)
        text = pytesseract.image_to_string(image_pil, lang='eng+vie')
        return text.lower()
    except Exception as e:
        logger.error(f"Lỗi khi trích xuất văn bản: {e}")
        return ""

# Hàm nhận diện từ ảnh
def process_image_with_yolo(image: np.ndarray):
    processed_image = preprocess_image(image)
    annotated_image = image.copy()
    detections = []
    text = extract_text_from_image(image)
    logger.info(f"Văn bản trích xuất: {text}")

    # Xác định quốc gia ưu tiên dựa trên văn bản OCR
    country_priority = None
    if "bank negara malaysia" in text or "ringgit" in text:
        country_priority = "Malaysia"
    elif "đồng" in text or "việt nam" in text:
        country_priority = "Việt Nam"
    elif "baht" in text or "thái lan" in text:
        country_priority = "Thái Lan"
    elif "riel" in text or "campuchia" in text:
        country_priority = "Campuchia"
    elif "kip" in text or "lào" in text:
        country_priority = "Lào"
    elif "kyat" in text or "myanmar" in text:
        country_priority = "Myanmar"
    elif "peso" in text or "philippines" in text:
        country_priority = "Philippines"
    elif "rupiah" in text or "indonesia" in text:
        country_priority = "Indonesia"
    elif "đô la brunei" in text or "brunei" in text:
        country_priority = "Brunei"
    elif "đô la singapore" in text or "singapore" in text:
        country_priority = "Singapore"
    elif "đô la mỹ" in text or "đông timor" in text:
        country_priority = "Đông Timor"

    # Nhận diện bằng YOLO (chạy tất cả mô hình)
    for model_info in models:
        country = model_info["country"]
        model = model_info["model"]
        source = model_info["source"]
        results = model(processed_image)
        for r in results[0].boxes:
            confidence = float(r.conf)
            if confidence < CONFIDENCE_THRESHOLD:
                continue
            class_name = results[0].names[int(r.cls)]
            x1, y1, x2, y2 = map(int, r.xyxy[0].tolist())
            detections.append({
                "country": country,
                "class_name": class_name,
                "confidence": confidence,
                "box": (x1, y1, x2, y2),
                "source": source
            })

    # Sắp xếp và ưu tiên detections
    detections = sorted(detections, key=lambda x: x["confidence"], reverse=True)
    if country_priority:
        prioritized_detections = [d for d in detections if d["country"] == country_priority]
        other_detections = [d for d in detections if d["country"] != country_priority]
        detections = prioritized_detections + other_detections

        # So sánh confidence giữa run và runs cho country_priority
        run_detections = [d for d in prioritized_detections if d["source"] == "run"]
        runs_detections = [d for d in prioritized_detections if d["source"] == "runs"]
        if run_detections and runs_detections:
            run_top = run_detections[0]
            runs_top = runs_detections[0]
            logger.info(f"So sánh {country_priority}: run (confidence={run_top['confidence']:.2f}, denomination={run_top['class_name']}) vs runs (confidence={runs_top['confidence']:.2f}, denomination={runs_top['class_name']})")
            confidence_diff = abs(run_top['confidence'] - runs_top['confidence'])
            logger.info(f"Chênh lệch confidence: {confidence_diff:.2f}")
        elif run_detections:
            logger.info(f"Chỉ có detection từ run cho {country_priority}: confidence={run_detections[0]['confidence']:.2f}, denomination={run_detections[0]['class_name']}")
        elif runs_detections:
            logger.info(f"Chỉ có detection từ runs cho {country_priority}: confidence={runs_detections[0]['confidence']:.2f}, denomination={runs_detections[0]['class_name']}")

    # Nhận diện mệnh giá bằng OCR
    ocr_denomination = None
    for word in text.split():
        if word.isdigit():
            ocr_denomination = word
            break

    # Loại bỏ các detection trùng lặp dựa trên IoU
    confirmed_detections = []
    for detection in detections:
        box = detection["box"]
        is_duplicate = False
        for confirmed in confirmed_detections:
            iou = calculate_iou(box, confirmed["box"])
            if iou > IOU_THRESHOLD:
                is_duplicate = True
                break
        if not is_duplicate:
            confirmed_detections.append(detection)

    # So sánh kết quả từ YOLO và OCR
    final_detections = []
    if confirmed_detections:
        top_yolo_detection = confirmed_detections[0]  # Lấy detection có confidence cao nhất
        yolo_country = top_yolo_detection["country"]
        yolo_denomination = top_yolo_detection["class_name"].replace("Ngàn", "").replace("Ngan", "").replace(".", "").strip()
        yolo_confidence = top_yolo_detection["confidence"]
        yolo_source = top_yolo_detection["source"]

        # Kiểm tra sự đồng thuận giữa YOLO và OCR
        if ocr_denomination and ocr_denomination == yolo_denomination:
            final_detections = [top_yolo_detection]
            logger.info(f"YOLO ({yolo_source}) và OCR đồng ý: {yolo_country}: {yolo_denomination}")
        else:
            if yolo_confidence > 0.9:
                final_detections = [top_yolo_detection]
                logger.info(f"Ưu tiên YOLO ({yolo_source}) do confidence cao: {yolo_country}: {yolo_denomination} ({yolo_confidence})")
            elif ocr_denomination:
                from routes import MONEY_DATA
                if yolo_country in MONEY_DATA and ocr_denomination in MONEY_DATA[yolo_country]:
                    ocr_detection = {
                        "country": yolo_country,
                        "class_name": ocr_denomination,
                        "confidence": 0.95,
                        "box": top_yolo_detection["box"],
                        "source": "OCR"
                    }
                    final_detections = [ocr_detection]
                    logger.info(f"Ưu tiên OCR do khớp với MONEY_DATA: {yolo_country}: {ocr_denomination}")
                else:
                    final_detections = [top_yolo_detection]
                    logger.info(f"Giữ YOLO ({yolo_source}) vì OCR không hợp lệ: {yolo_country}: {yolo_denomination}")
            else:
                final_detections = [top_yolo_detection]
                logger.info(f"Không có OCR, sử dụng YOLO ({yolo_source}): {yolo_country}: {yolo_denomination}")
    else:
        if ocr_denomination and country_priority:
            from routes import MONEY_DATA
            if country_priority in MONEY_DATA and ocr_denomination in MONEY_DATA[country_priority]:
                final_detections = [{
                    "country": country_priority,
                    "class_name": ocr_denomination,
                    "confidence": 0.95,
                    "box": (0, 0, image.shape[1], image.shape[0]),
                    "source": "OCR"
                }]
                logger.info(f"Chỉ có OCR hợp lệ: {country_priority}: {ocr_denomination}")
            else:
                logger.info("Không nhận diện được tờ tiền bởi cả YOLO và OCR.")

    # Vẽ bounding box và thêm nhãn lên ảnh
    for detection in final_detections:
        country = detection["country"]
        class_name = detection["class_name"]
        confidence = detection["confidence"]
        x1, y1, x2, y2 = detection["box"]
        source = detection["source"]
        label = f"{country}: {class_name} ({confidence:.2f}, {source})"
        cv2.rectangle(annotated_image, (x1, y1), (x2, y2), (0, 255, 0), 2)
        cv2.putText(annotated_image, label, (x1, y1 - 10),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 255, 0), 2)

    # Mã hóa ảnh thành base64
    _, buffer = cv2.imencode('.jpg', annotated_image)
    image_base64 = base64.b64encode(buffer).decode('utf-8')
    return image_base64, final_detections