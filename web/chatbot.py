# from langchain_google_genai import ChatGoogleGenerativeAI
# from langchain.chains import RetrievalQA
# from langchain.prompts import PromptTemplate
# from langchain_community.embeddings import GPT4AllEmbeddings
# from langchain_community.vectorstores import FAISS
# from config import api_key, BASE_DIR, vector_db_path, logger
# from googlesearch import search
# import requests
# from bs4 import BeautifulSoup

# def load_llm(api_key):
#     try:
#         llm = ChatGoogleGenerativeAI(
#             model="gemini-1.5-pro",
#             google_api_key=api_key,
#             temperature=0.01,
#             max_output_tokens=1024
#         )
#         return llm
#     except Exception as e:
#         raise RuntimeError(f"Lỗi khi khởi tạo mô hình LLM: {e}")

# def read_vectors_db():
#     try:
#         embedding_model = GPT4AllEmbeddings(model_file=str(BASE_DIR / "model/all-MiniLM-L6-v2-f16.gguf"))
#         db = FAISS.load_local(vector_db_path, embedding_model, allow_dangerous_deserialization=True)
#         return db
#     except Exception as e:
#         raise RuntimeError(f"Lỗi khi đọc Vector Database: {e}")

# def create_prompt(template):
#     return PromptTemplate(template=template, input_variables=["context", "question"])

# def create_qa_chain(prompt, llm, db):
#     return RetrievalQA.from_chain_type(
#         llm=llm,
#         chain_type="stuff",
#         retriever=db.as_retriever(search_kwargs={"k": 3}),
#         return_source_documents=False,
#         chain_type_kwargs={'prompt': prompt}
#     )

# def fetch_top_5_web_content(query):
#     try:
#         logger.info(f"Tìm kiếm web với truy vấn: {query}")
#         urls = list(search(query, num_results=10, lang="vi"))
#         web_contents = []
#         for url in urls[:5]:
#             try:
#                 response = requests.get(url, timeout=5)
#                 response.raise_for_status()
#                 soup = BeautifulSoup(response.text, 'html.parser')
#                 text = ' '.join(p.get_text() for p in soup.find_all('p') if p.get_text().strip())
#                 if text and len(text) > 100:
#                     web_contents.append({"url": url, "content": text[:2000]})
#                     logger.info(f"Đã lấy nội dung từ: {url}")
#             except Exception as e:
#                 logger.error(f"Lỗi khi truy xuất {url}: {e}")
#         return web_contents if web_contents else [{"url": "", "content": "Không tìm thấy nội dung từ web."}]
#     except Exception as e:
#         logger.error(f"Lỗi khi tìm kiếm Google: {e}")
#         return [{"url": "", "content": f"Lỗi khi tìm kiếm web: {e}"}]

# def process_with_gemini(web_contents, query, llm):
#     try:
#         documents = [content["content"] for content in web_contents if content["content"].strip()]
#         if not documents:
#             return "Không có nội dung hợp lệ từ web để xử lý."
        
#         embedding_model = GPT4AllEmbeddings(model_file=str(BASE_DIR / "model/all-MiniLM-L6-v2-f16.gguf"))
#         temp_db = FAISS.from_texts(documents, embedding_model)
        
#         qa_chain = RetrievalQA.from_chain_type(
#             llm=llm,
#             chain_type="stuff",
#             retriever=temp_db.as_retriever(search_kwargs={"k": 3}),
#             return_source_documents=False,
#             chain_type_kwargs={'prompt': PromptTemplate(
#                 template="Bạn là một trợ lý thông minh. Sử dụng thông tin từ web để trả lời câu hỏi ngắn gọn và chính xác.\nContext: {context}\nQuestion: {question}\nAnswer:",
#                 input_variables=["context", "question"]
#             )}
#         )
        
#         response = qa_chain.invoke({"query": query})
#         return response["result"].strip()
#     except Exception as e:
#         logger.error(f"Lỗi trong process_with_gemini: {e}")
#         if "429" in str(e) or "quota" in str(e):
#             return "Đã vượt quá giới hạn quota của Gemini API. Vui lòng thử lại sau hoặc kiểm tra tài khoản của bạn."
#         return f"Lỗi khi xử lý với Gemini: {e}"

# # Initialize chatbot components
# try:
#     db = read_vectors_db()
#     llm = load_llm(api_key)
#     template = """<|im_start|>system
#     Sử dụng thông tin sau đây để trả lời câu hỏi. Nếu không biết, hãy nói 'Tôi không biết'.
#     {context}
#     <|im_start|>user
#     {question}
#     <|im_start|>assistant"""
#     prompt = create_prompt(template)
#     llm_chain = create_qa_chain(prompt, llm, db)
# except Exception as e:
#     raise RuntimeError(f"Lỗi khi khởi tạo chatbot: {e}")



import os
import logging
from typing import List, Dict, Any
from langchain_openai import OpenAI
from langchain_google_genai import ChatGoogleGenerativeAI
from langchain.vectorstores import FAISS
from langchain.prompts import PromptTemplate
from langchain.chains import RetrievalQA
from langchain.schema import Document
from googlesearch import search
import requests
from bs4 import BeautifulSoup

logger = logging.getLogger(__name__)

