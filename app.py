from flask import Flask, render_template, request, make_response
from wtforms import Form, StringField, FileField, validators  # Import WTForms for form validation
import pusher

import mysql.connector
import datetime
import pytz

app = Flask(__name__)

# Database connection details (replace with your actual configuration)
con = mysql.connector.connect(
    host="185.232.14.52",
    database="u760464709_tst_sep",
    user="u760464709_tst_sep_usr",
    password="dJ0CIAFF="
)


# Connect to database (replace with your connection logic)
def connect_to_database():
    # Replace this with your actual connection code
    pass

# Form class for student payment information
class PagoCursoForm(Form):
    telefono = StringField("Teléfono:", validators=[DataRequired(), Length(min=10, max=10), Regexp(r"^\d+$")])
    comprobante = FileField("Comprobante:", validators=[DataRequired(), AllowedExtensions(["jpg", "jpeg", "png", "pdf"])])

@app.route("/")
def index():
    form = PagoCursoForm()  # Create an empty form instance
    return render_template("pago_curso.html", form=form)  # Pass form to the template

@app.route("/pago_curso", methods=["POST"])
def pago_curso():
    form = PagoCursoForm()  # Create a form instance with submitted data
    if form.validate_on_submit():
        # Get form data
        telefono = form.telefono.data
        comprobante = form.comprobante.data  # File object
        # Save data to database (replace with your logic)
        # ...
        # Send confirmation or error message
        message = "Pago recibido exitosamente!"
        return render_template("pago_curso.html", form=form, message=message)
    else:
        # Display validation errors
        return render_template("pago_curso.html", form=form)

# ... other routes from your existing code

if __name__ == "__main__":
    app.run(debug=True)
