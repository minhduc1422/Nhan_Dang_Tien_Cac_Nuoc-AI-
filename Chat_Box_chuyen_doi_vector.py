import os
from langchain.text_splitter import RecursiveCharacterTextSplitter, CharacterTextSplitter
from langchain_community.document_loaders import TextLoader, DirectoryLoader
from langchain_community.vectorstores import FAISS
from langchain_community.embeddings import GPT4AllEmbeddings

# 🔹 Cấu hình đường dẫn
PDF_DATA_PATH = "D:/thuc_tap_tot_nghiep/DATA_MONEY/data"
VECTOR_DB_PATH = "vectorstores/db_faiss"
MODEL_PATH = r"D:/thuc_tap_tot_nghiep/model/vinallama-7b-chat_q5_0.gguf"

# 🔹 Kiểm tra mô hình có tồn tại không
if not os.path.exists(MODEL_PATH):
    raise FileNotFoundError(f"Lỗi: Không tìm thấy mô hình tại {MODEL_PATH}")

# 🔹 Khởi tạo mô hình nhúng
try:
    embedding_model = GPT4AllEmbeddings(model_file=MODEL_PATH)
except Exception as e:
    raise RuntimeError(f"Lỗi khi tải mô hình GPT4All: {e}")

def create_db_from_text():
    """Tạo cơ sở dữ liệu vector FAISS từ văn bản tĩnh."""
    raw_text = """Hệ thống hoán đổi khuôn mặt (Swap Face) là một công nghệ xử lý hình ảnh sử dụng trí tuệ nhân tạo (AI) và 
    học sâu (Deep Learning) để thay thế khuôn mặt của một người trong ảnh hoặc video bằng khuôn mặt của người khác. Công nghệ 
    này đã phát triển mạnh mẽ nhờ vào các thuật toán thị giác máy tính hiện đại, đặc biệt là các mô hình mạng nơ-ron tích chập 
    (CNN) và mô hình học sâu như Autoencoder hoặc GAN (Generative Adversarial Network)."""

    text_splitter = CharacterTextSplitter(
        separator="\n",
        chunk_size=500,
        chunk_overlap=50,
        length_function=len
    )

    chunks = text_splitter.split_text(raw_text)
    
    # 🔹 Kiểm tra dữ liệu có hợp lệ không
    if not chunks:
        raise ValueError("Lỗi: Không có đoạn văn bản nào sau khi tách.")

    # 🔹 Tạo embeddings
    embeddings = [embedding_model.embed_query(text) for text in chunks]

    if not embeddings:
        raise ValueError("Lỗi: Không tạo được embeddings từ văn bản.")

    # 🔹 Tạo FAISS Database
    db = FAISS.from_texts(texts=chunks, embedding=embedding_model)
    db.save_local(VECTOR_DB_PATH)
    print(f"✅ Cơ sở dữ liệu vector FAISS đã được lưu tại: {VECTOR_DB_PATH}")
    return db

def create_db_from_files():
    """Tạo cơ sở dữ liệu vector FAISS từ các file trong thư mục `data/`."""
    # 🔹 Kiểm tra thư mục có tồn tại không
    if not os.path.exists(PDF_DATA_PATH):
        raise FileNotFoundError(f"Lỗi: Không tìm thấy thư mục dữ liệu tại {PDF_DATA_PATH}")

    # 🔹 Load các file .txt
    loader = DirectoryLoader(PDF_DATA_PATH, glob="*.txt", loader_cls=lambda path: TextLoader(path, encoding="utf-8"))
    documents = loader.load()

    if not documents:
        raise ValueError(f"Lỗi: Không tìm thấy file .txt nào trong thư mục {PDF_DATA_PATH}")
    print(f"✅ Đã load {len(documents)} tài liệu.")

    # 🔹 Cắt văn bản thành các đoạn nhỏ
    text_splitter = RecursiveCharacterTextSplitter(chunk_size=512, chunk_overlap=50)
    chunks = text_splitter.split_documents(documents)

    if not chunks:
        raise ValueError("Lỗi: Không có đoạn văn bản nào sau khi tách.")

    print(f"✅ Đã tách thành {len(chunks)} đoạn văn bản.")

    # 🔹 Tạo embeddings
    chunk_texts = [chunk.page_content for chunk in chunks]
    embeddings = [embedding_model.embed_query(text) for text in chunk_texts]

    if not embeddings:
        raise ValueError("Lỗi: Không tạo được embeddings từ văn bản.")

    # 🔹 Kiểm tra embeddings có rỗng không
    if len(embeddings) == 0 or len(embeddings[0]) == 0:
        raise ValueError("Lỗi: Embeddings rỗng. Hãy kiểm tra lại mô hình GPT4All.")

    # 🔹 Tạo FAISS Database
    db = FAISS.from_documents(chunks, embedding_model)
    db.save_local(VECTOR_DB_PATH)
    print(f"✅ Cơ sở dữ liệu vector FAISS đã được lưu tại: {VECTOR_DB_PATH}")
    return db

# 🔹 Chạy hàm chính
if __name__ == "__main__":
    create_db_from_files()