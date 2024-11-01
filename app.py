from datetime import datetime

class PagoCurso:
    def __init__(self, telefono, ruta_comprobante):
        self.telefono = telefono
        self.ruta_comprobante = ruta_comprobante
        self.fecha_registro = datetime.now()
        self.estado = 'PENDIENTE'

        from flask_wtf import FlaskForm
from wtforms import StringField, FileField, validators
from wtforms.validators import DataRequired, Length, Regexp

class PagoCursoForm(FlaskForm):
    telefono = StringField(
        "Teléfono:",
        validators=[
            DataRequired(message="El teléfono es obligatorio"),
            Length(min=10, max=10, message="El teléfono debe tener 10 dígitos"),
            Regexp(r"^\d+$", message="Solo se permiten números")
        ]
    )
    comprobante = FileField(
        "Comprobante:",
        validators=[
            DataRequired(message="Debe adjuntar un comprobante"),
            FileAllowed(['jpg', 'jpeg', 'png', 'pdf'], 
                       message="Solo se permiten archivos JPG, JPEG, PNG y PDF")
        ]
    )

    from flask import Flask, render_template, request, flash
from werkzeug.utils import secure_filename
import os

class PagoCursoController:
    def __init__(self, db_connection):
        self.db = db_connection
        self.UPLOAD_FOLDER = 'uploads/comprobantes'
        
    def procesar_pago(self, form):
        if form.validate_on_submit():
            try:
                # Guardar archivo
                filename = secure_filename(form.comprobante.data.filename)
                ruta_archivo = os.path.join(self.UPLOAD_FOLDER, filename)
                form.comprobante.data.save(ruta_archivo)
                
                # Crear registro
                pago = PagoCurso(
                    telefono=form.telefono.data,
                    ruta_comprobante=ruta_archivo
                )
                
                # Guardar en base de datos
                self.guardar_pago(pago)
                
                return True, "Pago registrado exitosamente"
            except Exception as e:
                return False, f"Error al procesar el pago: {str(e)}"
        return False, "Datos inválidos"
    
    def guardar_pago(self, pago):
        # Implementación de guardado en base de datos
        query = """
        INSERT INTO pagos_curso (telefono, ruta_comprobante, fecha_registro, estado)
        VALUES (%s, %s, %s, %s)
        """
        values = (pago.telefono, pago.ruta_comprobante, 
                 pago.fecha_registro, pago.estado)
        # Ejecutar query...
        app = Flask(__name__)
app.config['SECRET_KEY'] = 'clave-secreta-aqui'

@app.route("/")
def index():
    form = PagoCursoForm()
    return render_template("pago_curso.html", form=form)

@app.route("/pago_curso", methods=["POST"])
def pago_curso():
    form = PagoCursoForm()
    controller = PagoCursoController(db_connection)
    
    success, message = controller.procesar_pago(form)
    
    if success:
        flash(message, 'success')
    else:
        flash(message, 'error')
        
    return render_template("pago_curso.html", form=form)
