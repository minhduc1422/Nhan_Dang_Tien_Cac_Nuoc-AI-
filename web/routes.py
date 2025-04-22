# from fastapi import UploadFile, File, Response, Request, HTTPException, Form
# from fastapi.responses import FileResponse, JSONResponse
# from config import STATIC_DIR, logger, api_key, BASE_DIR
# from yolo_processing import process_image_with_yolo
# from database import get_db_connection
# from auth import login, signup, forgot_password
# from slowapi import Limiter
# import cv2
# import base64
# import numpy as np
# from mysql.connector import Error
# from googlesearch import search
# import requests
# from bs4 import BeautifulSoup
# import time
# from langchain_community.embeddings import GPT4AllEmbeddings
# from langchain_community.vectorstores import FAISS
# from langchain_google_genai import ChatGoogleGenerativeAI
# from langchain_core.documents import Document
# from langchain.chains import RetrievalQA
# from langchain.prompts import PromptTemplate
# from config import vector_db_path

# MONEY_DATA = {
#     "Brunei": {
#         "1": {"year_of_issue": "2004", "description": "Tờ 1 Đô la Brunei có màu xanh lá cây, được phát hành năm 2004, với chân dung Quốc vương Sultan Hassanal Bolkiah và Quốc huy Brunei ở mặt trước, mặt sau mô phỏng kiến trúc truyền thống Brunei cùng các biểu tượng văn hóa và hoa văn Hồi giáo.", "color": "xanh lá cây"},
#         "5": {"year_of_issue": "2004", "description": "Tờ 5 Đô la Brunei có màu cam, được phát hành năm 2004, với chân dung Quốc vương Sultan Hassanal Bolkiah và Quốc huy Brunei ở mặt trước, mặt sau là hình ảnh Cảng Muara, biểu tượng sự phát triển thương mại của quốc gia.", "color": "cam"},
#         "10": {"year_of_issue": "2004", "description": "Tờ 10 Đô la Brunei có màu đỏ, được phát hành năm 2004, với hình ảnh Quốc vương Sultan Hassanal Bolkiah và Quốc huy Brunei ở mặt trước, mặt sau là Trường Đại học Brunei Darussalam, thể hiện sự đầu tư vào giáo dục.", "color": "đỏ"},
#         "20": {"year_of_issue": "2004", "description": "Tờ 20 Đô la Brunei có màu xanh dương, được phát hành năm 2004, với chân dung Quốc vương Sultan Hassanal Bolkiah và Quốc huy Brunei ở mặt trước, mặt sau là Thánh đường Omar Ali Saifuddien và con thuyền Hoàng gia Brunei.", "color": "xanh dương"},
#         "50": {"year_of_issue": "2004", "description": "Tờ 50 Đô la Brunei có màu xanh dương, được phát hành năm 2004, với hình ảnh Quốc vương Sultan Hassanal Bolkiah và Quốc huy Brunei ở mặt trước, mặt sau là Sông Brunei và cảnh quan thiên nhiên.", "color": "xanh dương"},
#         "100": {"year_of_issue": "2004", "description": "Tờ 100 Đô la Brunei có màu tím, được phát hành năm 2004, với hình ảnh Quốc vương Sultan Hassanal Bolkiah và Quốc huy Brunei ở mặt trước, mặt sau là Tòa nhà Quốc hội Brunei.", "color": "tím"},
#         "10000": {"year_of_issue": "2006", "description": "Tờ 10000 Đô la Brunei có màu xanh lá câycây, được phát hành năm 2006, với chân dung Quốc vương Sultan Hassanal Bolkiah ở mặt trước, mặt sau là Cung điện Istana Nurul Iman, dinh thự lớn nhất thế giới.", "color": "xanh lá cây"}
#     },
#     "Campuchia": {
#         "50": {"year_of_issue": "2002", "description": "Tờ 50 Riel Campuchia có màu tím, được phát hành năm 2002, với hình ảnh Angkor Wat và hoa văn truyền thống Khmer ở mặt trước, mặt sau là các biểu tượng văn hóa và nghệ thuật Khmer.", "color": "tím"},
#         "100": {"year_of_issue": "2002", "description": "Tờ 100 Riel Campuchia có màu xanh dương, được phát hành năm 2002, với hình ảnh Quốc vương Norodom Sihanouk ở mặt trước, mặt sau là cảnh quan thiên nhiên Campuchia.", "color": "xanh dương"},
#         "500": {"year_of_issue": "2001", "description": "Tờ 500 Riel Campuchia có màu đỏ, được phát hành năm 2001, với hình ảnh Đền Preah Vihear ở mặt trước, mặt sau là hình ảnh nền văn hóa Khmer truyền thống.", "color": "đỏ"},
#         "1000": {"year_of_issue": "2013", "description": "Tờ 1000 Riel Campuchia có màu nâu, được phát hành năm 2013, với hình ảnh Quốc vương Norodom Sihanouk ở mặt trước, mặt sau là Cung điện Hoàng gia Campuchia.", "color": "nâu"},
#         "2000": {"year_of_issue": "2008", "description": "Tờ 2000 Riel Campuchia có màu xám, được phát hành năm 2008, với hình ảnh Hoàng hậu Norodom Monineath ở mặt trước, mặt sau là các ngôi đền và di tích lịch sử Khmer.", "color": "xám"},
#         "5000": {"year_of_issue": "2015", "description": "Tờ 5000 Riel Campuchia có màu vàng, được phát hành năm 2015, với hình ảnh Quốc vương Norodom Sihamoni ở mặt trước, mặt sau là sông Mekong.", "color": "vàng"},
#         "10000": {"year_of_issue": "2015", "description": "Tờ 10000 Riel Campuchia có màu xanh lá cây, được phát hành năm 2015, với hình ảnh Quốc vương Norodom Sihanouk ở mặt trước, mặt sau là công trình kiến trúc và cảnh quan thiên nhiên Campuchia.", "color": "xanh lá cây"},
#         "20000": {"year_of_issue": "2017", "description": "Tờ 20000 Riel Campuchia có màu không rõ, được phát hành năm 2017, với hình ảnh Quốc vương Norodom Sihanouk ở mặt trước, mặt sau là Cầu Kampong Kdei.", "color": "không rõ"}
#     },
#     "Indonesia": {
#         "1000": {"year_of_issue": "2016", "description": "Tờ 1000 Rupiah có màu xám, phát hành năm 2016, với hình anh hùng quốc gia Kapitan Pattimura ở mặt trước, mặt sau là cảnh đảo Banda Neira.", "color": "xám"},
#         "2000": {"year_of_issue": "2016", "description": "Tờ 2000 Rupiah có màu xanh lá cây, phát hành năm 2016, với hình anh hùng quốc gia Mohammad Husni Thamrin ở mặt trước, mặt sau là cảnh đảo Ngarai Sianok.", "color": "xanh lá cây"},
#         "5000": {"year_of_issue": "2016", "description": "Tờ 5000 Rupiah có màu nâu, phát hành năm 2016, với hình anh hùng quốc gia Idham Chalid ở mặt trước, mặt sau là cảnh núi Gunung Tambora.", "color": "nâu"},
#         "10000": {"year_of_issue": "2016", "description": "Tờ 10000 Rupiah có màu tím, phát hành năm 2016, với hình anh hùng quốc gia Frans Kaisiepo ở mặt trước, mặt sau là cảnh đảo Wakatobi.", "color": "tím"},
#         "20000": {"year_of_issue": "2016", "description": "Tờ 20000 Rupiah có màu xanh lá cây, phát hành năm 2016, với hình anh hùng quốc gia Sam Ratulangi ở mặt trước, mặt sau là cảnh đảo Derawan.", "color": "xanh lá cây"},
#         "50000": {"year_of_issue": "2016", "description": "Tờ 50000 Rupiah có màu xanh dương, phát hành năm 2016, với hình anh hùng quốc gia Djuanda Kartawidjaja ở mặt trước, mặt sau là cảnh đảo Komodo.", "color": "xanh dương"},
#         "100000": {"year_of_issue": "2016", "description": "Tờ 100000 Rupiah có màu đỏ, phát hành năm 2016, với hình Sukarno và Hatta ở mặt trước, mặt sau là Tòa nhà Quốc hội Indonesia.", "color": "đỏ"}
#     },
#     "Lào": {
#         "100": {"year_of_issue": "2003", "description": "Tờ 100 Kip có màu xanh lá cây, phát hành năm 2003, với hình Chủ tịch Kaysone Phomvihane ở mặt trước, mặt sau là cảnh nông thôn Lào.", "color": "xanh lá cây"},
#         "500": {"year_of_issue": "2003", "description": "Tờ 500 Kip có màu xanh dương, phát hành năm 2003, với hình Chủ tịch Kaysone Phomvihane ở mặt trước, mặt sau là cảnh đập thủy điện.", "color": "xanh dương"},
#         "1000": {"year_of_issue": "2003", "description": "Tờ 1000 Kip có màu đỏ, phát hành năm 2003, với hình Chủ tịch Kaysone Phomvihane ở mặt trước, mặt sau là cảnh chùa Pha That Luang.", "color": "đỏ"},
#         "2000": {"year_of_issue": "2003", "description": "Tờ 2000 Kip có màu nâu, phát hành năm 2003, với hình Chủ tịch Kaysone Phomvihane ở mặt trước, mặt sau là cảnh cầu Friendship.", "color": "nâu"},
#         "5000": {"year_of_issue": "2003", "description": "Tờ 5000 Kip có màu tím, phát hành năm 2003, với hình Chủ tịch Kaysone Phomvihane ở mặt trước, mặt sau là cảnh chùa Wat Xieng Thong.", "color": "tím"},
#         "10000": {"year_of_issue": "2003", "description": "Tờ 10000 Kip có màu xanh lá cây, phát hành năm 2003, với hình Chủ tịch Kaysone Phomvihane ở mặt trước, mặt sau là cảnh thiên nhiên Lào.", "color": "xanh lá cây"},
#         "20000": {"year_of_issue": "2003", "description": "Tờ 20000 Kip có màu vàng, phát hành năm 2003, với hình Chủ tịch Kaysone Phomvihane ở mặt trước, mặt sau là cảnh thác nước Kuang Si.", "color": "vàng"}
#     },
#     "Malaysia": {
#         "1": {"year_of_issue": "2012", "description": "Tờ 1 Ringgit Malaysia có màu xanh dương, được phát hành năm 2012, với chân dung Yang di-Pertuan Agong Tuanku Abdul Rahman ở mặt trước, mặt sau là hình ảnh Wau Bulan, một loại diều truyền thống.", "color": "xanh dương"},
#         "5": {"year_of_issue": "2012", "description": "Tờ 5 Ringgit Malaysia có màu xanh lá cây, được phát hành năm 2012, với chân dung Yang di-Pertuan Agong Tuanku Abdul Rahman ở mặt trước, mặt sau là rừng mưa nhiệt đới Malaysia.", "color": "xanh lá cây"},
#         "10": {"year_of_issue": "2012", "description": "Tờ 10 Ringgit Malaysia có màu đỏ, được phát hành năm 2012, với chân dung Yang di-Pertuan Agong Tuanku Abdul Rahman ở mặt trước, mặt sau là hình ảnh Rafflesia, loài hoa khổng lồ biểu tượng.", "color": "đỏ"},
#         "20": {"year_of_issue": "2012", "description": "Tờ 20 Ringgit Malaysia có màu cam, được phát hành năm 2012, với chân dung Yang di-Pertuan Agong Tuanku Abdul Rahman ở mặt trước, mặt sau là hình ảnh rùa biển Hawksbill và rùa xanh, đại diện cho hệ sinh thái biển.", "color": "cam"},
#         "50": {"year_of_issue": "2012", "description": "Tờ 50 Ringgit Malaysia có màu xanh dương/tím, được phát hành năm 2012, với chân dung Yang di-Pertuan Agong Tuanku Abdul Rahman ở mặt trước, mặt sau là cây cọ dầu, biểu tượng của nền kinh tế Malaysia.", "color": "xanh dương/tím"},
#         "100": {"year_of_issue": "2012", "description": "Tờ 100 Ringgit Malaysia có màu tím, được phát hành năm 2012, với chân dung Yang di-Pertuan Agong Tuanku Abdul Rahman ở mặt trước, mặt sau là Núi Kinabalu, đỉnh núi cao nhất Đông Nam Á.", "color": "tím"}
#     },
#     "Myanmar": {
#         "50": {"year_of_issue": "1997", "description": "Tờ 50 Kyat có màu tím, phát hành năm 1997, với hình tượng Chinthe (sư tử thần thoại) ở mặt trước, mặt sau là cảnh nghệ nhân chạm khắc.", "color": "tím"},
#         "100": {"year_of_issue": "1997", "description": "Tờ 100 Kyat có màu xanh dương, phát hành năm 1997, với hình tượng Chinthe ở mặt trước, mặt sau là cảnh chùa Shwedagon.", "color": "xanh dương"},
#         "200": {"year_of_issue": "1997", "description": "Tờ 200 Kyat có màu nâu, phát hành năm 1997, với hình tượng Chinthe ở mặt trước, mặt sau là cảnh voi làm việc trong rừng.", "color": "nâu"},
#         "500": {"year_of_issue": "1997", "description": "Tờ 500 Kyat có màu cam, phát hành năm 1997, với hình tượng Chinthe ở mặt trước, mặt sau là cảnh thu hoạch lúa.", "color": "cam"},
#         "1000": {"year_of_issue": "1997", "description": "Tờ 1000 Kyat có màu xanh lá cây, phát hành năm 1997, với hình tượng Chinthe ở mặt trước, mặt sau là cảnh cầu U Bein.", "color": "xanh lá cây"},
#         "5000": {"year_of_issue": "2009", "description": "Tờ 5000 Kyat có màu đỏ, phát hành năm 2009, với hình voi trắng ở mặt trước, mặt sau là cảnh chùa Ananda.", "color": "đỏ"},
#         "10000": {"year_of_issue": "2009", "description": "Tờ 10000 Kyat có màu vàng, phát hành năm 2009, với hình voi trắng ở mặt trước, mặt sau là cảnh chùa Mandalay.", "color": "vàng"}
#     },
#     "Philippines": {
#         "1": {"year_of_issue": "1969", "description": "Tờ 1 Peso có màu xanh lục, phát hành năm 1969, với hình anh hùng Jose Rizal ở mặt trước, mặt sau là cảnh nhà thờ Barasoain.", "color": "xanh lục"},
#         "5": {"year_of_issue": "2010", "description": "Tờ 5 Peso có màu tím, phát hành năm 2010, với hình Emilio Aguinaldo ở mặt trước, mặt sau là cảnh chiến thắng tại Malolos.", "color": "tím"},
#         "20": {"year_of_issue": "2010", "description": "Tờ 20 Peso có màu cam, phát hành năm 2010, với hình Manuel Quezon ở mặt trước, mặt sau là cảnh Banaue Rice Terraces.", "color": "cam"},
#         "50": {"year_of_issue": "2010", "description": "Tờ 50 Peso có màu đỏ, phát hành năm 2010, với hình Sergio Osmeña ở mặt trước, mặt sau là cảnh hang động Taal.", "color": "đỏ"},
#         "100": {"year_of_issue": "2010", "description": "Tờ 100 Peso có màu tím, phát hành năm 2010, với hình Manuel Roxas ở mặt trước, mặt sau là cảnh núi Mayon.", "color": "tím"},
#         "200": {"year_of_issue": "2010", "description": "Tờ 200 Peso có màu xanh lá cây, phát hành năm 2010, với hình Diosdado Macapagal ở mặt trước, mặt sau là cảnh Chocolate Hills.", "color": "xanh lá cây"},
#         "500": {"year_of_issue": "2010", "description": "Tờ 500 Peso có màu vàng, phát hành năm 2010, với hình Benigno Aquino Jr. ở mặt trước, mặt sau là cảnh núi Cordillera.", "color": "vàng"},
#         "1000": {"year_of_issue": "2010", "description": "Tờ 1000 Peso có màu xanh dương, phát hành năm 2010, với hình Josefa Llanes Escoda ở mặt trước, mặt sau là cảnh đảo Tubbataha.", "color": "xanh dương"}
#     },
#     "Singapore": {
#         "2": {"year_of_issue": "1999", "description": "Tờ 2 Đô la Singapore có màu tím, phát hành năm 1999, với hình Tổng thống Yusof Ishak ở mặt trước, mặt sau là cảnh giáo dục và học sinh.", "color": "tím"},
#         "5": {"year_of_issue": "1999", "description": "Tờ 5 Đô la Singapore có màu xanh lá cây, phát hành năm 1999, với hình Tổng thống Yusof Ishak ở mặt trước, mặt sau là cảnh vườn hoa.", "color": "xanh lá cây"},
#         "10": {"year_of_issue": "1999", "description": "Tờ 10 Đô la Singapore có màu đỏ, phát hành năm 1999, với hình Tổng thống Yusof Ishak ở mặt trước, mặt sau là cảnh thể thao.", "color": "đỏ"},
#         "50": {"year_of_issue": "1999", "description": "Tờ 50 Đô la Singapore có màu xanh dương, phát hành năm 1999, với hình Tổng thống Yusof Ishak ở mặt trước, mặt sau là cảnh nghệ thuật.", "color": "xanh dương"},
#         "100": {"year_of_issue": "1999", "description": "Tờ 100 Đô la Singapore có màu cam, phát hành năm 1999, với hình Tổng thống Yusof Ishak ở mặt trước, mặt sau là cảnh hàng hải.", "color": "cam"}
#     },
#     "Thái Lan": {
#         "1": {"year_of_issue": "1946", "description": "Tờ 1 Baht có màu xanh lục, phát hành năm 1946, với hình Vua Rama IX ở mặt trước, mặt sau là cảnh chùa Wat Phra Kaew.", "color": "xanh lục"},
#         "2": {"year_of_issue": "1948", "description": "Tờ 2 Baht có màu nâu, phát hành năm 1948, với hình Vua Rama IX ở mặt trước, mặt sau là cảnh nông thôn Thái Lan.", "color": "nâu"},
#         "5": {"year_of_issue": "1955", "description": "Tờ 5 Baht có màu tím, phát hành năm 1955, với hình Vua Rama IX ở mặt trước, mặt sau là cảnh cầu Rama VIII.", "color": "tím"},
#         "10": {"year_of_issue": "2018", "description": "Tờ 10 Baht có màu nâu, phát hành năm 2018, với hình Vua Rama X ở mặt trước, mặt sau là cảnh chùa Wat Arun.", "color": "nâu"},
#         "20": {"year_of_issue": "2018", "description": "Tờ 20 Baht có màu xanh lá cây, phát hành năm 2018, với hình Vua Rama X ở mặt trước, mặt sau là cảnh cầu Bhumibol.", "color": "xanh lá cây"},
#         "25": {"year_of_issue": "1996", "description": "Tờ 25 Baht có màu tím, phát hành năm 1996 (tiền kỷ niệm), với hình Vua Rama IX ở mặt trước, mặt sau là cảnh Hoàng cung Thái Lan.", "color": "tím"},
#         "50": {"year_of_issue": "2018", "description": "Tờ 50 Baht có màu xanh dương, phát hành năm 2018, với hình Vua Rama X ở mặt trước, mặt sau là cảnh chùa Wat Pho.", "color": "xanh dương"},
#         "100": {"year_of_issue": "2018", "description": "Tờ 100 Baht có màu đỏ, phát hành năm 2018, với hình Vua Rama X ở mặt trước, mặt sau là cảnh cung điện Dusit.", "color": "đỏ"},
#         "500": {"year_of_issue": "2018", "description": "Tờ 500 Baht có màu tím, phát hành năm 2018, với hình Vua Rama X ở mặt trước, mặt sau là cảnh chùa Wat Benchamabophit.", "color": "tím"},
#         "1000": {"year_of_issue": "2018", "description": "Tờ 1000 Baht có màu xám, phát hành năm 2018, với hình Vua Rama X ở mặt trước, mặt sau là cảnh Hoàng cung Thái Lan.", "color": "xám"}
#     },
#     "Đông Timor": {
#         "1": {"year_of_issue": "2002", "description": "Tờ 1 Đô la Mỹ tại Đông Timor có màu xanh lục/xám, phát hành năm 2002, với hình George Washington ở mặt trước, mặt sau là Đại bàng Mỹ.", "color": "xanh lục/xám"},
#         "5": {"year_of_issue": "2002", "description": "Tờ 5 Đô la Mỹ tại Đông Timor có màu tím, phát hành năm 2002, với hình Abraham Lincoln ở mặt trước, mặt sau là Đài tưởng niệm Lincoln.", "color": "tím"},
#         "10": {"year_of_issue": "2002", "description": "Tờ 10 Đô la Mỹ tại Đông Timor có màu cam, phát hành năm 2002, với hình Alexander Hamilton ở mặt trước, mặt sau là Bộ Tài chính Mỹ.", "color": "cam"},
#         "20": {"year_of_issue": "2002", "description": "Tờ 20 Đô la Mỹ tại Đông Timor có màu xanh lá cây, phát hành năm 2002, với hình Andrew Jackson ở mặt trước, mặt sau là Nhà Trắng.", "color": "xanh lá cây"},
#         "50": {"year_of_issue": "2002", "description": "Tờ 50 Đô la Mỹ tại Đông Timor có màu hồng, phát hành năm 2002, với hình Ulysses S. Grant ở mặt trước, mặt sau là Điện Capitol.", "color": "hồng"},
#         "100": {"year_of_issue": "2002", "description": "Tờ 100 Đô la Mỹ tại Đông Timor có màu xanh lục, phát hành năm 2002, với hình Benjamin Franklin ở mặt trước, mặt sau là Tuyên ngôn Độc lập.", "color": "xanh lục"}
#     },
#     "Việt Nam": {
#         "1": {"year_of_issue": "1985", "description": "Tờ 1 Đồng Việt Nam có màu xanh lá cây, được phát hành năm 1985, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là cánh đồng lúa nước, biểu tượng của nền nông nghiệp Việt Nam.", "color": "xanh lá cây"},
#         "5": {"year_of_issue": "1985", "description": "Tờ 5 Đồng Việt Nam có màu đỏ cam, được phát hành năm 1985, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là cảnh thu hoạch lúa với nông dân trên đồng ruộng.", "color": "đỏ cam"},
#         "10": {"year_of_issue": "1985", "description": "Tờ 10 Đồng Việt Nam có màu xanh dương, được phát hành năm 1985, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Cảng Hải Phòng, biểu tượng của sự phát triển thương mại và giao thương hàng hải.", "color": "xanh dương"},
#         "20": {"year_of_issue": "1985", "description": "Tờ 20 Đồng Việt Nam có màu nâu tím, được phát hành năm 1985, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Nhà máy Thủy điện Hòa Bình, công trình thủy điện lớn nhất Việt Nam thời điểm đó.", "color": "nâu tím"},
#         "50": {"year_of_issue": "1985", "description": "Tờ 50 Đồng Việt Nam có màu xanh lục, được phát hành năm 1985, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là cảnh khai thác than tại Quảng Ninh, thể hiện vai trò của ngành công nghiệp khai khoáng.", "color": "xanh lục"},
#         "100": {"year_of_issue": "1985", "description": "Tờ 100 Đồng Việt Nam có màu đỏ nâu, được phát hành năm 1985, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Nhà máy Thép Thái Nguyên, biểu tượng của ngành công nghiệp nặng.", "color": "đỏ nâu"},
#         "200": {"year_of_issue": "1987", "description": "Tờ 200 Đồng Việt Nam có màu nâu vàng, được phát hành năm 1987, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Nhà máy Cơ khí Hà Nội, thể hiện sự phát triển công nghiệp.", "color": "nâu vàng"},
#         "500": {"year_of_issue": "1989", "description": "Tờ 500 Đồng Việt Nam có màu nâu đỏ, được phát hành năm 1989, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Nhà máy dệt Nam Định, biểu tượng của ngành công nghiệp dệt.", "color": "nâu đỏ"},
#         "1000": {"year_of_issue": "1988", "description": "Tờ 1000 Đồng Việt Nam có màu nâu vàng, được phát hành năm 1988, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là phong cảnh núi rừng Tây Nguyên với voi.", "color": "nâu vàng"},
#         "2000": {"year_of_issue": "1988", "description": "Tờ 2000 Đồng Việt Nam có màu xám xanh, được phát hành năm 1988, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Nhà máy dệt Nam Định, biểu tượng của sự phát triển công nghiệp dệt may.", "color": "xám xanh"},
#         "5000": {"year_of_issue": "1991", "description": "Tờ 5000 Đồng Việt Nam có màu xanh dương, được phát hành năm 1991, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Nhà máy thủy điện Trị An.", "color": "xanh dương"},
#         "10000": {"year_of_issue": "2006", "description": "Tờ 10000 Đồng Việt Nam có màu nâu vàng, được phát hành năm 2006, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Giàn khoan dầu khí ngoài khơi Việt Nam.", "color": "nâu vàng"},
#         "20000": {"year_of_issue": "2006", "description": "Tờ 20000 Đồng Việt Nam có màu xanh dương, được phát hành năm 2006, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Khuê Văn Các – biểu tượng của Văn Miếu Quốc Tử Giám.", "color": "xanh dương"},
#         "50000": {"year_of_issue": "2004", "description": "Tờ 50000 Đồng Việt Nam có màu hồng tím, được phát hành năm 2004, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Khu di tích Nguyễn Sinh Sắc - Nguyễn Tất Thành tại Nghệ An.", "color": "hồng tím"},
#         "100000": {"year_of_issue": "2000", "description": "Tờ 100000 Đồng Việt Nam có màu xanh lá cây, được phát hành năm 2000, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Văn Miếu - Quốc Tử Giám, biểu tượng của truyền thống hiếu học.", "color": "xanh lá cây"},
#         "200000": {"year_of_issue": "2006", "description": "Tờ 200000 Đồng Việt Nam có màu đỏ cam, được phát hành năm 2006, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Vịnh Hạ Long, di sản thiên nhiên thế giới.", "color": "đỏ cam"},
#         "500000": {"year_of_issue": "2003", "description": "Tờ 500000 Đồng Việt Nam có màu xanh lục, được phát hành năm 2003, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Ngôi nhà sàn của Chủ tịch Hồ Chí Minh tại Nam Đàn, Nghệ An.", "color": "xanh lục"}
#     }
# }

