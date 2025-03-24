import os
# Thêm biến môi trường để tạm thời bỏ qua lỗi OpenMP
os.environ["KMP_DUPLICATE_LIB_OK"] = "TRUE"

from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.responses import HTMLResponse
from fastapi.staticfiles import StaticFiles
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from ultralytics import YOLO
import cv2
import numpy as np
import base64
from langchain_google_genai import ChatGoogleGenerativeAI
from langchain.chains import RetrievalQA
from langchain.prompts import PromptTemplate
from langchain_community.embeddings import GPT4AllEmbeddings
from langchain_community.vectorstores import FAISS
from dotenv import load_dotenv

# Khởi tạo ứng dụng FastAPI
app = FastAPI()

# Thêm middleware CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Mount thư mục static với đường dẫn tuyệt đối
app.mount("/static", StaticFiles(directory="D:/thuc_tap_tot_nghiep/project_directory/static"), name="static")

# Load API key từ file .env
load_dotenv(override=True)
api_key = os.getenv("GEMINI_API_KEY")

if not api_key:
    raise ValueError("API Key không hợp lệ. Vui lòng kiểm tra file .env hoặc đặt GOOGLE_API_KEY.")

# Đường dẫn đến vector database
vector_db_path = "D:/thuc_tap_tot_nghiep/vectorstores/db_faiss"

# Load mô hình YOLO
try:
    model = YOLO(r'D:/thuc_tap_tot_nghiep/runs/runs/train/train_money/weights/best.pt')
    print("Mô hình YOLO đã được tải thành công.")
    print("Classes supported by the model:", model.names)
except Exception as e:
    print(f"Lỗi khi tải mô hình YOLO: {e}")
    raise

# Hàm khởi tạo LLM (Gemini)
def load_llm(api_key):
    try:
        llm = ChatGoogleGenerativeAI(
            model="gemini-1.5-pro",
            google_api_key=api_key,
            temperature=0.01,
            max_output_tokens=1024
        )
        print("Mô hình Gemini đã được khởi tạo thành công.")
        return llm
    except Exception as e:
        print(f"Lỗi khi khởi tạo mô hình Gemini: {e}")
        return None

# Hàm đọc vector database
def read_vectors_db():
    try:
        embedding_model = GPT4AllEmbeddings(model_file="model/all-MiniLM-L6-v2-f16.gguf")
        db = FAISS.load_local(vector_db_path, embedding_model, allow_dangerous_deserialization=True)
        print("Vector database đã được đọc thành công.")
        return db
    except Exception as e:
        print(f"Lỗi khi đọc Vector Database: {e}")
        return None

# Tạo prompt cho LangChain
def create_prompt(template):
    return PromptTemplate(template=template, input_variables=["context", "question"])

# Tạo chuỗi QA với LangChain
def create_qa_chain(prompt, llm, db):
    if llm is None or db is None:
        return None
    return RetrievalQA.from_chain_type(
        llm=llm,
        chain_type="stuff",
        retriever=db.as_retriever(search_kwargs={"k": 3}),
        return_source_documents=False,
        chain_type_kwargs={'prompt': prompt}
    )

# Khởi tạo LangChain và vector database
db = read_vectors_db()
llm = load_llm(api_key)
template = """<|im_start|>system
Sử dụng thông tin sau đây để trả lời câu hỏi. Nếu không biết, hãy nói 'Tôi không biết'.
{context}<|im_end|>
<|im_start|>user
{question}<|im_end|>
<|im_start|>assistant"""
prompt = create_prompt(template)
llm_chain = create_qa_chain(prompt, llm, db)

if llm_chain is None:
    print("Cảnh báo: LangChain không được khởi tạo. Tính năng chatbox sẽ không hoạt động.")

# Endpoint GET / để trả về giao diện web
@app.get("/", response_class=HTMLResponse)
async def read_root():
    with open("D:/thuc_tap_tot_nghiep/project_directory/static/index.html", "r", encoding="utf-8") as f:
        return f.read()

# Endpoint POST /upload để upload ảnh và nhận diện tiền
@app.post("/upload")
async def upload_file(file: UploadFile = File(...)):
    try:
        print(f"Received file: {file.filename}")
        if not file.content_type.startswith('image/'):
            print("File không phải là ảnh.")
            raise HTTPException(status_code=400, detail="File phải là ảnh (jpg, png, jpeg)")

        contents = await file.read()
        print(f"File size: {len(contents)} bytes")
        nparr = np.frombuffer(contents, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            print("Không thể đọc file ảnh.")
            raise HTTPException(status_code=400, detail="Không thể đọc file ảnh")

        results = model(img)
        print("YOLO detection results:", results[0].boxes)
        if len(results[0].boxes) == 0:
            print("No objects detected in the image.")

        img_with_boxes = results[0].plot()
        detections = []
        for r in results[0].boxes:
            class_name = results[0].names[int(r.cls)]
            try:
                value_str = class_name.lower().replace("k", "").replace(".", "").replace(",", "")
                value = int(value_str) * 1000
            except ValueError:
                value = class_name

            detection = {
                "class": class_name,
                "value": value,
                "confidence": float(r.conf),
                "box": [float(x) for x in r.xyxy[0].tolist()]
            }
            detections.append(detection)

        _, buffer = cv2.imencode('.jpg', img_with_boxes)
        jpg_as_text = base64.b64encode(buffer).decode('utf-8')

        print(f"Returning {len(detections)} detections.")
        return JSONResponse(content={
            "message": "Nhận diện thành công",
            "filename": file.filename,
            "image_base64": jpg_as_text,
            "detections": detections
        }, status_code=200)

    except Exception as e:
        print(f"Error in /upload endpoint: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Lỗi: {str(e)}")

# Endpoint POST /chat để xử lý câu hỏi từ chatbox
@app.post("/chat")
async def chat(request: dict):
    try:
        question = request.get("query")
        print(f"Received question: {question}")
        if not question:
            print("Câu hỏi không được để trống.")
            raise HTTPException(status_code=400, detail="Câu hỏi không được để trống")

        if llm_chain is None:
            print("LangChain không được khởi tạo.")
            raise HTTPException(status_code=500, detail="Tính năng chatbox hiện không khả dụng do lỗi khởi tạo LangChain.")

        response = llm_chain.invoke({"query": question})
        print(f"Response from LangChain: {response}")
        return JSONResponse(content={"result": response["result"]}, status_code=200)
    except Exception as e:
        print(f"Error in /chat endpoint: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Lỗi: {str(e)}")

# Chạy server
if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)