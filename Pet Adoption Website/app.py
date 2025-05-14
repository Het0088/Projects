from flask import Flask, render_template, request
from flask_socketio import SocketIO, send

app = Flask(__name__)
socketio = SocketIO(app, cors_allowed_origins="*")

@app.route("/")
def chat():
    return render_template("chat.html")

@socketio.on("message")
def handle_message(data):
    send(data, broadcast=True)

if __name__ == "__main__":
    socketio.run(app, debug=True)