# # Khởi tạo LLM và RetrievalQA chain cho chatbot (giữ nguyên)
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
#         logger.info(f"vector_db_path: {vector_db_path}")
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

# try:
#     db = read_vectors_db()
#     llm = load_llm(api_key)
#     template = """system
#     Sử dụng thông tin sau đây để trả lời câu hỏi. Nếu không biết, hãy nói 'Tôi không biết'.
#     {context}
#     user
#     {question}
#     assistant"""
#     prompt = create_prompt(template)
#     llm_chain = create_qa_chain(prompt, llm, db)
# except Exception as e:
#     logger.error(f"Lỗi khi khởi tạo chatbot: {e}")
#     raise RuntimeError(f"Lỗi khi khởi tạo chatbot: {e}")

# def fetch_top_5_web_content(query):
#     try:
#         urls = list(search(query, num_results=5))
#         web_contents = []
#         for url in urls:
#             if not url.startswith(('http://', 'https://')):
#                 continue
#             try:
#                 response = requests.get(url, timeout=5)
#                 soup = BeautifulSoup(response.text, 'html.parser')
#                 paragraphs = soup.find_all('p')
#                 content = " ".join(p.get_text() for p in paragraphs if p.get_text().strip())[:1000]
#                 web_contents.append({"url": url, "content": content or "Không có nội dung phù hợp."})
#             except Exception as e:
#                 web_contents.append({"url": url, "content": f"Lỗi khi cào dữ liệu: {str(e)}"})
#         while len(web_contents) < 5:
#             web_contents.append({"url": "N/A", "content": "Không tìm thấy nội dung từ web."})
#         return web_contents
#     except Exception as e:
#         logger.error(f"Lỗi khi tìm kiếm web: {e}")
#         return [{"url": "N/A", "content": f"Lỗi khi tìm kiếm web: {str(e)}"}] * 5

