import os

class Config:
    SECRET_KEY = os.environ.get('SECRET_KEY') or 'Ximena05R'
    
    # Configuración de SQL Server
    SQL_SERVER = os.environ.get('SQL_SERVER') or 'localhost'
    SQL_DATABASE = os.environ.get('SQL_DATABASE') or 'SurveyAdmissionSystem'
    SQL_USERNAME = os.environ.get('SQL_USERNAME') or 'sa'
    SQL_PASSWORD = os.environ.get('SQL_PASSWORD') or 'tu_password'
    
    # Cadena de conexión para pyodbc
    SQL_CONN_STR = f'DRIVER={{ODBC Driver 17 for SQL Server}};SERVER={SQL_SERVER};DATABASE={SQL_DATABASE};UID={SQL_USERNAME};PWD={SQL_PASSWORD}'
    
    # Configuración de uploads
    UPLOAD_FOLDER = 'uploads'
    MAX_CONTENT_LENGTH = 16 * 1024 * 1024  # 16MB max-limit