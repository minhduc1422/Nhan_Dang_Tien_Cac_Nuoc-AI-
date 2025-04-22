import os
from pathlib import Path
from dotenv import load_dotenv
import logging

# Set up logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Load API key and MySQL info from .env
load_dotenv(override=True)
api_key = os.getenv("GEMINI_API_KEY")
mysql_host = os.getenv("MYSQL_HOST")
logger.info(f"MYSQL_HOST: {mysql_host}")
mysql_user = os.getenv("MYSQL_USER")
mysql_password = os.getenv("MYSQL_PASSWORD", "")
mysql_database = os.getenv("MYSQL_DATABASE", "money")

if not api_key:
    raise ValueError("API Key không hợp lệ. Vui lòng kiểm tra file .env hoặc đặt GEMINI_API_KEY.")
if not all([mysql_host, mysql_user, mysql_database]):
    raise ValueError("Thông tin MySQL không đầy đủ. Vui lòng kiểm tra file .env.")

# Paths (dùng đường dẫn cục bộ thay vì container)
BASE_DIR = Path(__file__).resolve().parent
STATIC_DIR = BASE_DIR / "static"

# Xác định vector_db_path dựa trên môi trường
if os.getenv("DOCKER_ENV"):
    vector_db_path = os.getenv("VECTOR_DB_PATH", "/app/vectorstores/db_faiss")
else:
    vector_db_path = os.getenv("VECTOR_DB_PATH", str(BASE_DIR / "vectorstores" / "db_faiss"))
logger.info(f"vector_db_path: {vector_db_path}")

# Model paths (gộp 22 mô hình từ 'run' và 'runs')
model_paths = [
    {"country": "Việt Nam", "path": BASE_DIR / "run" / "train" / "VietNam" / "weights" / "best.pt", "source": "run"},
    {"country": "Thái Lan", "path": BASE_DIR / "run" / "train" / "ThaiLan" / "weights" / "best.pt", "source": "run"},
    {"country": "Brunei", "path": BASE_DIR / "run" / "train" / "Brunei" / "weights" / "best.pt", "source": "run"},
    {"country": "Campuchia", "path": BASE_DIR / "run" / "train" / "Campuchia" / "weights" / "best.pt", "source": "run"},
    {"country": "Indonesia", "path": BASE_DIR / "run" / "train" / "INDONESIA" / "weights" / "best.pt", "source": "run"},
    {"country": "Lào", "path": BASE_DIR / "run" / "train" / "LAO" / "weights" / "best.pt", "source": "run"},
    {"country": "Myanmar", "path": BASE_DIR / "run" / "train" / "Myanmar" / "weights" / "best.pt", "source": "run"},
    {"country": "Philippines", "path": BASE_DIR / "run" / "train" / "Philippines" / "Philippines2" / "weights" / "best.pt", "source": "run"},
    {"country": "Singapore", "path": BASE_DIR / "run" / "train" / "Singapore" / "weights" / "best.pt", "source": "run"},
    {"country": "Đông Timor", "path": BASE_DIR / "run" / "train" / "USD(Dong_timor)" / "weights" / "best.pt", "source": "run"},
    {"country": "Malaysia", "path": BASE_DIR / "run" / "train" / "Malaysia" / "weights" / "best.pt", "source": "run"},
    {"country": "Việt Nam", "path": BASE_DIR / "runs" / "train" / "VietNam" / "weights" / "best.pt", "source": "runs"},
    {"country": "Thái Lan", "path": BASE_DIR / "runs" / "train" / "ThaiLan" / "weights" / "best.pt", "source": "runs"},
    {"country": "Brunei", "path": BASE_DIR / "runs" / "train" / "Brunei" / "weights" / "best.pt", "source": "runs"},
    {"country": "Campuchia", "path": BASE_DIR / "runs" / "train" / "Campuchia" / "weights" / "best.pt", "source": "runs"},
    {"country": "Indonesia", "path": BASE_DIR / "runs" / "train" / "INDONESIA" / "weights" / "best.pt", "source": "runs"},
    {"country": "Lào", "path": BASE_DIR / "runs" / "train" / "LAO" / "weights" / "best.pt", "source": "runs"},
    {"country": "Myanmar", "path": BASE_DIR / "runs" / "train" / "Myanmar" / "weights" / "best.pt", "source": "runs"},
    {"country": "Philippines", "path": BASE_DIR / "runs" / "train" / "philippines" / "weights" / "best.pt", "source": "runs"},
    {"country": "Singapore", "path": BASE_DIR / "runs" /
      "train" / "Singapore" / "weights" / "best.pt", "source": "runs"},
    {"country": "Đông Timor", "path": BASE_DIR / "runs" / "train" / "USD(Dong_timor)" / "weights" / "best.pt", "source": "runs"},
    {"country": "Malaysia", "path": BASE_DIR / "runs" / "train" / "Malaysia" / "weights" / "best.pt", "source": "runs"},
]

# Kiểm tra sự tồn tại của các file mô hình
for model_info in model_paths:
    country = model_info["country"]
    path = model_info["path"]
    source = model_info["source"]
    if not path.exists():
        logger.warning(f"File không tồn tại: {path}. Bỏ qua mô hình {country} ({source}).")
    else:
        logger.info(f"Found: {path}")





# import os
# from pathlib import Path
# from dotenv import load_dotenv
# import logging