def load_llm(model_name: str, api_key: str) -> Any:
    """
    Tải mô hình ngôn ngữ lớn (LLM) dựa trên tên mô hình và API key.
    """
    try:
        if model_name == "gemini-1.5-pro":
            os.environ["GOOGLE_API_KEY"] = api_key
            llm = ChatGoogleGenerativeAI(
                model="gemini-1.5-pro",
                temperature=0.01,
                max_tokens=1024
            )
        elif model_name == "openai-gpt-4":
            os.environ["OPENAI_API_KEY"] = api_key
            llm = OpenAI(
                model_name="gpt-4",
                temperature=0.01,
                max_tokens=1024
            )
        else:
            raise ValueError(f"Mô hình {model_name} không được hỗ trợ! Hỗ trợ: gemini-1.5-pro, openai-gpt-4")

        # Kiểm tra API key
        test_response = llm.invoke("Kiểm tra kết nối API")
        if not test_response:
            raise ValueError("API key không hợp lệ hoặc không thể kết nối đến dịch vụ!")
        
        logger.info(f"Đã tải LLM: {model_name}")
        return llm
    except Exception as e:
        logger.error(f"Lỗi khi tải LLM {model_name}: {str(e)}")
        raise ValueError(f"Lỗi khi tải LLM: {str(e)}")

def fetch_top_5_web_content(query: str) -> List[Dict[str, str]]:
    """
    Tìm kiếm web và lấy nội dung từ 5 trang hàng đầu.
    """
    try:
        search_results = list(search(query, num_results=5, lang="vi"))
        web_contents = []
        for url in search_results:
            try:
                response = requests.get(url, timeout=5)
                soup = BeautifulSoup(response.text, 'html.parser')
                paragraphs = soup.find_all('p')
                content = ' '.join([p.get_text() for p in paragraphs])[:1000]
                web_contents.append({"url": url, "content": content})
            except Exception as e:
                logger.warning(f"Lỗi khi lấy nội dung từ {url}: {str(e)}")
                continue
        logger.info(f"Tìm thấy {len(web_contents)} nội dung web cho truy vấn: {query}")
        return web_contents
    except Exception as e:
        logger.error(f"Lỗi khi tìm kiếm web: {str(e)}")
        return []

def read_vectors_db() -> FAISS:
    """
    Đọc cơ sở dữ liệu vector (giả định đã được tạo trước).
    """
    try:
        # Giả định FAISS index đã được lưu tại đường dẫn cụ thể
        vector_store = FAISS.load_local("faiss_index", embeddings=None)  # Cần embeddings nếu tái tạo
        logger.info("Đã tải vector database")
        return vector_store
    except Exception as e:
        logger.error(f"Lỗi khi đọc vector database: {str(e)}")
        return FAISS.from_documents([Document(page_content="Không có dữ liệu")], embeddings=None)

def create_prompt(template: str) -> PromptTemplate:
    """
    Tạo prompt template cho QA chain.
    """
    return PromptTemplate(
        input_variables=["context", "question"],
        template=template
    )

def create_qa_chain(prompt: PromptTemplate, llm: Any, db: FAISS, model_name: str) -> RetrievalQA:
    """
    Tạo chuỗi QA dựa trên LLM và vector database.
    """
    try:
        qa_chain = RetrievalQA.from_chain_type(
            llm=llm,
            chain_type="stuff",
            retriever=db.as_retriever(search_kwargs={"k": 5}),
            chain_type_kwargs={"prompt": prompt}
        )
        logger.info(f"Đã tạo QA chain với mô hình {model_name}")
        return qa_chain
    except Exception as e:
        logger.error(f"Lỗi khi tạo QA chain: {str(e)}")
        raise ValueError(f"Lỗi khi tạo QA chain: {str(e)}")

def process_with_gemini(web_contents: List[Dict[str, str]], question: str, llm: Any, use_web_search: bool = False) -> str:
    """
    Xử lý câu hỏi với Gemini, sử dụng nội dung web nếu bật tìm kiếm.
    """
    try:
        if use_web_search and web_contents:
            context = "\n".join([f"URL: {item['url']}\nNội dung: {item['content']}" for item in web_contents])
            prompt = f"""Bạn là một trợ lý thông minh. Dựa trên thông tin từ web dưới đây, trả lời câu hỏi một cách chính xác và ngắn gọn.
            Nếu không có thông tin phù hợp, trả lời dựa trên kiến thức của bạn.
            Context: {context}
            Câu hỏi: {question}
            Trả lời:"""
        else:
            prompt = f"""Bạn là một trợ lý thông minh. Trả lời câu hỏi dựa trên kiến thức của bạn.
            Câu hỏi: {question}
            Trả lời:"""
        
        response = llm.invoke(prompt)
        return response.content.strip()
    except Exception as e:
        logger.error(f"Lỗi khi xử lý với Gemini: {str(e)}")
        return f"Lỗi khi xử lý câu hỏi: {str(e)}"

def process_with_openai(web_contents: List[Dict[str, str]], question: str, llm: Any, use_web_search: bool = False) -> str:
    """
    Xử lý câu hỏi với OpenAI, sử dụng nội dung web nếu bật tìm kiếm.
    """
    try:
        if use_web_search and web_contents:
            context = "\n".join([f"URL: {item['url']}\nNội dung: {item['content']}" for item in web_contents])
            prompt = f"""Bạn là một trợ lý thông minh. Dựa trên thông tin từ web dưới đây, trả lời câu hỏi một cách chính xác và ngắn gọn.
            Nếu không có thông tin phù hợp, trả lời dựa trên kiến thức của bạn.
            Context: {context}
            Câu hỏi: {question}
            Trả lời:"""
        else:
            prompt = f"""Bạn là một trợ lý thông minh. Trả lời câu hỏi dựa trên kiến thức của bạn.
            Câu hỏi: {question}
            Trả lời:"""
        
        response = llm.invoke(prompt)
        return response.strip()
    except Exception as e:
        logger.error(f"Lỗi khi xử lý với OpenAI: {str(e)}")
        return f"Lỗi khi xử lý câu hỏi: {str(e)}"