import os
from dotenv import load_dotenv
from llama_index.core import settings


# Load các biến môi trường từ file .env
load_dotenv()


class Settings:
    """
    Lớp cấu hình chung cho ứng dụng, quản lý các biến môi trường.

    Attributes:
        DIR_ROOT (str): Đường dẫn thư mục gốc của dự án.
    """

    # Thiết lập đường dẫn thư mục gốc của dự án
    DIR_ROOT = os.path.dirname(os.path.abspath(".env"))
    
    KEY_API_GPT = os.environ["KEY_API_GPT"]
    
    NUM_DOC = os.environ["NUM_DOC"]
    
    LLM_NAME = os.environ["NUM_DOC"]
    
    OPENAI_LLM = os.environ["OPENAI_LLM"]
    KEY_API_GERMINI = os.environ["KEY_API_GERMINI"]
    CHATBOT_LLM=os.environ["CHATBOT_LLM"]
    CHATBOT_LLM = "loco"  
    
# Tạo một thể hiện của lớp Settings để sử dụng trong ứng dụng
settings = Settings()
