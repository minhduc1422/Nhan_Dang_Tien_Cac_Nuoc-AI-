from fastapi import Form, HTTPException
from passlib.context import CryptContext
from database import get_db_connection
from config import logger
import base64
from mysql.connector import Error  # Thêm import này

# Password hashing
pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

async def login(name: str = Form(...), password: str = Form(...)):
    connection = get_db_connection()
    if not connection:
        raise HTTPException(status_code=500, detail="Không thể kết nối đến cơ sở dữ liệu!")
    try:
        cursor = connection.cursor()
        query = "SELECT name, email, password FROM users WHERE name = %s"
        cursor.execute(query, (name,))
        user = cursor.fetchone()
        if user and pwd_context.verify(password, user[2]):
            token = base64.b64encode(f"{user[1]}:{user[0]}".encode()).decode()
            return {
                "message": "Đăng nhập thành công!",
                "user": {"name": user[0], "email": user[1]},
                "token": token
            }
        else:
            raise HTTPException(status_code=401, detail="Tên tài khoản hoặc mật khẩu không đúng!")
    except Error as e:
        raise HTTPException(status_code=500, detail=f"Lỗi khi truy vấn cơ sở dữ liệu: {e}")
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()

async def signup(name: str = Form(...), email: str = Form(...), password: str = Form(...)):
    connection = get_db_connection()
    if not connection:
        raise HTTPException(status_code=500, detail="Không thể kết nối đến cơ sở dữ liệu!")
    try:
        cursor = connection.cursor()
        query_check = "SELECT email FROM users WHERE email = %s"
        cursor.execute(query_check, (email,))
        if cursor.fetchone():
            raise HTTPException(status_code=400, detail="Email đã được đăng ký!")
        hashed_password = pwd_context.hash(password)
        query_insert = "INSERT INTO users (name, email, password, balance) VALUES (%s, %s, %s, %s)"
        cursor.execute(query_insert, (name, email, hashed_password, 0.0))
        connection.commit()
        logger.info(f"Đăng ký thành công cho {email}")
        return {"message": "Đăng ký thành công!"}
    except Error as e:
        raise HTTPException(status_code=500, detail=f"Lỗi khi truy vấn cơ sở dữ liệu: {e}")
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()

async def forgot_password(email: str = Form(...)):
    connection = get_db_connection()
    if not connection:
        raise HTTPException(status_code=500, detail="Không thể kết nối đến cơ sở dữ liệu!")
    try:
        cursor = connection.cursor()
        query = "SELECT email FROM users WHERE email = %s"
        cursor.execute(query, (email,))
        user = cursor.fetchone()
        if not user:
            raise HTTPException(status_code=404, detail="Email không tồn tại trong hệ thống!")
        return {"message": "Yêu cầu đặt lại mật khẩu đã được gửi! Vui lòng kiểm tra email của bạn."}
    except Error as e:
        raise HTTPException(status_code=500, detail=f"Lỗi khi truy vấn cơ sở dữ liệu: {e}")
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()