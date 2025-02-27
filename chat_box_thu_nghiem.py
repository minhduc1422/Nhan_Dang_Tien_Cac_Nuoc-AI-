import os
from langchain_google_genai import ChatGoogleGenerativeAI
from langchain.chains import LLMChain
from langchain.prompts import PromptTemplate
from dotenv import load_dotenv

# Load API key từ file .env
load_dotenv(override=True)
api_key = os.getenv("GEMINI_API_KEY")

if not api_key:
    raise ValueError("API Key không hợp lệ. Vui lòng kiểm tra file .env hoặc đặt GOOGLE_API_KEY.")

# Hàm load LLM sử dụng Gemini API
def load_llm(api_key):
    try:
        llm = ChatGoogleGenerativeAI(
            model="gemini-pro",  # Chọn mô hình Gemini
            google_api_key=api_key,
            temperature=0.01,
            max_output_tokens=1024
        )
        return llm
    except Exception as e:
        raise RuntimeError(f"Lỗi khi khởi tạo mô hình: {e}")

# Hàm tạo Prompt Template
def create_prompt(template):
    return PromptTemplate(template=template, input_variables=["question"])

# Hàm tạo LLM Chain
def create_simple_chain(prompt, llm):
    return LLMChain(prompt=prompt, llm=llm)

# Prompt Template
template = """<|im_start|>system
Bạn là một trợ lí AI hữu ích. Hãy trả lời người dùng một cách chính xác.
<|im_end|>
<|im_start|>user
{question}<|im_end|>
<|im_start|>assistant"""

# Tạo prompt, model và chain
try:
    prompt = create_prompt(template)
    llm = load_llm(api_key)
    llm_chain = create_simple_chain(prompt, llm)
    
    # Câu hỏi
    question = "1 cộng 1 bang mấy"
    response = llm_chain.invoke({"question": question})
    
    # In kết quả
    print(response)
except Exception as e:
    print(f"Đã xảy ra lỗi: {e}")