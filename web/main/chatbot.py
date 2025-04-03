from langchain_google_genai import ChatGoogleGenerativeAI
from langchain.chains import RetrievalQA
from langchain.prompts import PromptTemplate
from langchain_community.embeddings import GPT4AllEmbeddings
from langchain_community.vectorstores import FAISS
from config import api_key, BASE_DIR, vector_db_path, logger
from googlesearch import search
import requests
from bs4 import BeautifulSoup

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
        embedding_model = GPT4AllEmbeddings(model_file=str(BASE_DIR / "model/all-MiniLM-L6-v2-f16.gguf"))
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

def fetch_top_5_web_content(query):
    try:
        logger.info(f"Tìm kiếm web với truy vấn: {query}")
        urls = list(search(query, num_results=10, lang="vi"))
        web_contents = []
        for url in urls[:5]:
            try:
                response = requests.get(url, timeout=5)
                response.raise_for_status()
                soup = BeautifulSoup(response.text, 'html.parser')
                text = ' '.join(p.get_text() for p in soup.find_all('p') if p.get_text().strip())
                if text and len(text) > 100:
                    web_contents.append({"url": url, "content": text[:2000]})
                    logger.info(f"Đã lấy nội dung từ: {url}")
            except Exception as e:
                logger.error(f"Lỗi khi truy xuất {url}: {e}")
        return web_contents if web_contents else [{"url": "", "content": "Không tìm thấy nội dung từ web."}]
    except Exception as e:
        logger.error(f"Lỗi khi tìm kiếm Google: {e}")
        return [{"url": "", "content": f"Lỗi khi tìm kiếm web: {e}"}]

def process_with_gemini(web_contents, query):
    try:
        documents = [content["content"] for content in web_contents if content["content"].strip()]
        if not documents:
            return "Không có nội dung hợp lệ từ web để xử lý."
        
        embedding_model = GPT4AllEmbeddings(model_file=str(BASE_DIR / "model/all-MiniLM-L6-v2-f16.gguf"))
        temp_db = FAISS.from_texts(documents, embedding_model)
        
        qa_chain = RetrievalQA.from_chain_type(
            llm=llm,
            chain_type="stuff",
            retriever=temp_db.as_retriever(search_kwargs={"k": 3}),
            return_source_documents=False,
            chain_type_kwargs={'prompt': PromptTemplate(
                template="Bạn là một trợ lý thông minh. Sử dụng thông tin từ web để trả lời câu hỏi ngắn gọn và chính xác.\nContext: {context}\nQuestion: {question}\nAnswer:",
                input_variables=["context", "question"]
            )}
        )
        
        response = qa_chain.invoke({"query": query})
        return response["result"].strip()
    except Exception as e:
        logger.error(f"Lỗi trong process_with_gemini: {e}")
        if "429" in str(e) or "quota" in str(e):
            return "Đã vượt quá giới hạn quota của Gemini API. Vui lòng thử lại sau hoặc kiểm tra tài khoản của bạn."
        return f"Lỗi khi xử lý với Gemini: {e}"

# Initialize chatbot components
try:
    db = read_vectors_db()
    llm = load_llm(api_key)
    embedding_model = GPT4AllEmbeddings(model_file=str(BASE_DIR / "model/all-MiniLM-L6-v2-f16.gguf"))
    template = """<|im_start|>system
    Sử dụng thông tin sau đây để trả lời câu hỏi. Nếu không biết, hãy nói 'Tôi không biết'.
    {context}
    <|im_start|>user
    {question}
    <|im_start|>assistant"""
    prompt = create_prompt(template)
    llm_chain = create_qa_chain(prompt, llm, db)
except Exception as e:
    raise RuntimeError(f"Lỗi khi khởi tạo chatbot: {e}")