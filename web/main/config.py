import os
os.environ["KMP_DUPLICATE_LIB_OK"] = "TRUE"

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
mysql_user = os.getenv("MYSQL_USER")
mysql_password = os.getenv("MYSQL_PASSWORD", "")
mysql_database = os.getenv("MYSQL_DATABASE", "money")

if not api_key:
    raise ValueError("API Key không hợp lệ. Vui lòng kiểm tra file .env hoặc đặt GEMINI_API_KEY.")
if not all([mysql_host, mysql_user, mysql_database]):
    raise ValueError("Thông tin MySQL không đầy đủ. Vui lòng kiểm tra file .env.")

# Paths
BASE_DIR = Path(__file__).resolve().parent.parent  # Điều chỉnh để phù hợp với cấu trúc thư mục
STATIC_DIR = BASE_DIR / "static"
vector_db_path = os.getenv("VECTOR_DB_PATH", "D:/thuc_tap_tot_nghiep/vectorstores/db_faiss")
model1_path = os.getenv("MODEL1_PATH", "D:/thuc_tap_tot_nghiep/run/train/ThaiLan/weights/best.pt")
model2_path = os.getenv("MODEL2_PATH", "D:/thuc_tap_tot_nghiep/runs/runs/train/train_money/weights/best.pt")

# Check file existence
for path in [model1_path, model2_path]:
    if not os.path.exists(path):
        raise FileNotFoundError(f"File không tồn tại: {path}")