# def process_with_gemini(web_contents, query):
#     try:
#         documents = [Document(page_content=item["content"], metadata={"url": item.get("url", "N/A")}) for item in web_contents]
#         embeddings = GPT4AllEmbeddings()
#         vector_store = FAISS.from_documents(documents, embeddings)
#         qa_chain = RetrievalQA.from_chain_type(
#             llm=llm,
#             chain_type="stuff",
#             retriever=vector_store.as_retriever(search_kwargs={"k": 3}),
#             return_source_documents=True
#         )
#         result = qa_chain({"query": query})
#         return result["result"]
#     except Exception as e:
#         logger.error(f"Lỗi khi xử lý với Gemini: {e}")
#         return f"Lỗi khi xử lý với Gemini: {str(e)}"

# def register_routes(app, limiter):
#     @app.get("/")
#     async def root():
#         return FileResponse(STATIC_DIR / "index.html")

#     @app.post("/detect_money")
#     async def detect_money(file: UploadFile = File(...), request: Request = None):
#         try:
#             token = request.headers.get("Authorization")
#             if token:
#                 connection = get_db_connection()
#                 if not connection:
#                     raise HTTPException(status_code=500, detail="Không thể kết nối đến cơ sở dữ liệu!")
#                 try:
#                     cursor = connection.cursor()
#                     token_decoded = base64.b64decode(token.split()[1]).decode().split(":")
#                     email = token_decoded[0]
#                     cursor.execute("SELECT balance FROM users WHERE email = %s", (email,))
#                     balance = cursor.fetchone()
#                     if not balance or balance[0] < 1.0:
#                         raise HTTPException(status_code=403, detail="Số dư không đủ để sử dụng tính năng này!")
#                     cursor.execute("UPDATE users SET balance = balance - 1 WHERE email = %s", (email,))
#                     connection.commit()
#                 except Error as e:
#                     raise HTTPException(status_code=500, detail=f"Lỗi khi truy vấn cơ sở dữ liệu: {e}")
#                 finally:
#                     if connection.is_connected():
#                         cursor.close()
#                         connection.close()

#             contents = await file.read()
#             nparr = np.frombuffer(contents, np.uint8)
#             image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
#             if image is None:
#                 raise HTTPException(status_code=400, detail="Không thể đọc file ảnh!")

#             image_base64, detections = process_image_with_yolo(image)

#             detection_info = {}
#             money_details = {}
#             additional_info = ""

#             if detections:
#                 detection = detections[0]
#                 predicted_class = f"{detection['country']}: {detection['class_name']}"
#                 confidence = detection["confidence"]
#                 logger.info(f"Nhận diện thành công: {predicted_class} với độ tin cậy {confidence}")

#                 # Chuẩn hóa predicted_class để loại bỏ hậu tố không cần thiết
#                 predicted_class = predicted_class.replace("Ngàn", "").replace("Ngan", "").replace(".", "")
#                 # Loại bỏ hậu tố như "_campuchia" nếu có
#                 if "_" in predicted_class:
#                     predicted_class = predicted_class.split("_")[0]
#                 country, denomination = predicted_class.split(": ")
#                 denomination = denomination.strip()

#                 detection_info = {
#                     "denomination": predicted_class,
#                     "confidence": f"{confidence:.2f}"
#                 }

#                 # Kiểm tra trong MONEY_DATA trước
#                 if country in MONEY_DATA and denomination in MONEY_DATA[country]:
#                     money_details = MONEY_DATA[country][denomination]
#                     additional_info = "Thông tin được lấy từ cơ sở dữ liệu tĩnh."
#                 else:
#                     # Nếu không có trong MONEY_DATA, thử tìm kiếm web
#                     search_query = f"{predicted_class} banknote year of issue description site:*.edu | site:*.org | site:*.gov -inurl:(signup | login)"
#                     search_data = {"year_of_issue": "Không rõ", "description": "Không có mô tả chi tiết.", "color": "Không rõ"}
#                     try:
#                         search_results = list(search(search_query, num_results=1))
#                         if search_results and search_results[0].startswith(('http://', 'https://')):
#                             response = requests.get(search_results[0], timeout=5)
#                             soup = BeautifulSoup(response.text, 'html.parser')
#                             paragraphs = soup.find_all('p')
#                             description = " ".join(p.get_text() for p in paragraphs[:3] if p.get_text().strip())[:500]
#                             year = "Không rõ"
#                             for p in paragraphs:
#                                 text = p.get_text().lower()
#                                 if "year" in text or "issued" in text or "phát hành" in text:
#                                     words = text.split()
#                                     for word in words:
#                                         if word.isdigit() and 1900 <= int(word) <= 2025:
#                                             year = word
#                                             break
#                                     if year != "Không rõ":
#                                         break
#                             search_data = {
#                                 "year_of_issue": year,
#                                 "description": description or "Không có mô tả chi tiết.",
#                                 "color": "Không rõ"
#                             }
#                             money_details = search_data
#                             additional_info = "Thông tin được lấy từ kết quả tìm kiếm web."
#                     except Exception as e:
#                         logger.error(f"Lỗi khi tìm kiếm web: {e}")
#                         money_details = {
#                             "year_of_issue": "Không tìm thấy",
#                             "description": "Không có thông tin chi tiết do lỗi tìm kiếm.",
#                             "color": "N/A"
#                         }
#                         additional_info = "Không tìm thấy thông tin từ web."

