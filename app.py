from flask import Flask, render_template, request, jsonify, make_response
import pusher
import mysql.connector

# Configuración de la base de datos
con = mysql.connector.connect(
    host="185.232.14.52",
    database="u760464709_tst_sep",
    user="u760464709_tst_sep_usr",
    password="dJ0CIAFF="
)


# Inicializar la aplicación Flask
app = Flask(__name__)

# Configurar Pusher

pusher_client = pusher.Pusher(
  app_id='1889312',
  key='5918e984cc31802c0cbb',
  secret='d6fa4a63c867604e0007',
  cluster='us2',
  ssl=True
)

pusher_client.trigger('my-channel', 'my-event', {'message': 'hello world'})
)

def notificarActualizacionTelefonoArchivo():
    pusher_client.trigger("CanalPago_curso", "pago-curso", {})

# Página principal
@app.route("/")
def index():
     #return render_template("Formulario.html")
      return render_template("Pago-Curso.html")

# Ruta para buscar pagos en la base de datos
@app.route("/buscar")
def buscar():
    if not con.is_connected():
        con.reconnect()

    cursor = con.cursor()
    cursor.execute("""
    SELECT Id_Curso_Pago, Telefono, Archivo FROM tst0_cursos_pagos 
    ORDER BY Id_Curso_Pago DESC
    LIMIT 10 OFFSET 0
    """)
    
    registros = cursor.fetchall()
    con.close()

    return make_response(jsonify(registros))

# Ruta para registrar un nuevo pago y activar el evento Pusher
@app.route("/registrar", methods=["POST"])
def registrar():
    if not con.is_connected():
        con.reconnect()

    id = request.form.get("id")
    Telefono = request.form.get("Telefono")
    Archivo = request.form.get("Archivo")
    
    cursor = con.cursor()

    if id:
        sql = """
        UPDATE tst0_cursos_pagos SET
        Telefono = %s,
        Archivo = %s
        WHERE Id_Curso_Pago = %s
        """
        val = (Telefono, Archivo, id)
    else:
        sql = """
        INSERT INTO tst0_cursos_pagos (Telefono, Archivo)
        VALUES (%s, %s)
        """
        val = (Telefono, Archivo)
    
    cursor.execute(sql, val)
    con.commit()
    cursor.close()

    notificarActualizacionTelefonoArchivo()
    return make_response(jsonify({}))

# Ruta para editar un registro existente
@app.route("/editar", methods=["GET"])
def editar():
    if not con.is_connected():
        con.reconnect()

    id = request.args.get("id")

    cursor = con.cursor(dictionary=True)
    sql = """
    SELECT Id_Curso_Pago, Telefono, Archivo FROM tst0_cursos_pagos
    WHERE Id_Curso_Pago = %s
    """
    val = (id,)

    cursor.execute(sql, val)
    registros = cursor.fetchall()
    con.close()

    return make_response(jsonify(registros))

# Ruta para eliminar un registro
@app.route("/eliminar", methods=["POST"])
def eliminar():
    if not con.is_connected():
        con.reconnect()

    id = request.form.get("id")

    cursor = con.cursor()
    sql = """
    DELETE FROM tst0_cursos_pagos
    WHERE Id_Curso_Pago = %s
    """
    val = (id,)

    cursor.execute(sql, val)
    con.commit()
    con.close()

    notificarActualizacionTelefonoArchivo()

    return make_response(jsonify({}))

# Iniciar la aplicación
if __name__ == "__main__":
    app.run(debug=True)