# # Set up logging
# logging.basicConfig(level=logging.INFO)
# logger = logging.getLogger(__name__)

# # Load API key and MySQL info from .env
# load_dotenv(override=True)
# api_key = os.getenv("GEMINI_API_KEY")
# mysql_host = os.getenv("MYSQL_HOST")
# logger.info(f"MYSQL_HOST: {mysql_host}")
# mysql_user = os.getenv("MYSQL_USER")
# mysql_password = os.getenv("MYSQL_PASSWORD", "")
# mysql_database = os.getenv("MYSQL_DATABASE", "money")

# if not api_key:
#     raise ValueError("API Key không hợp lệ. Vui lòng kiểm tra file .env hoặc đặt GEMINI_API_KEY.")
# if not all([mysql_host, mysql_user, mysql_database]):
#     raise ValueError("Thông tin MySQL không đầy đủ. Vui lòng kiểm tra file .env.")

# # Paths (dùng đường dẫn cục bộ thay vì container)
# BASE_DIR = Path(__file__).resolve().parent
# STATIC_DIR = BASE_DIR / "static"

# # Xác định vector_db_path dựa trên môi trường
# if os.getenv("DOCKER_ENV"):
#     vector_db_path = os.getenv("VECTOR_DB_PATH", "/app/vectorstores/db_faiss")
# else:
#     vector_db_path = "D:/thuc_tap_tot_nghiep/web/vectorstores/db_faiss"
# logger.info(f"vector_db_path: {vector_db_path}")

# # Model paths (gộp 22 mô hình từ 'run' và 'runs')
# model_paths = [
#     {"country": "Việt Nam", "path": BASE_DIR / "run" / "train" / "VietNam" / "weights" / "best.pt", "source": "run"},
#     {"country": "Thái Lan", "path": BASE_DIR / "run" / "train" / "ThaiLan" / "weights" / "best.pt", "source": "run"},
#     {"country": "Brunei", "path": BASE_DIR / "run" / "train" / "Brunei" / "weights" / "best.pt", "source": "run"},
#     {"country": "Campuchia", "path": BASE_DIR / "run" / "train" / "Campuchia" / "weights" / "best.pt", "source": "run"},
#     {"country": "Indonesia", "path": BASE_DIR / "run" / "train" / "INDONESIA" / "weights" / "best.pt", "source": "run"},
#     {"country": "Lào", "path": BASE_DIR / "run" / "train" / "LAO" / "weights" / "best.pt", "source": "run"},
#     {"country": "Myanmar", "path": BASE_DIR / "run" / "train" / "Myanmar" / "weights" / "best.pt", "source": "run"},
#     {"country": "Philippines", "path": BASE_DIR / "run" / "train" / "Philippines" / "Philippines2" / "weights" / "best.pt", "source": "run"},
#     {"country": "Singapore", "path": BASE_DIR / "run" / "train" / "Singapore" / "weights" / "best.pt", "source": "run"},
#     {"country": "Đông Timor", "path": BASE_DIR / "run" / "train" / "USD(Dong_timor)" / "weights" / "best.pt", "source": "run"},
#     {"country": "Malaysia", "path": BASE_DIR / "run" / "train" / "Malaysia" / "weights" / "best.pt", "source": "run"},
#     {"country": "Việt Nam", "path": BASE_DIR / "runs" / "train" / "VietNam" / "weights" / "best.pt", "source": "runs"},
#     {"country": "Thái Lan", "path": BASE_DIR / "runs" / "train" / "ThaiLan" / "weights" / "best.pt", "source": "runs"},
#     {"country": "Brunei", "path": BASE_DIR / "runs" / "train" / "Brunei" / "weights" / "best.pt", "source": "runs"},
#     {"country": "Campuchia", "path": BASE_DIR / "runs" / "train" / "Campuchia" / "weights" / "best.pt", "source": "runs"},
#     {"country": "Indonesia", "path": BASE_DIR / "runs" / "train" / "INDONESIA" / "weights" / "best.pt", "source": "runs"},
#     {"country": "Lào", "path": BASE_DIR / "runs" / "train" / "LAO" / "weights" / "best.pt", "source": "runs"},
#     {"country": "Myanmar", "path": BASE_DIR / "runs" / "train" / "Myanmar" / "weights" / "best.pt", "source": "runs"},
#     {"country": "Philippines", "path": BASE_DIR / "runs" / "train" / "Philippines" / "weights" / "best.pt", "source": "runs"},
#     {"country": "Singapore", "path": BASE_DIR / "runs" / "train" / "Singapore" / "weights" / "best.pt", "source": "runs"},
#     {"country": "Đông Timor", "path": BASE_DIR / "runs" / "train" / "USD(Dong_timor)" / "weights" / "best.pt", "source": "runs"},
#     {"country": "Malaysia", "path": BASE_DIR / "runs" / "train" / "Malaysia" / "weights" / "best.pt", "source": "runs"},
# ]

# # Kiểm tra sự tồn tại của các file mô hình
# for model_info in model_paths:
#     country = model_info["country"]
#     path = model_info["path"]
#     source = model_info["source"]
#     if not path.exists():
#         logger.warning(f"File không tồn tại: {path}. Bỏ qua mô hình {country} ({source}).")
#     else:
#         logger.info(f"Found: {path}")