#                     # Nếu web không trả về, thử Gemini
#                     if "Không rõ" in money_details["year_of_issue"] or "Không có" in money_details["description"]:
#                         web_contents = fetch_top_5_web_content(f"{predicted_class} tiền tệ")
#                         query = f"Thông tin chi tiết về '{predicted_class}' bao gồm màu sắc, năm phát hành, và đặc điểm nổi bật."
#                         try:
#                             gemini_response = process_with_gemini(web_contents, query)
#                             money_details = {
#                                 "year_of_issue": "Không rõ",
#                                 "description": gemini_response,
#                                 "color": "Không rõ"
#                             }
#                             gemini_response_lower = gemini_response.lower()
#                             colors = ["xanh", "đỏ", "tím", "vàng", "nâu", "cam", "xám", "hồng"]
#                             for color in colors:
#                                 if color in gemini_response_lower:
#                                     money_details["color"] = color
#                                     break
#                             for word in gemini_response.split():
#                                 if word.isdigit() and 1900 <= int(word) <= 2025:
#                                     money_details["year_of_issue"] = word
#                                     break
#                             additional_info = "Thông tin được lấy từ Gemini API dựa trên dữ liệu web."
#                         except Exception as e:
#                             logger.error(f"Lỗi khi gọi Gemini API: {e}")
#                             money_details = {
#                                 "year_of_issue": "Không tìm thấy",
#                                 "description": "Không có thông tin chi tiết do lỗi xử lý.",
#                                 "color": "N/A"
#                             }
#                             additional_info = "Không tìm thấy thông tin từ Gemini API."
#             else:
#                 logger.info("Không nhận diện được tờ tiền.")
#                 detection_info = {
#                     "denomination": "Không nhận diện được",
#                     "confidence": "N/A"
#                 }
#                 money_details = {
#                     "year_of_issue": "N/A",
#                     "description": "Không có thông tin do không nhận diện được tờ tiền.",
#                     "color": "N/A"
#                 }
#                 additional_info = "Không nhận diện được tờ tiền."

#             # Đảm bảo money_details luôn có các key cần thiết để tránh KeyError
#             money_details.setdefault("year_of_issue", "N/A")
#             money_details.setdefault("description", "Không có thông tin chi tiết.")
#             money_details.setdefault("color", "N/A")

#             return {
#                 "image": f"data:image/jpeg;base64,{image_base64}",
#                 "detection_info": detection_info,
#                 "money_details": money_details,
#                 "additional_info": additional_info
#             }
#         except Exception as e:
#             logger.error(f"Lỗi khi xử lý nhận diện tiền: {e}")
#             raise HTTPException(status_code=500, detail=f"Lỗi khi xử lý: {str(e)}")

#     @app.post("/detect_money_webcam")
#     async def detect_money_webcam():
#         cap = cv2.VideoCapture(0)
#         if not cap.isOpened():
#             raise HTTPException(status_code=500, detail="Không thể truy cập webcam!")

#         try:
#             ret, frame = cap.read()
#             if not ret:
#                 raise HTTPException(status_code=500, detail="Không thể nhận frame từ webcam!")

#             image_base64, detections = process_image_with_yolo(frame)

#             additional_info = ""
#             if detections:
#                 predicted_class = f"{detections[0]['country']}: {detections[0]['class_name']}"
#                 logger.info(f"Nhận diện thành công từ webcam: {predicted_class}")
#                 web_contents = fetch_top_5_web_content(f"{predicted_class} tiền tệ")
#                 query = f"Thông tin chi tiết về '{predicted_class}' bao gồm màu sắc, năm phát hành, và đặc điểm nổi bật."
#                 additional_info = process_with_gemini(web_contents, query)
#                 logger.info(f"Thông tin bổ sung: {additional_info}")
#             else:
#                 logger.info("Không nhận diện được tờ tiền từ webcam.")

#             cap.release()
#             return JSONResponse(content={
#                 "image": f"data:image/jpeg;base64,{image_base64}",
#                 "detections": detections,
#                 "additional_info": additional_info
#             })
#         except Exception as e:
#             cap.release()
#             logger.error(f"Lỗi khi xử lý webcam: {e}")
#             raise HTTPException(status_code=500, detail=f"Lỗi khi xử lý webcam: {str(e)}")

#     @app.post("/chat")
#     @limiter.limit("5/minute")
#     async def chat(request: Request):
#         data = await request.json()
#         question = data.get("question")
#         if not question:
#             raise HTTPException(status_code=400, detail="Vui lòng cung cấp câu hỏi!")
        
#         try:
#             logger.info(f"Nhận được câu hỏi: {question}")
            
#             # Sử dụng RetrievalQA chain với vector database
#             response = llm_chain.invoke({"query": question})
#             result = response["result"].strip()
            
#             # Nếu không có câu trả lời từ vector DB hoặc không rõ ràng, tìm kiếm web
#             if not result or "Tôi không biết" in result or len(result) < 20:
#                 logger.info("Không tìm thấy câu trả lời từ vector DB, chuyển sang tìm kiếm web.")
#                 web_contents = fetch_top_5_web_content(question)
#                 logger.info(f"Nội dung từ web: {web_contents}")
#                 result = process_with_gemini(web_contents, question)
            
#             # Fallback data nếu cả hai cách trên không hoạt động
#             if "Lỗi" in result or "Không tìm thấy" in result:
#                 asean_countries = {
#                     "việt nam": ["đồng", "vnd"],
#                     "thái lan": ["baht", "thb"],
#                     "campuchia": ["riel", "khr"],
#                     "lào": ["kip", "lak"],
#                     "myanmar": ["kyat", "mmk"],
#                     "malaysia": ["ringgit", "myr"],
#                     "singapore": ["đô la singapore", "sgd"],
#                     "philippines": ["peso", "php"],
#                     "indonesia": ["rupiah", "idr"],
#                     "brunei": ["đô la brunei", "bnd"],
#                     "đông timor": ["đô la mỹ", "usd"],
#                     "mỹ": ["đô la", "usd"]
#                 }
                
#                 fallback_data = {
#                     "brunei": {
#                         "1": {"color": "xanh lá cây", "year": "2004"},
#                         "5": {"color": "cam", "year": "2004"},
#                         "10": {"color": "đỏ", "year": "2004"},
#                         "20": {"color": "xanh dương", "year": "2004"},
#                         "50": {"color": "xanh dương", "year": "2004"},
#                         "100": {"color": "tím", "year": "2004"},
#                         "500": {"color": "vàng", "year": "2004"},
#                         "1000": {"color": "xám", "year": "2004"}
#                     },
#                     "campuchia": {
#                         "50": {"color": "tím", "year": "1995"},
#                         "100": {"color": "xanh dương", "year": "1995"},
#                         "200": {"color": "đỏ", "year": "1995"},
#                         "500": {"color": "đỏ", "year": "1995"},
#                         "1000": {"color": "nâu", "year": "1995"},
#                         "2000": {"color": "xám", "year": "1995"},
#                         "5000": {"color": "vàng", "year": "1995"},
#                         "10000": {"color": "xanh lá cây", "year": "1995"}
#                     },
#                     "đông timor": {
#                         "1": {"color": "xanh lục/xám", "year": "2009"},
#                         "5": {"color": "tím", "year": "2009"},
#                         "10": {"color": "cam", "year": "2009"},
#                         "20": {"color": "xanh lá cây", "year": "2009"},
#                         "50": {"color": "hồng", "year": "2009"},
#                         "100": {"color": "xanh lục", "year": "2013"}
#                     },
#                     "indonesia": {
#                         "1000": {"color": "xám", "year": "2016"},
#                         "2000": {"color": "xanh lá cây", "year": "2016"},
#                         "5000": {"color": "nâu", "year": "2016"},
#                         "10000": {"color": "tím", "year": "2016"},
#                         "20000": {"color": "xanh lá cây", "year": "2016"},
#                         "50000": {"color": "xanh dương", "year": "2016"},
#                         "100000": {"color": "đỏ", "year": "2016"}
#                     },
#                     "lào": {
#                         "500": {"color": "xanh dương", "year": "2003"},
#                         "1000": {"color": "đỏ", "year": "2003"},
#                         "2000": {"color": "nâu", "year": "2003"},
#                         "5000": {"color": "tím", "year": "2003"},
#                         "10000": {"color": "xanh lá cây", "year": "2003"},
#                         "20000": {"color": "vàng", "year": "2003"},
#                         "50000": {"color": "cam", "year": "2003"},
#                         "100000": {"color": "xanh dương", "year": "2003"}
#                     },
#                     "malaysia": {
#                         "1": {"color": "xanh dương", "year": "2012"},
#                         "5": {"color": "xanh lá cây", "year": "2012"},
#                         "10": {"color": "đỏ", "year": "2012"},
#                         "20": {"color": "cam", "year": "2012"},
#                         "50": {"color": "xanh dương/tím", "year": "2012"},
#                         "100": {"color": "tím", "year": "2012"}
#                     },
#                     "myanmar": {
#                         "50": {"color": "tím", "year": "1990"},
#                         "100": {"color": "xanh dương", "year": "1990"},
#                         "200": {"color": "nâu", "year": "1990"},
#                         "500": {"color": "cam", "year": "1990"},
#                         "1000": {"color": "xanh lá cây", "year": "1990"},
#                         "5000": {"color": "đỏ", "year": "1990"},
#                         "10000": {"color": "vàng", "year": "1990"}
#                     },
#                     "philippines": {
#                         "20": {"color": "cam", "year": "2010"},
#                         "50": {"color": "đỏ", "year": "2010"},
#                         "100": {"color": "tím", "year": "2010"},
#                         "200": {"color": "xanh lá cây", "year": "2010"},
#                         "500": {"color": "vàng", "year": "2010"},
#                         "1000": {"color": "xanh dương", "year": "2010"}
#                     },
#                     "singapore": {
#                         "2": {"color": "tím", "year": "1999"},
#                         "5": {"color": "xanh lá cây", "year": "1999"},
#                         "10": {"color": "đỏ", "year": "1999"},
#                         "50": {"color": "xanh dương", "year": "1999"},
#                         "100": {"color": "cam", "year": "1999"},
#                         "1000": {"color": "tím", "year": "1999"}
#                     },
#                     "thái lan": {
#                         "20": {"color": "xanh lá cây", "year": "2018"},
#                         "50": {"color": "xanh dương", "year": "2018"},
#                         "100": {"color": "đỏ", "year": "2018"},
#                         "500": {"color": "tím", "year": "2018"},
#                         "1000": {"color": "xám", "year": "2018"}
#                     },
#                     "việt nam": {
#                         "100": {"color": "xanh lá cây", "year": "2003"},
#                         "200": {"color": "nâu", "year": "2003"},
#                         "500": {"color": "xanh dương", "year": "2003"},
#                         "1000": {"color": "xanh lá cây", "year": "2003"},
#                         "2000": {"color": "xám", "year": "2003"},
#                         "5000": {"color": "xanh lá cây", "year": "2003"},
#                         "10000": {"color": "xanh dương", "year": "2003"},
#                         "20000": {"color": "xanh dương nhạt", "year": "2003"},
#                         "50000": {"color": "nâu tím đỏ", "year": "2003"},
#                         "100000": {"color": "xanh lá cây đậm", "year": "2003"},
#                         "200000": {"color": "đỏ nâu", "year": "2003"},
#                         "500000": {"color": "xanh dương", "year": "2003"}
#                     }
#                 }
                
