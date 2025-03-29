import os
os.environ["KMP_DUPLICATE_LIB_OK"] = "TRUE"

from ultralytics import YOLO
import cv2
import numpy as np
from fastapi import FastAPI, UploadFile, File, Response, Request
from fastapi.staticfiles import StaticFiles
from starlette.responses import FileResponse
import uvicorn
from io import BytesIO
from pathlib import Path
from langchain_google_genai import ChatGoogleGenerativeAI
from langchain.chains import RetrievalQA
from langchain.prompts import PromptTemplate
from langchain_community.embeddings import GPT4AllEmbeddings
from langchain_community.vectorstores import FAISS
from dotenv import load_dotenv
from fastapi import HTTPException

# Load API key từ file .env
load_dotenv(override=True)
api_key = os.getenv("GEMINI_API_KEY")
if not api_key:
    raise ValueError("API Key không hợp lệ. Vui lòng kiểm tra file .env hoặc đặt GEMINI_API_KEY.")

# Đường dẫn
vector_db_path = "D:/thuc_tap_tot_nghiep/vectorstores/db_faiss"
model1_path = r"D:\thuc_tap_tot_nghiep\run\train\Thailan\weights\best.pt"
model2_path = r"D:\thuc_tap_tot_nghiep\runs\runs\train\train_money7\weights\best.pt"

# Kiểm tra file tồn tại
for path in [model1_path, model2_path]:
    if not os.path.exists(path):
        print(f"File không tồn tại: {path}")
        exit()

# Khởi tạo FastAPI
app = FastAPI()
BASE_DIR = Path(__file__).resolve().parent
STATIC_DIR = BASE_DIR / "static"
app.mount("/static", StaticFiles(directory=STATIC_DIR), name="static")

# Load mô hình YOLOv8
try:
    model1 = YOLO(model1_path)
    print("Mô hình 1 (Campuchia) đã được tải thành công.")
    print("Các lớp của mô hình 1:", model1.names)
    model2 = YOLO(model2_path)
    print("Mô hình 2 (VN) đã được tải thành công.")
    print("Các lớp của mô hình 2:", model2.names)
except Exception as e:
    print(f"Lỗi khi tải mô hình YOLO: {e}")
    exit()

# Load LLM và vector database cho chatbot
def load_llm(api_key):
    try:
        llm = ChatGoogleGenerativeAI(
            model="gemini-1.5-pro",
            google_api_key=api_key,
            temperature=0.01,
            max_output_tokens=1024
        )
        return llm
    except Exception as e:
        raise RuntimeError(f"Lỗi khi khởi tạo mô hình LLM: {e}")

def read_vectors_db():
    try:
        embedding_model = GPT4AllEmbeddings(model_file="D:/thuc_tap_tot_nghiep/model/all-MiniLM-L6-v2-f16.gguf")
        db = FAISS.load_local(vector_db_path, embedding_model, allow_dangerous_deserialization=True)
        return db
    except Exception as e:
        raise RuntimeError(f"Lỗi khi đọc Vector Database: {e}")

def create_prompt(template):
    return PromptTemplate(template=template, input_variables=["context", "question"])

def create_qa_chain(prompt, llm, db):
    return RetrievalQA.from_chain_type(
        llm=llm,
        chain_type="stuff",
        retriever=db.as_retriever(search_kwargs={"k": 3}),
        return_source_documents=False,
        chain_type_kwargs={'prompt': prompt}
    )

try:
    db = read_vectors_db()
    llm = load_llm(api_key)
    template = """<|im_start|>system
    Sử dụng thông tin sau đây để trả lời câu hỏi. Nếu không biết, hãy nói 'Tôi không biết'.
    {context}
    <|im_start|>user
    {question}
    <|im_start|>assistant"""
    prompt = create_prompt(template)
    llm_chain = create_qa_chain(prompt, llm, db)
except Exception as e:
    print(f"Lỗi khi khởi tạo chatbot: {e}")
    exit()

# Hàm xử lý ảnh
def process_image(image: np.ndarray):
    results1 = model1(image)
    results2 = model2(image)
    combined_detections = []
    for r in results1[0].boxes:
        class_name = results1[0].names[int(r.cls)]
        confidence = float(r.conf)
        box = r.xyxy[0].tolist()
        combined_detections.append({"class": class_name, "confidence": confidence, "box": box, "model": "Campuchia"})
    for r in results2[0].boxes:
        class_name = results2[0].names[int(r.cls)]
        confidence = float(r.conf)
        box = r.xyxy[0].tolist()
        combined_detections.append({"class": class_name, "confidence": confidence, "box": box, "model": "VN"})
    annotated_image = image.copy()
    for detection in combined_detections:
        x1, y1, x2, y2 = map(int, detection["box"])
        label = f"{detection['model']}: {detection['class']} ({detection['confidence']:.2f})"
        color = (0, 255, 0) if detection["model"] == "Campuchia" else (255, 0, 0)
        cv2.rectangle(annotated_image, (x1, y1), (x2, y2), color, 2)
        cv2.putText(annotated_image, label, (x1, y1 - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.5, color, 2)
    return annotated_image

@app.get("/")
async def root():
    return FileResponse(STATIC_DIR / "index.html")

@app.post("/detect_money")
async def detect_money(file: UploadFile = File(...)):
    contents = await file.read()
    nparr = np.frombuffer(contents, np.uint8)
    image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    if image is None:
        return {"error": "Không thể đọc file ảnh!"}
    annotated_image = process_image(image)
    ret, buffer = cv2.imencode('.jpg', annotated_image)
    if not ret:
        return {"error": "Không thể mã hóa ảnh!"}
    return Response(content=buffer.tobytes(), media_type="image/jpeg")

@app.post("/chat")
async def chat(request: Request):
    data = await request.json()
    question = data.get("question")
    if not question:
        raise HTTPException(status_code=400, detail="Vui lòng cung cấp câu hỏi!")
    try:
        response = llm_chain.invoke({"query": question})
        return {"response": response["result"]}
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Lỗi khi xử lý câu hỏi: {e}")

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8000)