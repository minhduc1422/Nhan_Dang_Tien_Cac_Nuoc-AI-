import mysql.connector
from mysql.connector import Error, pooling
from config import mysql_host, mysql_user, mysql_password, mysql_database, logger

# MySQL connection pool
mysql_pool = pooling.MySQLConnectionPool(
    pool_name="mypool",
    pool_size=5,
    host=mysql_host,
    user=mysql_user,
    password=mysql_password,
    database=mysql_database
)

def get_db_connection():
    try:
        connection = mysql_pool.get_connection()
        if connection.is_connected():
            logger.info("Kết nối MySQL thành công!")
            return connection
    except Error as e:
        logger.error(f"Lỗi khi kết nối MySQL: {e}")
        return None