# import uvicorn
# from fastapi.middleware.cors import CORSMiddleware
# from app_setup import create_app
# from routes import register_routes
# from slowapi import Limiter
# from slowapi.util import get_remote_address

# if __name__ == "__main__":
#     app = create_app()
#     limiter = Limiter(key_func=get_remote_address)
#     register_routes(app, limiter)

#     app.add_middleware(
#     CORSMiddleware,
#     allow_origins=["http://127.0.0.1:8000", "http://localhost:8000"],
#     allow_credentials=True,
#     allow_methods=["*"],
#     allow_headers=["*"],
#     ) 
# uvicorn.run(app, host="0.0.0.0", port=55015)


from fastapi.middleware.cors import CORSMiddleware
from app_setup import create_app
from routes import register_routes
from slowapi import Limiter
from slowapi.util import get_remote_address

app = create_app()
limiter = Limiter(key_func=get_remote_address)
register_routes(app, limiter)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://127.0.0.1:8000", "http://localhost:8000"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)