from fastapi import UploadFile, File, Response, Request, HTTPException, Form
from fastapi.responses import FileResponse, JSONResponse
from config import STATIC_DIR, logger
from yolo_processing import process_image_with_yolo
from chatbot import fetch_top_5_web_content, process_with_gemini
from database import get_db_connection
from auth import login, signup, forgot_password
from slowapi import Limiter
import cv2
import base64
import numpy as np
from mysql.connector import Error
from googlesearch import search
import requests
from bs4 import BeautifulSoup

def register_routes(app, limiter):
    @app.get("/")
    async def root():
        return FileResponse(STATIC_DIR / "index.html")

    @app.post("/detect_money")
    async def detect_money(file: UploadFile = File(...), request: Request = None):
        try:
            token = request.headers.get("Authorization")
            if token:
                connection = get_db_connection()
                if not connection:
                    raise HTTPException(status_code=500, detail="Không thể kết nối đến cơ sở dữ liệu!")
                try:
                    cursor = connection.cursor()
                    token_decoded = base64.b64decode(token.split()[1]).decode().split(":")
                    email = token_decoded[0]
                    cursor.execute("SELECT balance FROM users WHERE email = %s", (email,))
                    balance = cursor.fetchone()
                    if not balance or balance[0] < 1.0:
                        raise HTTPException(status_code=403, detail="Số dư không đủ để sử dụng tính năng này!")
                    cursor.execute("UPDATE users SET balance = balance - 1 WHERE email = %s", (email,))
                    connection.commit()
                except Error as e:
                    raise HTTPException(status_code=500, detail=f"Lỗi khi truy vấn cơ sở dữ liệu: {e}")
                finally:
                    if connection.is_connected():
                        cursor.close()
                        connection.close()

            contents = await file.read()
            nparr = np.frombuffer(contents, np.uint8)
            image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
            if image is None:
                raise HTTPException(status_code=400, detail="Không thể đọc file ảnh!")
            
            annotated_image, detections = process_image_with_yolo(image)
            _, buffer = cv2.imencode('.jpg', annotated_image)
            image_base64 = base64.b64encode(buffer).decode()

            detection_info = {}
            money_details = {}
            
            if detections:
                predicted_class = detections[0]["class"]
                confidence = detections[0]["confidence"]
                logger.info(f"Nhận diện thành công: {predicted_class} với độ tin cậy {confidence}")

                detection_info = {
                    "denomination": predicted_class,
                    "confidence": f"{confidence:.2%}"
                }

                search_query = f"{predicted_class} banknote year of issue description site:*.edu | site:*.org | site:*.gov -inurl:(signup | login)"
                try:
                    search_results = list(search(search_query, num_results=1))
                    if search_results:
                        response = requests.get(search_results[0], timeout=5)
                        soup = BeautifulSoup(response.text, 'html.parser')
                        paragraphs = soup.find_all('p')
                        description = " ".join(p.get_text() for p in paragraphs[:3] if p.get_text().strip())[:500]
                        
                        year = "Không rõ"
                        for p in paragraphs:
                            text = p.get_text().lower()
                            if "year" in text or "issued" in text or "phát hành" in text:
                                words = text.split()
                                for i, word in enumerate(words):
                                    if word.isdigit() and 1900 <= int(word) <= 2025:
                                        year = word
                                        break
                                if year != "Không rõ":
                                    break

                        money_details = {
                            "year_of_issue": year,
                            "description": description or "Không có mô tả chi tiết."
                        }
                    else:
                        money_details = {
                            "year_of_issue": "Không tìm thấy",
                            "description": "Không tìm thấy thông tin từ Google."
                        }
                except Exception as e:
                    logger.error(f"Lỗi khi tìm kiếm hoặc crawl Google: {e}")
                    money_details = {
                        "year_of_issue": "Lỗi",
                        "description": f"Lỗi khi tìm kiếm: {str(e)}"
                    }
            else:
                logger.info("Không nhận diện được tờ tiền sau khi thử lại.")
                detection_info = {
                    "denomination": "Không nhận diện được",
                    "confidence": "N/A"
                }
                money_details = {
                    "year_of_issue": "N/A",
                    "description": "Không có thông tin do không nhận diện được tờ tiền."
                }

            return {
                "image": f"data:image/jpeg;base64,{image_base64}",
                "detection_info": detection_info,
                "money_details": money_details
            }
        except Exception as e:
            logger.error(f"Lỗi khi xử lý nhận diện tiền: {e}")
            raise HTTPException(status_code=500, detail=f"Lỗi khi xử lý: {str(e)}")



    @app.post("/detect_money_webcam")
    async def detect_money_webcam():
        cap = cv2.VideoCapture(0)
        if not cap.isOpened():
            raise HTTPException(status_code=500, detail="Không thể truy cập webcam!")

        try:
            ret, frame = cap.read()
            if not ret:
                raise HTTPException(status_code=500, detail="Không thể nhận frame từ webcam!")

            annotated_image, detections = process_image_with_yolo(frame)
            _, buffer = cv2.imencode('.jpg', annotated_image)
            image_base64 = base64.b64encode(buffer).decode()

            additional_info = ""
            if detections:
                predicted_class = detections[0]["class"]
                logger.info(f"Nhận diện thành công từ webcam: {predicted_class}")
                web_contents = fetch_top_5_web_content(f"{predicted_class} tiền tệ")
                query = f"Thông tin chi tiết về '{predicted_class}' bao gồm màu sắc, năm phát hành, và đặc điểm nổi bật."
                additional_info = process_with_gemini(web_contents, query)
                logger.info(f"Thông tin bổ sung: {additional_info}")
            else:
                logger.info("Không nhận diện được tờ tiền từ webcam sau khi thử lại.")

            cap.release()
            return JSONResponse(content={
                "image": f"data:image/jpeg;base64,{image_base64}",
                "detections": detections,
                "additional_info": additional_info
            })
        except Exception as e:
            cap.release()
            logger.error(f"Lỗi khi xử lý webcam: {e}")
            raise HTTPException(status_code=500, detail=f"Lỗi khi xử lý webcam: {str(e)}")

    @app.post("/chat")
    @limiter.limit("5/minute")
    async def chat(request: Request):
        data = await request.json()
        question = data.get("question")
        if not question:
            raise HTTPException(status_code=400, detail="Vui lòng cung cấp câu hỏi!")
        
        try:
            logger.info(f"Nhận được câu hỏi: {question}")
            web_contents = fetch_top_5_web_content(question)
            logger.info(f"Nội dung từ web: {web_contents}")
            
            asean_countries = {
                "việt nam": ["đồng", "vnd"],
                "thái lan": ["baht", "thb"],
                "campuchia": ["riel", "khr"],
                "lào": ["kip", "lak"],
                "myanmar": ["kyat", "mmk"],
                "malaysia": ["ringgit", "myr"],
                "singapore": ["đô la singapore", "sgd"],
                "philippines": ["peso", "php"],
                "indonesia": ["rupiah", "idr"],
                "brunei": ["đô la brunei", "bnd"],
                "đông timor": ["đô la mỹ", "usd"],
                "mỹ": ["đô la", "usd"]
            }
            
            fallback_data = {
                "brunei": {
                    "1": {"color": "xanh lá cây", "year": "2004"},
                    "5": {"color": "cam", "year": "2004"},
                    "10": {"color": "đỏ", "year": "2004"},
                    "20": {"color": "xanh dương", "year": "2004"},
                    "50": {"color": "xanh dương", "year": "2004"},
                    "100": {"color": "tím", "year": "2004"},
                    "500": {"color": "vàng", "year": "2004"},
                    "1000": {"color": "xám", "year": "2004"}
                },
                "campuchia": {
                    "50": {"color": "tím", "year": "1995"},
                    "100": {"color": "xanh dương", "year": "1995"},
                    "200": {"color": "đỏ", "year": "1995"},
                    "500": {"color": "đỏ", "year": "1995"},
                    "1000": {"color": "nâu", "year": "1995"},
                    "2000": {"color": "xám", "year": "1995"},
                    "5000": {"color": "vàng", "year": "1995"},
                    "10000": {"color": "xanh lá cây", "year": "1995"}
                },
                "đông timor": {
                    "1": {"color": "xanh lục/xám", "year": "2009"},
                    "5": {"color": "tím", "year": "2009"},
                    "10": {"color": "cam", "year": "2009"},
                    "20": {"color": "xanh lá cây", "year": "2009"},
                    "50": {"color": "hồng", "year": "2009"},
                    "100": {"color": "xanh lục", "year": "2013"}
                },
                "indonesia": {
                    "1000": {"color": "xám", "year": "2016"},
                    "2000": {"color": "xanh lá cây", "year": "2016"},
                    "5000": {"color": "nâu", "year": "2016"},
                    "10000": {"color": "tím", "year": "2016"},
                    "20000": {"color": "xanh lá cây", "year": "2016"},
                    "50000": {"color": "xanh dương", "year": "2016"},
                    "100000": {"color": "đỏ", "year": "2016"}
                },
                "lào": {
                    "500": {"color": "xanh dương", "year": "2003"},
                    "1000": {"color": "đỏ", "year": "2003"},
                    "2000": {"color": "nâu", "year": "2003"},
                    "5000": {"color": "tím", "year": "2003"},
                    "10000": {"color": "xanh lá cây", "year": "2003"},
                    "20000": {"color": "vàng", "year": "2003"},
                    "50000": {"color": "cam", "year": "2003"},
                    "100000": {"color": "xanh dương", "year": "2003"}
                },
                "malaysia": {
                    "1": {"color": "xanh dương", "year": "2012"},
                    "5": {"color": "xanh lá cây", "year": "2012"},
                    "10": {"color": "đỏ", "year": "2012"},
                    "20": {"color": "cam", "year": "2012"},
                    "50": {"color": "xanh dương/tím", "year": "2012"},
                    "100": {"color": "tím", "year": "2012"}
                },
                "myanmar": {
                    "50": {"color": "tím", "year": "1990"},
                    "100": {"color": "xanh dương", "year": "1990"},
                    "200": {"color": "nâu", "year": "1990"},
                    "500": {"color": "cam", "year": "1990"},
                    "1000": {"color": "xanh lá cây", "year": "1990"},
                    "5000": {"color": "đỏ", "year": "1990"},
                    "10000": {"color": "vàng", "year": "1990"}
                },
                "philippines": {
                    "20": {"color": "cam", "year": "2010"},
                    "50": {"color": "đỏ", "year": "2010"},
                    "100": {"color": "tím", "year": "2010"},
                    "200": {"color": "xanh lá cây", "year": "2010"},
                    "500": {"color": "vàng", "year": "2010"},
                    "1000": {"color": "xanh dương", "year": "2010"}
                },
                "singapore": {
                    "2": {"color": "tím", "year": "1999"},
                    "5": {"color": "xanh lá cây", "year": "1999"},
                    "10": {"color": "đỏ", "year": "1999"},
                    "50": {"color": "xanh dương", "year": "1999"},
                    "100": {"color": "cam", "year": "1999"},
                    "1000": {"color": "tím", "year": "1999"}
                },
                "thái lan": {
                    "20": {"color": "xanh lá cây", "year": "2018"},
                    "50": {"color": "xanh dương", "year": "2018"},
                    "100": {"color": "đỏ", "year": "2018"},
                    "500": {"color": "tím", "year": "2018"},
                    "1000": {"color": "xám", "year": "2018"}
                },
                "việt nam": {
                    "100": {"color": "xanh lá cây", "year": "2003"},
                    "200": {"color": "nâu", "year": "2003"},
                    "500": {"color": "xanh dương", "year": "2003"},
                    "1000": {"color": "xanh lá cây", "year": "2003"},
                    "2000": {"color": "xám", "year": "2003"},
                    "5000": {"color": "xanh lá cây", "year": "2003"},
                    "10000": {"color": "xanh dương", "year": "2003"},
                    "20000": {"color": "xanh dương nhạt", "year": "2003"},
                    "50000": {"color": "nâu tím đỏ", "year": "2003"},
                    "100000": {"color": "xanh lá cây đậm", "year": "2003"},
                    "200000": {"color": "đỏ nâu", "year": "2003"},
                    "500000": {"color": "xanh dương", "year": "2003"}
                }
            }
            
            question_lower = question.lower()
            country = None
            denomination = None
            for c in asean_countries.keys():
                if c in question_lower:
                    country = c
                    break
            if not country and "đô la" in question_lower:
                country = "mỹ" if "mỹ" in question_lower else "đông timor"
            
            for word in question_lower.split():
                if word.replace(".", "").isdigit():
                    denomination = word.replace(".", "")
                    break
            
            if web_contents and "Lỗi khi tìm kiếm web" not in web_contents[0]["content"]:
                if "màu" in question_lower or "màu sắc" in question_lower:
                    query = f"Màu sắc của tờ {denomination or ''} {country.capitalize() if country else 'đô la'}"
                elif "có mấy" in question_lower or "bao nhiêu" in question_lower:
                    query = f"Tiền {country.capitalize()} có bao nhiêu mệnh giá và là những mệnh giá nào?"
                elif "năm" in question_lower or "phát hành" in question_lower:
                    query = f"Năm phát hành của tờ {denomination or ''} {country.capitalize() if country else 'đô la'}"
                else:
                    query = question
                
                try:
                    result = process_with_gemini(web_contents, query)
                    if "429" in result or "quota" in result:
                        raise Exception("Quota exceeded")
                except Exception as e:
                    logger.warning(f"API Gemini không hoạt động, chuyển sang dữ liệu mặc định: {e}")
                    if "tiền là gì" in question_lower:
                        result = "Tiền là phương tiện trao đổi được chấp nhận rộng rãi để mua bán hàng hóa, dịch vụ, hoặc thanh toán nợ."
                    elif country and denomination and country in fallback_data and denomination in fallback_data[country]:
                        if "màu" in question_lower or "màu sắc" in question_lower:
                            result = f"Tờ {denomination} {country.capitalize()} có màu {fallback_data[country][denomination]['color']}."
                        elif "năm" in question_lower or "phát hành" in question_lower:
                            result = f"Tờ {denomination} {country.capitalize()} được phát hành năm {fallback_data[country][denomination]['year']}."
                        else:
                            result = f"Thông tin về tờ {denomination} {country.capitalize()}: màu {fallback_data[country][denomination]['color']}, năm phát hành {fallback_data[country][denomination]['year']}."
                    else:
                        result = "Không có thông tin mặc định cho câu hỏi này. Vui lòng thử lại sau khi API hoạt động."
            else:
                logger.warning("Không tìm thấy thông tin từ web, chuyển sang dữ liệu mặc định")
                if "tiền là gì" in question_lower:
                    result = "Tiền là phương tiện trao đổi được chấp nhận rộng rãi để mua bán hàng hóa, dịch vụ, hoặc thanh toán nợ."
                elif country and denomination and country in fallback_data and denomination in fallback_data[country]:
                    if "màu" in question_lower or "màu sắc" in question_lower:
                        result = f"Tờ {denomination} {country.capitalize()} có màu {fallback_data[country][denomination]['color']}."
                    elif "năm" in question_lower or "phát hành" in question_lower:
                        result = f"Tờ {denomination} {country.capitalize()} được phát hành năm {fallback_data[country][denomination]['year']}."
                    else:
                        result = f"Thông tin về tờ {denomination} {country.capitalize()}: màu {fallback_data[country][denomination]['color']}, năm phát hành {fallback_data[country][denomination]['year']}."
                else:
                    result = "Không có thông tin mặc định cho câu hỏi này. Vui lòng thử lại sau khi API hoạt động."
            
            logger.info(f"Kết quả trả lời: {result}")
            return {"response": result}
        except Exception as e:
            logger.error(f"Chat error: {str(e)}")
            if "429" in str(e) or "quota" in str(e):
                return {"response": "Đã vượt quá giới hạn quota của Gemini API. Vui lòng thử lại sau hoặc kiểm tra tài khoản của bạn."}
            return {"response": f"Lỗi khi xử lý câu hỏi: {str(e)}"}

    @app.post("/login")
    async def login_route(name: str = Form(...), password: str = Form(...)):
        return await login(name, password)

    @app.post("/signup")
    async def signup_route(name: str = Form(...), email: str = Form(...), password: str = Form(...)):
        return await signup(name, email, password)

    @app.post("/forgot_password")
    async def forgot_password_route(email: str = Form(...)):
        return await forgot_password(email)

    @app.post("/deposit")
    async def deposit(amount: float = Form(...), request: Request = None):
        token = request.headers.get("Authorization")
        if not token:
            raise HTTPException(status_code=401, detail="Yêu cầu đăng nhập để nạp tiền!")
        
        connection = get_db_connection()
        if not connection:
            raise HTTPException(status_code=500, detail="Không thể kết nối đến cơ sở dữ liệu!")
        
        try:
            cursor = connection.cursor()
            token_decoded = base64.b64decode(token.split()[1]).decode().split(":")
            email = token_decoded[0]
            
            cursor.execute("SELECT id FROM users WHERE email = %s", (email,))
            user = cursor.fetchone()
            if not user:
                raise HTTPException(status_code=404, detail="Người dùng không tồn tại!")
            user_id = user[0]
            
            query_insert = "INSERT INTO deposits (user_id, amount, status) VALUES (%s, %s, %s)"
            cursor.execute(query_insert, (user_id, amount, "pending"))
            
            cursor.execute("UPDATE users SET balance = balance + %s WHERE id = %s", (amount, user_id))
            cursor.execute("UPDATE deposits SET status = 'completed' WHERE user_id = %s AND amount = %s AND status = 'pending'", (user_id, amount))
            
            connection.commit()
            logger.info(f"Nạp tiền thành công: {amount} cho người dùng {email}")
            return {"message": f"Nạp {amount} thành công! Số dư đã được cập nhật."}
        except Error as e:
            raise HTTPException(status_code=500, detail=f"Lỗi khi xử lý nạp tiền: {e}")
        finally:
            if connection.is_connected():
                cursor.close()
                connection.close()