#                 question_lower = question.lower()
#                 country = None
#                 denomination = None
#                 for c in asean_countries.keys():
#                     if c in question_lower:
#                         country = c
#                         break
#                 if not country and "đô la" in question_lower:
#                     country = "mỹ" if "mỹ" in question_lower else "đông timor"
                
#                 for word in question_lower.split():
#                     if word.replace(".", "").isdigit():
#                         denomination = word.replace(".", "")
#                         break
                
#                 if country and denomination and country in fallback_data and denomination in fallback_data[country]:
#                     if "màu" in question_lower or "màu sắc" in question_lower:
#                         result = f"Tờ {denomination} {country.capitalize()} có màu {fallback_data[country][denomination]['color']}."
#                     elif "năm" in question_lower or "phát hành" in question_lower:
#                         result = f"Tờ {denomination} {country.capitalize()} được phát hành năm {fallback_data[country][denomination]['year']}."
#                     else:
#                         result = f"Thông tin về tờ {denomination} {country.capitalize()}: màu {fallback_data[country][denomination]['color']}, năm phát hành {fallback_data[country][denomination]['year']}."
#                 elif "tiền là gì" in question_lower:
#                     result = "Tiền là phương tiện trao đổi được chấp nhận rộng rãi để mua bán hàng hóa, dịch vụ, hoặc thanh toán nợ."
#                 else:
#                     result = "Tôi không có đủ thông tin để trả lời câu hỏi này."
            
#             logger.info(f"Kết quả trả lời: {result}")
#             return {"response": result}
#         except Exception as e:
#             logger.error(f"Chat error: {str(e)}")
#             if "429" in str(e) or "quota" in str(e):
#                 return {"response": "Đã vượt quá giới hạn quota của Gemini API. Vui lòng thử lại sau hoặc kiểm tra tài khoản của bạn."}
#             return {"response": f"Lỗi khi xử lý câu hỏi: {str(e)}"}

#     @app.post("/login")
#     async def login_route(name: str = Form(...), password: str = Form(...)):
#         return await login(name, password)

#     @app.post("/signup")
#     async def signup_route(name: str = Form(...), email: str = Form(...), password: str = Form(...)):
#         return await signup(name, email, password)

#     @app.post("/forgot_password")
#     async def forgot_password_route(email: str = Form(...)):
#         return await forgot_password(email)

#     @app.post("/deposit")
#     async def deposit(amount: float = Form(...), request: Request = None):
#         token = request.headers.get("Authorization")
#         if not token:
#             raise HTTPException(status_code=401, detail="Yêu cầu đăng nhập để nạp tiền!")
        
#         connection = get_db_connection()
#         if not connection:
#             raise HTTPException(status_code=500, detail="Không thể kết nối đến cơ sở dữ liệu!")
        
#         try:
#             cursor = connection.cursor()
#             token_decoded = base64.b64decode(token.split()[1]).decode().split(":")
#             email = token_decoded[0]
            
#             cursor.execute("SELECT id FROM users WHERE email = %s", (email,))
#             user = cursor.fetchone()
#             if not user:
#                 raise HTTPException(status_code=404, detail="Người dùng không tồn tại!")
#             user_id = user[0]
            
#             query_insert = "INSERT INTO deposits (user_id, amount, status) VALUES (%s, %s, %s)"
#             cursor.execute(query_insert, (user_id, amount, "pending"))
            
#             cursor.execute("UPDATE users SET balance = balance + %s WHERE id = %s", (amount, user_id))
#             cursor.execute("UPDATE deposits SET status = 'completed' WHERE user_id = %s AND amount = %s AND status = 'pending'", (user_id, amount))
            
#             connection.commit()
#             logger.info(f"Nạp tiền thành công: {amount} cho người dùng {email}")
#             return {"message": f"Nạp {amount} thành công! Số dư đã được cập nhật."}
#         except Error as e:
#             raise HTTPException(status_code=500, detail=f"Lỗi khi xử lý nạp tiền: {e}")
#         finally:
#             if connection.is_connected():
#                 cursor.close()
#                 connection.close()

from fastapi import UploadFile, File, Response, Request, HTTPException, Body
from fastapi.responses import FileResponse, JSONResponse
from config import STATIC_DIR, logger, mysql_host, mysql_user, mysql_password, mysql_database
from yolo_processing import process_image_with_yolo
from mysql.connector import connect, Error
from auth import login, signup, forgot_password
from slowapi import Limiter
from pydantic import BaseModel, Field
import cv2
import base64
import numpy as np
import json
from chatbot import load_llm, fetch_top_5_web_content, process_with_gemini, process_with_openai

# Define supported models
SUPPORTED_MODELS = {
    "gemini-1.5-pro": "ChatGoogleGenerativeAI",
    "openai-gpt-4": "OpenAI"
}

# Pydantic model for JSON body validation
class LLMUpdateRequest(BaseModel):
    model_name: str = Field(..., min_length=1, description="Tên mô hình, không được rỗng")
    api_key: str = Field(..., min_length=1, description="API key, không được rỗng")

