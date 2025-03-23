from langchain_community.embeddings import OpenAIEmbeddings
from llama_index.embeddings.huggingface import HuggingFaceEmbedding
from app.config import settings


class ServiceManager:
    """
    Quản lý các dịch vụ liên quan đến embeddings.
    """

    def __init__(self) -> None:
        """
        Khởi tạo ServiceManager.
        """
        pass

    def get_embedding_model(self, embedding_model_name: str):
        """
        Trả về mô hình embeddings tương ứng dựa trên tên mô hình.

        Args:
            embedding_model_name (str): Tên của mô hình embeddings.

        Returns:
            OpenAIEmbeddings | HuggingFaceEmbedding | None: Đối tượng embeddings tương ứng.
        """
        embeddings = None
        if embedding_model_name == "openai":
            embeddings = OpenAIEmbeddings(openai_api_key=settings.KEY_API_GPT)
        elif embedding_model_name == "loco":
            embeddings = HuggingFaceEmbedding(model_name="BAAI/bge-small-en")  # Thay model_name bằng model của Loco
        return embeddings
