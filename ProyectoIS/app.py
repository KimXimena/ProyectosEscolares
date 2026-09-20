from flask import Flask, request, render_template, redirect, flash, session, jsonify
import pandas as pd
import pyodbc
import os
import json
from werkzeug.utils import secure_filename

app = Flask(__name__)
app.secret_key = 'Ximena05R'

# Cargar configuración
app.config.from_object('config.Config')
app.config['UPLOAD_FOLDER'] = 'uploads'

# Crear directorio de uploads si no existe
os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)

# Función para obtener conexión a la base de datos
def get_db_connection():
    try:
        conn = pyodbc.connect(app.config['SQL_CONN_STR'])
        return conn
    except Exception as e:
        print(f"Error de conexión a la base de datos: {e}")
        return None

# Página de inicio mejorada
@app.route('/')
def index():
    return render_template('index.html', title="Sistema de Encuestas de Admisión")

# Ruta con formulario HTML para importar encuesta (MEJORADA)
@app.route('/importar_encuesta', methods=['GET', 'POST'])
def importar_encuesta():
    if request.method == 'GET':
        return render_template('importar.html')
    
    archivo = request.files.get('file')
    if archivo and archivo.filename.endswith('.xlsx'):
        filename = secure_filename(archivo.filename)
        ruta = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        archivo.save(ruta)
        
        try:
            # Leer el archivo Excel
            df = pd.read_excel(ruta, sheet_name='Encuesta Reingreso 2')
            mensaje = importar_desde_excel(df)
            flash(mensaje, 'success')
        except Exception as e:
            flash(f"No se pudo leer el archivo Excel: {e}", 'error')
        
        return redirect('/importar_encuesta')
    else:
        flash("Archivo inválido. Solo se aceptan archivos .xlsx", 'error')
        return redirect('/importar_encuesta')

# Función mejorada para importar desde Excel
def importar_desde_excel(df):
    conn = None
    cursor = None
    try:
        conn = get_db_connection()
        if not conn:
            return "Error: No se pudo conectar a la base de datos"
            
        cursor = conn.cursor()
        
        # Primero, crear la encuesta principal
        cursor.execute("INSERT INTO Surveys (title, description, created_by) OUTPUT INSERTED.id VALUES (?, ?, ?)", 
                      "Encuesta de Reingreso", "Encuesta para estudiantes de reingreso", 1)
        survey_id = cursor.fetchone()[0]
        
        current_section = None
        current_section_id = None
        question_order = 0
        
        for index, row in df.iterrows():
            # Saltar filas vacías o de encabezado
            if pd.isna(row.iloc[0]) and pd.isna(row.iloc[1]):
                continue
                
            id_cell = row.iloc[0]  # Columna A
            pregunta_cell = row.iloc[1]  # Columna B
            respuestas_cell = row.iloc[2] if len(row) > 2 else None  # Columna C
            
            # Detectar secciones (filas que tienen texto en columna A pero no en B)
            if (not pd.isna(id_cell) and 
                pd.isna(pregunta_cell) and 
                isinstance(id_cell, str) and 
                not id_cell.isdigit()):
                
                current_section = str(id_cell).strip()
                cursor.execute(
                    "INSERT INTO SurveySections (survey_id, name, section_order) OUTPUT INSERTED.id VALUES (?, ?, ?)",
                    survey_id, current_section, index
                )
                current_section_id = cursor.fetchone()[0]
                question_order = 0
                continue
            
            # Detectar preguntas (filas con número en columna A y texto en columna B)
            if (not pd.isna(id_cell) and 
                not pd.isna(pregunta_cell) and 
                (isinstance(id_cell, (int, float)) or 
                 (isinstance(id_cell, str) and id_cell.isdigit()))):
                
                question_order += 1
                question_number = str(int(id_cell)) if isinstance(id_cell, (int, float)) else str(id_cell)
                question_text = str(pregunta_cell).strip()
                
                # Determinar tipo de pregunta basado en las respuestas
                question_type = "text"  # por defecto
                options = []
                
                if not pd.isna(respuestas_cell):
                    options = [str(respuestas_cell).strip()]
                    question_type = "single_choice"
                
                # Buscar más opciones en las filas siguientes
                next_index = index + 1
                while (next_index < len(df) and 
                       pd.isna(df.iloc[next_index].iloc[0]) and 
                       pd.isna(df.iloc[next_index].iloc[1]) and 
                       not pd.isna(df.iloc[next_index].iloc[2])):
                    
                    option_text = str(df.iloc[next_index].iloc[2]).strip()
                    if option_text and option_text not in options:
                        options.append(option_text)
                    next_index += 1
                
                # Si hay múltiples opciones, es choice
                if len(options) > 1:
                    question_type = "single_choice"
                elif len(options) == 1 and any(keyword in options[0].lower() for keyword in ['si', 'no', 'hombre', 'mujer']):
                    question_type = "single_choice"
                
                # Insertar pregunta
                cursor.execute(
                    """INSERT INTO Questions (section_id, question_number, question_text, question_type, options_json, question_order, is_required) 
                    OUTPUT INSERTED.id VALUES (?, ?, ?, ?, ?, ?, ?)""",
                    current_section_id, question_number, question_text, question_type, 
                    json.dumps(options) if options else None, question_order, True
                )
                
                conn.commit()
        
        return f"Encuesta importada correctamente. ID: {survey_id}, Preguntas procesadas: {question_order}"
        
    except Exception as e:
        if conn:
            conn.rollback()
        return f"Error al importar encuesta: {str(e)}"
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()