# MONEY_DATA (giữ nguyên)
MONEY_DATA = {
    "Brunei": {
        "1": {"year_of_issue": "2004", "description": "Tờ 1 Đô la Brunei có màu xanh lá cây, được phát hành năm 2004, với chân dung Quốc vương Sultan Hassanal Bolkiah và Quốc huy Brunei ở mặt trước, mặt sau mô phỏng kiến trúc truyền thống Brunei cùng các biểu tượng văn hóa và hoa văn Hồi giáo.", "color": "xanh lá cây"},
        "5": {"year_of_issue": "2004", "description": "Tờ 5 Đô la Brunei có màu cam, được phát hành năm 2004, với chân dung Quốc vương Sultan Hassanal Bolkiah và Quốc huy Brunei ở mặt trước, mặt sau là hình ảnh Cảng Muara, biểu tượng sự phát triển thương mại của quốc gia.", "color": "cam"},
        "10": {"year_of_issue": "2004", "description": "Tờ 10 Đô la Brunei có màu đỏ, được phát hành năm 2004, với hình ảnh Quốc vương Sultan Hassanal Bolkiah và Quốc huy Brunei ở mặt trước, mặt sau là Trường Đại học Brunei Darussalam, thể hiện sự đầu tư vào giáo dục.", "color": "đỏ"},
        "20": {"year_of_issue": "2004", "description": "Tờ 20 Đô la Brunei có màu xanh dương, được phát hành năm 2004, với chân dung Quốc vương Sultan Hassanal Bolkiah và Quốc huy Brunei ở mặt trước, mặt sau là Thánh đường Omar Ali Saifuddien và con thuyền Hoàng gia Brunei.", "color": "xanh dương"},
        "50": {"year_of_issue": "2004", "description": "Tờ 50 Đô la Brunei có màu xanh dương, được phát hành năm 2004, với hình ảnh Quốc vương Sultan Hassanal Bolkiah và Quốc huy Brunei ở mặt trước, mặt sau là Sông Brunei và cảnh quan thiên nhiên.", "color": "xanh dương"},
        "100": {"year_of_issue": "2004", "description": "Tờ 100 Đô la Brunei có màu tím, được phát hành năm 2004, với hình ảnh Quốc vương Sultan Hassanal Bolkiah và Quốc huy Brunei ở mặt trước, mặt sau là Tòa nhà Quốc hội Brunei.", "color": "tím"},
        "10000": {"year_of_issue": "2006", "description": "Tờ 10000 Đô la Brunei có màu xanh lá cây, được phát hành năm 2006, với chân dung Quốc vương Sultan Hassanal Bolkiah ở mặt trước, mặt sau là Cung điện Istana Nurul Iman, dinh thự lớn nhất thế giới.", "color": "xanh lá cây"}
    },
    "Campuchia": {
        "50": {"year_of_issue": "2002", "description": "Tờ 50 Riel Campuchia có màu tím, được phát hành năm 2002, với hình ảnh Angkor Wat và hoa văn truyền thống Khmer ở mặt trước, mặt sau là các biểu tượng văn hóa và nghệ thuật Khmer.", "color": "tím"},
        "100": {"year_of_issue": "2002", "description": "Tờ 100 Riel Campuchia có màu xanh dương, được phát hành năm 2002, với hình ảnh Quốc vương Norodom Sihanouk ở mặt trước, mặt sau là cảnh quan thiên nhiên Campuchia.", "color": "xanh dương"},
        "500": {"year_of_issue": "2001", "description": "Tờ 500 Riel Campuchia có màu đỏ, được phát hành năm 2001, với hình ảnh Đền Preah Vihear ở mặt trước, mặt sau là hình ảnh nền văn hóa Khmer truyền thống.", "color": "đỏ"},
        "1000": {"year_of_issue": "2013", "description": "Tờ 1000 Riel Campuchia có màu nâu, được phát hành năm 2013, với hình ảnh Quốc vương Norodom Sihanouk ở mặt trước, mặt sau là Cung điện Hoàng gia Campuchia.", "color": "nâu"},
        "2000": {"year_of_issue": "2008", "description": "Tờ 2000 Riel Campuchia có màu xám, được phát hành năm 2008, với hình ảnh Hoàng hậu Norodom Monineath ở mặt trước, mặt sau là các ngôi đền và di tích lịch sử Khmer.", "color": "xám"},
        "5000": {"year_of_issue": "2015", "description": "Tờ 5000 Riel Campuchia có màu vàng, được phát hành năm 2015, với hình ảnh Quốc vương Norodom Sihamoni ở mặt trước, mặt sau là sông Mekong.", "color": "vàng"},
        "10000": {"year_of_issue": "2015", "description": "Tờ 10000 Riel Campuchia có màu xanh lá cây, được phát hành năm 2015, với hình ảnh Quốc vương Norodom Sihanouk ở mặt trước, mặt sau là công trình kiến trúc và cảnh quan thiên nhiên Campuchia.", "color": "xanh lá cây"},
        "20000": {"year_of_issue": "2017", "description": "Tờ 20000 Riel Campuchia có màu không rõ, được phát hành năm 2017, với hình ảnh Quốc vương Norodom Sihanouk ở mặt trước, mặt sau là Cầu Kampong Kdei.", "color": "không rõ"}
    },
    "Indonesia": {
        "1000": {"year_of_issue": "2016", "description": "Tờ 1000 Rupiah có màu xám, phát hành năm 2016, với hình anh hùng quốc gia Kapitan Pattimura ở mặt trước, mặt sau là cảnh đảo Banda Neira.", "color": "xám"},
        "2000": {"year_of_issue": "2016", "description": "Tờ 2000 Rupiah có màu xanh lá cây, phát hành năm 2016, với hình anh hùng quốc gia Mohammad Husni Thamrin ở mặt trước, mặt sau là cảnh đảo Ngarai Sianok.", "color": "xanh lá cây"},
        "5000": {"year_of_issue": "2016", "description": "Tờ 5000 Rupiah có màu nâu, phát hành năm 2016, với hình anh hùng quốc gia Idham Chalid ở mặt trước, mặt sau là cảnh núi Gunung Tambora.", "color": "nâu"},
        "10000": {"year_of_issue": "2016", "description": "Tờ 10000 Rupiah có màu tím, phát hành năm 2016, với hình anh hùng quốc gia Frans Kaisiepo ở mặt trước, mặt sau là cảnh đảo Wakatobi.", "color": "tím"},
        "20000": {"year_of_issue": "2016", "description": "Tờ 20000 Rupiah có màu xanh lá cây, phát hành năm 2016, với hình anh hùng quốc gia Sam Ratulangi ở mặt trước, mặt sau là cảnh đảo Derawan.", "color": "xanh lá cây"},
        "50000": {"year_of_issue": "2016", "description": "Tờ 50000 Rupiah có màu xanh dương, phát hành năm 2016, với hình anh hùng quốc gia Djuanda Kartawidjaja ở mặt trước, mặt sau là cảnh đảo Komodo.", "color": "xanh dương"},
        "100000": {"year_of_issue": "2016", "description": "Tờ 100000 Rupiah có màu đỏ, phát hành năm 2016, với hình Sukarno và Hatta ở mặt trước, mặt sau là Tòa nhà Quốc hội Indonesia.", "color": "đỏ"}
    },
    "Lào": {
        "100": {"year_of_issue": "2003", "description": "Tờ 100 Kip có màu xanh lá cây, phát hành năm 2003, với hình Chủ tịch Kaysone Phomvihane ở mặt trước, mặt sau là cảnh nông thôn Lào.", "color": "xanh lá cây"},
        "500": {"year_of_issue": "2003", "description": "Tờ 500 Kip có màu xanh dương, phát hành năm 2003, với hình Chủ tịch Kaysone Phomvihane ở mặt trước, mặt sau là cảnh đập thủy điện.", "color": "xanh dương"},
        "1000": {"year_of_issue": "2003", "description": "Tờ 1000 Kip có màu đỏ, phát hành năm 2003, với hình Chủ tịch Kaysone Phomvihane ở mặt trước, mặt sau là cảnh chùa Pha That Luang.", "color": "đỏ"},
        "2000": {"year_of_issue": "2003", "description": "Tờ 2000 Kip có màu nâu, phát hành năm 2003, với hình Chủ tịch Kaysone Phomvihane ở mặt trước, mặt sau là cảnh cầu Friendship.", "color": "nâu"},
        "5000": {"year_of_issue": "2003", "description": "Tờ 5000 Kip có màu tím, phát hành năm 2003, với hình Chủ tịch Kaysone Phomvihane ở mặt trước, mặt sau là cảnh chùa Wat Xieng Thong.", "color": "tím"},
        "10000": {"year_of_issue": "2003", "description": "Tờ 10000 Kip có màu xanh lá cây, phát hành năm 2003, với hình Chủ tịch Kaysone Phomvihane ở mặt trước, mặt sau là cảnh thiên nhiên Lào.", "color": "xanh lá cây"},
        "20000": {"year_of_issue": "2003", "description": "Tờ 20000 Kip có màu vàng, phát hành năm 2003, với hình Chủ tịch Kaysone Phomvihane ở mặt trước, mặt sau là cảnh thác nước Kuang Si.", "color": "vàng"}
    },
    "Malaysia": {
        "1": {"year_of_issue": "2012", "description": "Tờ 1 Ringgit Malaysia có màu xanh dương, được phát hành năm 2012, với chân dung Yang di-Pertuan Agong Tuanku Abdul Rahman ở mặt trước, mặt sau là hình ảnh Wau Bulan, một loại diều truyền thống.", "color": "xanh dương"},
        "5": {"year_of_issue": "2012", "description": "Tờ 5 Ringgit Malaysia có màu xanh lá cây, được phát hành năm 2012, với chân dung Yang di-Pertuan Agong Tuanku Abdul Rahman ở mặt trước, mặt sau là rừng mưa nhiệt đới Malaysia.", "color": "xanh lá cây"},
        "10": {"year_of_issue": "2012", "description": "Tờ 10 Ringgit Malaysia có màu đỏ, được phát hành năm 2012, với chân dung Yang di-Pertuan Agong Tuanku Abdul Rahman ở mặt trước, mặt sau là hình ảnh Rafflesia, loài hoa khổng lồ biểu tượng.", "color": "đỏ"},
        "20": {"year_of_issue": "2012", "description": "Tờ 20 Ringgit Malaysia có màu cam, được phát hành năm 2012, với chân dung Yang di-Pertuan Agong Tuanku Abdul Rahman ở mặt trước, mặt sau là hình ảnh rùa biển Hawksbill và rùa xanh, đại diện cho hệ sinh thái biển.", "color": "cam"},
        "50": {"year_of_issue": "2012", "description": "Tờ 50 Ringgit Malaysia có màu xanh dương/tím, được phát hành năm 2012, với chân dung Yang di-Pertuan Agong Tuanku Abdul Rahman ở mặt trước, mặt sau là cây cọ dầu, biểu tượng của nền kinh tế Malaysia.", "color": "xanh dương/tím"},
        "100": {"year_of_issue": "2012", "description": "Tờ 100 Ringgit Malaysia có màu tím, được phát hành năm 2012, với chân dung Yang di-Pertuan Agong Tuanku Abdul Rahman ở mặt trước, mặt sau là Núi Kinabalu, đỉnh núi cao nhất Đông Nam Á.", "color": "tím"}
    },
    "Myanmar": {
        "50": {"year_of_issue": "1997", "description": "Tờ 50 Kyat có màu tím, phát hành năm 1997, với hình tượng Chinthe (sư tử thần thoại) ở mặt trước, mặt sau là cảnh nghệ nhân chạm khắc.", "color": "tím"},
        "100": {"year_of_issue": "1997", "description": "Tờ 100 Kyat có màu xanh dương, phát hành năm 1997, với hình tượng Chinthe ở mặt trước, mặt sau là cảnh chùa Shwedagon.", "color": "xanh dương"},
        "200": {"year_of_issue": "1997", "description": "Tờ 200 Kyat có màu nâu, phát hành năm 1997, với hình tượng Chinthe ở mặt trước, mặt sau là cảnh voi làm việc trong rừng.", "color": "nâu"},
        "500": {"year_of_issue": "1997", "description": "Tờ 500 Kyat có màu cam, phát hành năm 1997, với hình tượng Chinthe ở mặt trước, mặt sau là cảnh thu hoạch lúa.", "color": "cam"},
        "1000": {"year_of_issue": "1997", "description": "Tờ 1000 Kyat có màu xanh lá cây, phát hành năm 1997, với hình tượng Chinthe ở mặt trước, mặt sau là cảnh cầu U Bein.", "color": "xanh lá cây"},
        "5000": {"year_of_issue": "2009", "description": "Tờ 5000 Kyat có màu đỏ, phát hành năm 2009, với hình voi trắng ở mặt trước, mặt sau là cảnh chùa Ananda.", "color": "đỏ"},
        "10000": {"year_of_issue": "2009", "description": "Tờ 10000 Kyat có màu vàng, phát hành năm 2009, với hình voi trắng ở mặt trước, mặt sau là cảnh chùa Mandalay.", "color": "vàng"}
    },
    "Philippines": {
        "1": {"year_of_issue": "1969", "description": "Tờ 1 Peso có màu xanh lục, phát hành năm 1969, với hình anh hùng Jose Rizal ở mặt trước, mặt sau là cảnh nhà thờ Barasoain.", "color": "xanh lục"},
        "5": {"year_of_issue": "2010", "description": "Tờ 5 Peso có màu tím, phát hành năm 2010, với hình Emilio Aguinaldo ở mặt trước, mặt sau là cảnh chiến thắng tại Malolos.", "color": "tím"},
        "20": {"year_of_issue": "2010", "description": "Tờ 20 Peso có màu cam, phát hành năm 2010, với hình Manuel Quezon ở mặt trước, mặt sau là cảnh Banaue Rice Terraces.", "color": "cam"},
        "50": {"year_of_issue": "2010", "description": "Tờ 50 Peso có màu đỏ, phát hành năm 2010, với hình Sergio Osmeña ở mặt trước, mặt sau là cảnh hang động Taal.", "color": "đỏ"},
        "100": {"year_of_issue": "2010", "description": "Tờ 100 Peso có màu tím, phát hành năm 2010, với hình Manuel Roxas ở mặt trước, mặt sau là cảnh núi Mayon.", "color": "tím"},
        "200": {"year_of_issue": "2010", "description": "Tờ 200 Peso có màu xanh lá cây, phát hành năm 2010, với hình Diosdado Macapagal ở mặt trước, mặt sau là cảnh Chocolate Hills.", "color": "xanh lá cây"},
        "500": {"year_of_issue": "2010", "description": "Tờ 500 Peso có màu vàng, phát hành năm 2010, với hình Benigno Aquino Jr. ở mặt trước, mặt sau là cảnh núi Cordillera.", "color": "vàng"},
        "1000": {"year_of_issue": "2010", "description": "Tờ 1000 Peso có màu xanh dương, phát hành năm 2010, với hình Josefa Llanes Escoda ở mặt trước, mặt sau là cảnh đảo Tubbataha.", "color": "xanh dương"}
    },
    "Singapore": {
        "2": {"year_of_issue": "1999", "description": "Tờ 2 Đô la Singapore có màu tím, phát hành năm 1999, với hình Tổng thống Yusof Ishak ở mặt trước, mặt sau là cảnh giáo dục và học sinh.", "color": "tím"},
        "5": {"year_of_issue": "1999", "description": "Tờ 5 Đô la Singapore có màu xanh lá cây, phát hành năm 1999, với hình Tổng thống Yusof Ishak ở mặt trước, mặt sau là cảnh vườn hoa.", "color": "xanh lá cây"},
        "10": {"year_of_issue": "1999", "description": "Tờ 10 Đô la Singapore có màu đỏ, phát hành năm 1999, với hình Tổng thống Yusof Ishak ở mặt trước, mặt sau là cảnh thể thao.", "color": "đỏ"},
        "50": {"year_of_issue": "1999", "description": "Tờ 50 Đô la Singapore có màu xanh dương, phát hành năm 1999, với hình Tổng thống Yusof Ishak ở mặt trước, mặt sau là cảnh nghệ thuật.", "color": "xanh dương"},
        "100": {"year_of_issue": "1999", "description": "Tờ 100 Đô la Singapore có màu cam, phát hành năm 1999, với hình Tổng thống Yusof Ishak ở mặt trước, mặt sau là cảnh hàng hải.", "color": "cam"}
    },
    "Thái Lan": {
        "1": {"year_of_issue": "1946", "description": "Tờ 1 Baht có màu xanh lục, phát hành năm 1946, với hình Vua Rama IX ở mặt trước, mặt sau là cảnh chùa Wat Phra Kaew.", "color": "xanh lục"},
        "2": {"year_of_issue": "1948", "description": "Tờ 2 Baht có màu nâu, phát hành năm 1948, với hình Vua Rama IX ở mặt trước, mặt sau là cảnh nông thôn Thái Lan.", "color": "nâu"},
        "5": {"year_of_issue": "1955", "description": "Tờ 5 Baht có màu tím, phát hành năm 1955, với hình Vua Rama IX ở mặt trước, mặt sau là cảnh cầu Rama VIII.", "color": "tím"},
        "10": {"year_of_issue": "2018", "description": "Tờ 10 Baht có màu nâu, phát hành năm 2018, với hình Vua Rama X ở mặt trước, mặt sau là cảnh chùa Wat Arun.", "color": "nâu"},
        "20": {"year_of_issue": "2018", "description": "Tờ 20 Baht có màu xanh lá cây, phát hành năm 2018, với hình Vua Rama X ở mặt trước, mặt sau là cảnh cầu Bhumibol.", "color": "xanh lá cây"},
        "25": {"year_of_issue": "1996", "description": "Tờ 25 Baht có màu tím, phát hành năm 1996 (tiền kỷ niệm), với hình Vua Rama IX ở mặt trước, mặt sau là cảnh Hoàng cung Thái Lan.", "color": "tím"},
        "50": {"year_of_issue": "2018", "description": "Tờ 50 Baht có màu xanh dương, phát hành năm 2018, với hình Vua Rama X ở mặt trước, mặt sau là cảnh chùa Wat Pho.", "color": "xanh dương"},
        "100": {"year_of_issue": "2018", "description": "Tờ 100 Baht có màu đỏ, phát hành năm 2018, với hình Vua Rama X ở mặt trước, mặt sau là cảnh cung điện Dusit.", "color": "đỏ"},
        "500": {"year_of_issue": "2018", "description": "Tờ 500 Baht có màu tím, phát hành năm 2018, với hình Vua Rama X ở mặt trước, mặt sau là cảnh chùa Wat Benchamabophit.", "color": "tím"},
        "1000": {"year_of_issue": "2018", "description": "Tờ 1000 Baht có màu xám, phát hành năm 2018, với hình Vua Rama X ở mặt trước, mặt sau là cảnh Hoàng cung Thái Lan.", "color": "xám"}
    },
    "Đông Timor": {
        "1": {"year_of_issue": "2002", "description": "Tờ 1 Đô la Mỹ tại Đông Timor có màu xanh lục/xám, phát hành năm 2002, với hình George Washington ở mặt trước, mặt sau là Đại bàng Mỹ.", "color": "xanh lục/xám"},
        "5": {"year_of_issue": "2002", "description": "Tờ 5 Đô la Mỹ tại Đông Timor có màu tím, phát hành năm 2002, với hình Abraham Lincoln ở mặt trước, mặt sau là Đài tưởng niệm Lincoln.", "color": "tím"},
        "10": {"year_of_issue": "2002", "description": "Tờ 10 Đô la Mỹ tại Đông Timor có màu cam, phát hành năm 2002, với hình Alexander Hamilton ở mặt trước, mặt sau là Bộ Tài chính Mỹ.", "color": "cam"},
        "20": {"year_of_issue": "2002", "description": "Tờ 20 Đô la Mỹ tại Đông Timor có màu xanh lá cây, phát hành năm 2002, với hình Andrew Jackson ở mặt trước, mặt sau là Nhà Trắng.", "color": "xanh lá cây"},
        "50": {"year_of_issue": "2002", "description": "Tờ 50 Đô la Mỹ tại Đông Timor có màu hồng, phát hành năm 2002, với hình Ulysses S. Grant ở mặt trước, mặt sau là Điện Capitol.", "color": "hồng"},
        "100": {"year_of_issue": "2002", "description": "Tờ 100 Đô la Mỹ tại Đông Timor có màu xanh lục, phát hành năm 2002, với hình Benjamin Franklin ở mặt trước, mặt sau là Tuyên ngôn Độc lập.", "color": "xanh lục"}
    },
    "Việt Nam": {
        "1": {"year_of_issue": "1985", "description": "Tờ 1 Đồng Việt Nam có màu xanh lá cây, được phát hành năm 1985, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là cánh đồng lúa nước, biểu tượng của nền nông nghiệp Việt Nam.", "color": "xanh lá cây"},
        "5": {"year_of_issue": "1985", "description": "Tờ 5 Đồng Việt Nam có màu đỏ cam, được phát hành năm 1985, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là cảnh thu hoạch lúa với nông dân trên đồng ruộng.", "color": "đỏ cam"},
        "10": {"year_of_issue": "1985", "description": "Tờ 10 Đồng Việt Nam có màu xanh dương, được phát hành năm 1985, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Cảng Hải Phòng, biểu tượng của sự phát triển thương mại và giao thương hàng hải.", "color": "xanh dương"},
        "20": {"year_of_issue": "1985", "description": "Tờ 20 Đồng Việt Nam có màu nâu tím, được phát hành năm 1985, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Nhà máy Thủy điện Hòa Bình, công trình thủy điện lớn nhất Việt Nam thời điểm đó.", "color": "nâu tím"},
        "50": {"year_of_issue": "1985", "description": "Tờ 50 Đồng Việt Nam có màu xanh lục, được phát hành năm 1985, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là cảnh khai thác than tại Quảng Ninh, thể hiện vai trò của ngành công nghiệp khai khoáng.", "color": "xanh lục"},
        "100": {"year_of_issue": "1985", "description": "Tờ 100 Đồng Việt Nam có màu đỏ nâu, được phát hành năm 1985, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Nhà máy Thép Thái Nguyên, biểu tượng của ngành công nghiệp nặng.", "color": "đỏ nâu"},
        "200": {"year_of_issue": "1987", "description": "Tờ 200 Đồng Việt Nam có màu nâu vàng, được phát hành năm 1987, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Nhà máy Cơ khí Hà Nội, thể hiện sự phát triển công nghiệp.", "color": "nâu vàng"},
        "500": {"year_of_issue": "1989", "description": "Tờ 500 Đồng Việt Nam có màu nâu đỏ, được phát hành năm 1989, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Nhà máy dệt Nam Định, biểu tượng của ngành công nghiệp dệt.", "color": "nâu đỏ"},
        "1000": {"year_of_issue": "1988", "description": "Tờ 1000 Đồng Việt Nam có màu nâu vàng, được phát hành năm 1988, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là phong cảnh núi rừng Tây Nguyên với voi.", "color": "nâu vàng"},
        "2000": {"year_of_issue": "1988", "description": "Tờ 2000 Đồng Việt Nam có màu xám xanh, được phát hành năm 1988, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Nhà máy dệt Nam Định, biểu tượng của sự phát triển công nghiệp dệt may.", "color": "xám xanh"},
        "5000": {"year_of_issue": "1991", "description": "Tờ 5000 Đồng Việt Nam có màu xanh dương, được phát hành năm 1991, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Nhà máy thủy điện Trị An.", "color": "xanh dương"},
        "10000": {"year_of_issue": "2006", "description": "Tờ 10000 Đồng Việt Nam có màu nâu vàng, được phát hành năm 2006, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Giàn khoan dầu khí ngoài khơi Việt Nam.", "color": "nâu vàng"},
        "20000": {"year_of_issue": "2006", "description": "Tờ 20000 Đồng Việt Nam có màu xanh dương, được phát hành năm 2006, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Khuê Văn Các – biểu tượng của Văn Miếu Quốc Tử Giám.", "color": "xanh dương"},
        "50000": {"year_of_issue": "2004", "description": "Tờ 50000 Đồng Việt Nam có màu hồng tím, được phát hành năm 2004, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Khu di tích Nguyễn Sinh Sắc - Nguyễn Tất Thành tại Nghệ An.", "color": "hồng tím"},
        "100000": {"year_of_issue": "2000", "description": "Tờ 100000 Đồng Việt Nam có màu xanh lá cây, được phát hành năm 2000, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Văn Miếu - Quốc Tử Giám, biểu tượng của truyền thống hiếu học.", "color": "xanh lá cây"},
        "200000": {"year_of_issue": "2006", "description": "Tờ 200000 Đồng Việt Nam có màu đỏ cam, được phát hành năm 2006, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Vịnh Hạ Long, di sản thiên nhiên thế giới.", "color": "đỏ cam"},
        "500000": {"year_of_issue": "2003", "description": "Tờ 500000 Đồng Việt Nam có màu xanh lục, được phát hành năm 2003, với chân dung Chủ tịch Hồ Chí Minh ở mặt trước, mặt sau là Ngôi nhà sàn của Chủ tịch Hồ Chí Minh tại Nam Đàn, Nghệ An.", "color": "xanh lục"}
    }
}