# Nueva ruta para ver las encuestas importadas
@app.route('/encuestas')
def ver_encuestas():
    conn = get_db_connection()
    if not conn:
        return "Error de conexión a la base de datos"
    
    try:
        cursor = conn.cursor()
        
        # Obtener todas las encuestas
        cursor.execute("""
            SELECT s.id, s.title, s.description, s.created_at, 
                   COUNT(DISTINCT ss.id) as num_sections,
                   COUNT(DISTINCT q.id) as num_questions
            FROM Surveys s
            LEFT JOIN SurveySections ss ON s.id = ss.survey_id
            LEFT JOIN Questions q ON ss.id = q.section_id
            GROUP BY s.id, s.title, s.description, s.created_at
            ORDER BY s.created_at DESC
        """)
        
        encuestas = []
        for row in cursor.fetchall():
            encuestas.append({
                'id': row[0],
                'title': row[1],
                'description': row[2],
                'created_at': row[3],
                'num_sections': row[4],
                'num_questions': row[5]
            })
        
        return render_template('encuestas.html', encuestas=encuestas)
        
    except Exception as e:
        return f"Error al obtener encuestas: {str(e)}"
    finally:
        conn.close()

# Ruta para ver detalles de una encuesta
@app.route('/encuesta/<int:encuesta_id>')
def ver_encuesta(encuesta_id):
    conn = get_db_connection()
    if not conn:
        return "Error de conexión a la base de datos"
    
    try:
        cursor = conn.cursor()
        
        # Obtener información de la encuesta
        cursor.execute("SELECT title, description FROM Surveys WHERE id = ?", encuesta_id)
        encuesta = cursor.fetchone()
        
        if not encuesta:
            return "Encuesta no encontrada"
        
        # Obtener secciones y preguntas
        cursor.execute("""
            SELECT ss.id, ss.name, ss.section_order,
                   q.id, q.question_number, q.question_text, q.question_type, q.options_json
            FROM SurveySections ss
            LEFT JOIN Questions q ON ss.id = q.section_id
            WHERE ss.survey_id = ?
            ORDER BY ss.section_order, q.question_order
        """, encuesta_id)
        
        secciones = {}
        for row in cursor.fetchall():
            section_id, section_name, section_order, q_id, q_number, q_text, q_type, q_options = row
            
            if section_id not in secciones:
                secciones[section_id] = {
                    'name': section_name,
                    'order': section_order,
                    'preguntas': []
                }
            
            if q_id:  # Si hay pregunta
                secciones[section_id]['preguntas'].append({
                    'id': q_id,
                    'number': q_number,
                    'text': q_text,
                    'type': q_type,
                    'options': json.loads(q_options) if q_options else []
                })
        
        return render_template('detalle_encuesta.html', 
                             encuesta={'id': encuesta_id, 'title': encuesta[0], 'description': encuesta[1]},
                             secciones=secciones)
        
    except Exception as e:
        return f"Error al obtener encuesta: {str(e)}"
    finally:
        conn.close()

def crear_tablas():
    raise NotImplementedError

if __name__ == '__main__':
    # Crear tablas si no existen
    crear_tablas()
    app.run(debug=True)

def crear_tablas_si_no_existen():
    """Crear las tablas necesarias si no existen"""
    conn = get_db_connection()
    if not conn:
        print("No se pudo conectar a la base de datos para crear tablas")
        return
    
    try:
        cursor = conn.cursor()
        
        # Tabla de Surveys
        cursor.execute("""
            IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='Surveys' AND xtype='U')
            CREATE TABLE Surveys (
                id INT IDENTITY(1,1) PRIMARY KEY,
                title NVARCHAR(255) NOT NULL,
                description NVARCHAR(MAX),
                created_by INT DEFAULT 1,
                created_at DATETIME2 DEFAULT GETDATE(),
                is_active BIT DEFAULT 1
            )
        """)
        
        # Tabla de SurveySections
        cursor.execute("""
            IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='SurveySections' AND xtype='U')
            CREATE TABLE SurveySections (
                id INT IDENTITY(1,1) PRIMARY KEY,
                survey_id INT FOREIGN KEY REFERENCES Surveys(id),
                name NVARCHAR(255) NOT NULL,
                section_order INT NOT NULL
            )
        """)
        
        # Tabla de Questions
        cursor.execute("""
            IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='Questions' AND xtype='U')
            CREATE TABLE Questions (
                id INT IDENTITY(1,1) PRIMARY KEY,
                section_id INT FOREIGN KEY REFERENCES SurveySections(id),
                question_number NVARCHAR(10),
                question_text NVARCHAR(MAX) NOT NULL,
                question_type NVARCHAR(50) NOT NULL,
                options_json NVARCHAR(MAX),
                is_required BIT DEFAULT 1,
                question_order INT NOT NULL
            )
        """)
        
        conn.commit()
        print("Tablas verificadas/creadas correctamente")
        
    except Exception as e:
        print(f"Error al crear tablas: {e}")
    finally:
        conn.close()