def get_db_connection():
    try:
        connection = connect(
            host=mysql_host,
            user=mysql_user,
            password=mysql_password,
            database=mysql_database
        )
        return connection
    except Error as e:
        logger.error(f"Lỗi khi kết nối MySQL: {e}")
        return None

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

            image_base64, detections = process_image_with_yolo(image)

            detection_info = {}
            money_details = {}
            additional_info = ""

            if detections:
                detection = detections[0]
                predicted_class = f"{detection['country']}: {detection['class_name']}"
                confidence = detection["confidence"]
                logger.info(f"Nhận diện thành công: {predicted_class} với độ tin cậy {confidence}")

                predicted_class = predicted_class.replace("Ngàn", "").replace("Ngan", "").replace(".", "")
                if "_" in predicted_class:
                    predicted_class = predicted_class.split("_")[0]
                country, denomination = predicted_class.split(": ")
                denomination = denomination.strip()

                detection_info = {
                    "denomination": predicted_class,
                    "confidence": f"{confidence:.2f}"
                }

                if country in MONEY_DATA and denomination in MONEY_DATA[country]:
                    money_details = MONEY_DATA[country][denomination]
                    additional_info = "Thông tin được lấy từ cơ sở dữ liệu tĩnh."
                else:
                    money_details = {
                        "year_of_issue": "Không tìm thấy",
                        "description": "Không có thông tin chi tiết do không có trong cơ sở dữ liệu.",
                        "color": "N/A"
                    }
                    additional_info = "Không tìm thấy thông tin trong cơ sở dữ liệu tĩnh."
            else:
                logger.info("Không nhận diện được tờ tiền.")
                detection_info = {
                    "denomination": "Không nhận diện được",
                    "confidence": "N/A"
                }
                money_details = {
                    "year_of_issue": "N/A",
                    "description": "Không có thông tin do không nhận diện được tờ tiền.",
                    "color": "N/A"
                }
                additional_info = "Không nhận diện được tờ tiền."

            money_details.setdefault("year_of_issue", "N/A")
            money_details.setdefault("description", "Không có thông tin chi tiết.")
            money_details.setdefault("color", "N/A")

            return {
                "image": f"data:image/jpeg;base64,{image_base64}",
                "detection_info": detection_info,
                "money_details": money_details,
                "additional_info": additional_info
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

            image_base64, detections = process_image_with_yolo(frame)

            detection_info = {}
            money_details = {}
            additional_info = ""

            if detections:
                detection = detections[0]
                predicted_class = f"{detection['country']}: {detection['class_name']}"
                logger.info(f"Nhận diện thành công từ webcam: {predicted_class}")

                predicted_class = predicted_class.replace("Ngàn", "").replace("Ngan", "").replace(".", "")
                if "_" in predicted_class:
                    predicted_class = predicted_class.split("_")[0]
                country, denomination = predicted_class.split(": ")
                denomination = denomination.strip()

                detection_info = {
                    "denomination": predicted_class,
                    "confidence": f"{detection['confidence']:.2f}"
                }

                if country in MONEY_DATA and denomination in MONEY_DATA[country]:
                    money_details = MONEY_DATA[country][denomination]
                    additional_info = "Thông tin được lấy từ cơ sở dữ liệu tĩnh."
                else:
                    money_details = {
                        "year_of_issue": "Không tìm thấy",
                        "description": "Không có thông tin chi tiết do không có trong cơ sở dữ liệu.",
                        "color": "N/A"
                    }
                    additional_info = "Không tìm thấy thông tin trong cơ sở dữ liệu tĩnh."
            else:
                logger.info("Không nhận diện được tờ tiền từ webcam.")
                detection_info = {
                    "denomination": "Không nhận diện được",
                    "confidence": "N/A"
                }
                money_details = {
                    "year_of_issue": "N/A",
                    "description": "Không có thông tin do không nhận diện được tờ tiền.",
                    "color": "N/A"
                }
                additional_info = "Không nhận diện được tờ tiền."

            cap.release()
            return JSONResponse(content={
                "image": f"data:image/jpeg;base64,{image_base64}",
                "detection_info": detection_info,
                "money_details": money_details,
                "additional_info": additional_info
            })
        except Exception as e:
            cap.release()
            logger.error(f"Lỗi khi xử lý webcam: {e}")
            raise HTTPException(status_code=500, detail=f"Lỗi khi xử lý webcam: {str(e)}")

    @app.post("/chat")
    @limiter.limit("5/minute")
    async def chat(request: Request):
        try:
            data = await request.json()
            question = data.get("question")
            if not question:
                raise HTTPException(status_code=400, detail="Vui lòng cung cấp câu hỏi hợp lệ!")

            logger.info(f"Nhận được câu hỏi: {question[:100]}")

            # Lấy cấu hình từ chat_api
            connection = get_db_connection()
            if not connection:
                raise HTTPException(status_code=500, detail="Không thể kết nối đến cơ sở dữ liệu!")
            try:
                cursor = connection.cursor(dictionary=True)
                cursor.execute("SELECT model_name, api_key FROM chat_api ORDER BY updated_at DESC LIMIT 1")
                llm_config = cursor.fetchone()
                if not llm_config:
                    raise HTTPException(status_code=500, detail="Không tìm thấy cấu hình LLM!")
                model_name = llm_config["model_name"]
                api_key = llm_config["api_key"]
                logger.info(f"Đọc cấu hình LLM: model={model_name}, api_key={api_key[:4]}****")
            finally:
                connection.close()

            if model_name not in SUPPORTED_MODELS:
                raise HTTPException(status_code=400, detail=f"Mô hình {model_name} không được hỗ trợ. Hỗ trợ: {list(SUPPORTED_MODELS.keys())}")

            # Kiểm tra MONEY_DATA
            question_lower = question.lower().strip()
            for country, denominations in MONEY_DATA.items():
                if country.lower() in question_lower:
                    for denomination, details in denominations.items():
                        if denomination in question_lower or any(word in question_lower for word in details["description"].lower().split()):
                            result = f"Tờ {denomination} của {country} có màu {details['color']}, được phát hành năm {details['year_of_issue']}. {details['description']}"
                            logger.info(f"Trả lời từ MONEY_DATA: {result[:100]}...")
                            return {"response": result, "source": "MONEY_DATA"}

            # Khởi tạo chatbot
            try:
                llm = load_llm(model_name, api_key)
                # Nếu câu hỏi không liên quan đến tiền tệ, tìm kiếm web
                if not any(country.lower() in question_lower for country in MONEY_DATA):
                    web_contents = fetch_top_5_web_content(question)
                    if model_name == "gemini-1.5-pro":
                        result = process_with_gemini(web_contents, question, llm)
                    else:  # openai-gpt-4
                        result = process_with_openai(web_contents, question, llm)
                else:
                    # Trả lời trực tiếp bằng LLM nếu liên quan đến tiền tệ
                    if model_name == "gemini-1.5-pro":
                        response = llm.invoke(question)
                        result = response.content.strip()
                    else:  # openai-gpt-4
                        response = llm.chat.completions.create(
                            model="gpt-3.5-turbo",
                            messages=[{"role": "user", "content": question}],
                            max_tokens=1024,
                            temperature=0.01
                        )
                        result = response.choices[0].message.content.strip()
            except ValueError as e:
                logger.error(f"Lỗi khi gọi LLM: {str(e)}")
                if "API key không hợp lệ" in str(e) or "API_KEY_INVALID" in str(e):
                    raise HTTPException(
                        status_code=422,
                        detail=f"API key không hợp lệ cho {model_name}. Vui lòng kiểm tra API key trong cấu hình."
                    )
                if "Quota hoặc quyền truy cập" in str(e):
                    raise HTTPException(
                        status_code=403,
                        detail=f"Quota hoặc quyền truy cập bị từ chối cho {model_name}. Vui lòng kiểm tra tài khoản Google."
                    )
                raise HTTPException(status_code=500, detail=f"Lỗi khi gọi LLM: {str(e)}")
            except Exception as e:
                logger.error(f"Lỗi không xác định khi gọi LLM: {str(e)}")
                if "429" in str(e) or "insufficient_quota" in str(e):
                    raise HTTPException(
                        status_code=429,
                        detail={
                            "error": {
                                "message": f"API Key đã hết quota. Vui lòng kiểm tra tài khoản {'Google' if model_name == 'gemini-1.5-pro' else 'OpenAI'}.",
                                "type": "insufficient_quota",
                                "param": None,
                                "code": "insufficient_quota"
                            }
                        }
                    )
                raise HTTPException(status_code=500, detail=f"Lỗi không xác định khi gọi LLM: {str(e)}")

            if not result or len(result) < 10:
                result = "Tôi không có đủ thông tin để trả lời câu hỏi này."

            logger.info(f"Phản hồi từ {model_name}: {result[:100]}...")
            return {"response": result, "source": model_name}
        except HTTPException as e:
            raise e
        except Exception as e:
            logger.error(f"Lỗi khi xử lý câu hỏi: {str(e)}")
            raise HTTPException(status_code=500, detail=f"Lỗi khi xử lý câu hỏi: {str(e)}")

    @app.post("/llm-update")
    async def update_llm(request: Request, body: LLMUpdateRequest = Body(...)):
        try:
            content_type = request.headers.get("Content-Type", "unknown")
            logger.info(f"Nhận yêu cầu /llm-update với Content-Type: {content_type}")
            logger.info(f"JSON body: {json.dumps(body.dict(), ensure_ascii=False)}")

            model_name = body.model_name
            api_key = body.api_key

            if content_type != "application/json":
                raise HTTPException(status_code=422, detail="Content-Type phải là application/json")

            if model_name not in SUPPORTED_MODELS:
                raise HTTPException(
                    status_code=422,
                    detail=f"Mô hình {model_name} không được hỗ trợ. Hỗ trợ: {list(SUPPORTED_MODELS.keys())}"
                )

            try:
                llm = load_llm(model_name, api_key)
            except ValueError as e:
                logger.error(f"Lỗi khi xác thực API key: {str(e)}")
                if "API key không hợp lệ" in str(e) or "API_KEY_INVALID" in str(e):
                    raise HTTPException(
                        status_code=422,
                        detail=f"API Key không hợp lệ cho {model_name}. Vui lòng kiểm tra API key."
                    )
                if "Quota hoặc quyền truy cập" in str(e):
                    raise HTTPException(
                        status_code=403,
                        detail=f"Quota hoặc quyền truy cập bị từ chối cho {model_name}. Vui lòng kiểm tra tài khoản Google."
                    )
                if "insufficient_quota" in str(e):
                    raise HTTPException(
                        status_code=429,
                        detail={
                            "error": {
                                "message": f"API Key đã hết quota. Vui lòng kiểm tra tài khoản {'Google' if model_name == 'gemini-1.5-pro' else 'OpenAI'}.",
                                "type": "insufficient_quota",
                                "param": None,
                                "code": "insufficient_quota"
                            }
                        }
                    )
                raise HTTPException(status_code=422, detail=f"Lỗi khi xác thực API key: {str(e)}")

            connection = get_db_connection()
            if not connection:
                logger.error("Không thể kết nối đến cơ sở dữ liệu MySQL")
                raise HTTPException(status_code=500, detail="Không thể kết nối đến cơ sở dữ liệu!")
            try:
                cursor = connection.cursor()
                cursor.execute("SELECT COUNT(*) FROM chat_api")
                count = cursor.fetchone()[0]
                if count > 0:
                    cursor.execute(
                        "UPDATE chat_api SET model_name = %s, api_key = %s, updated_at = CURRENT_TIMESTAMP WHERE id = (SELECT id FROM chat_api ORDER BY updated_at DESC LIMIT 1)",
                        (model_name, api_key)
                    )
                else:
                    cursor.execute(
                        "INSERT INTO chat_api (model_name, api_key, created_at) VALUES (%s, %s, CURRENT_TIMESTAMP)",
                        (model_name, api_key)
                    )
                connection.commit()
                logger.info(f"Đã cập nhật LLM: model={model_name}, api_key={api_key[:4]}****")
            except Error as e:
                logger.error(f"Lỗi khi lưu vào chat_api: {str(e)}")
                raise HTTPException(status_code=500, detail=f"Lỗi khi lưu cấu hình: {str(e)}")
            finally:
                if connection.is_connected():
                    cursor.close()
                    connection.close()

            return {"message": f"Đã cập nhật thành công mô hình {model_name}"}
        except HTTPException as e:
            raise e
        except Exception as e:
            logger.error(f"Lỗi không xác định trong /llm-update: {str(e)}")
            raise HTTPException(status_code=500, detail=f"Lỗi không xác định: {str(e)}")

    @app.post("/login")
    async def login_route(name: str = Body(...), password: str = Body(...)):
        return await login(name, password)

    @app.post("/signup")
    async def signup_route(name: str = Body(...), email: str = Body(...), password: str = Body(...)):
        return await signup(name, email, password)

    @app.post("/forgot_password")
    async def forgot_password_route(email: str = Body(...)):
        return await forgot_password(email)

    @app.post("/deposit")
    async def deposit(amount: float = Body(...), request: Request = None):
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