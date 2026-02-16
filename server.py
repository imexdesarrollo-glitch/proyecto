import http.server
import socketserver
import socket
import os
from pathlib import Path

# Obtener la IP local
def get_local_ip():
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect(("8.8.8.8", 80))
        ip = s.getsockname()[0]
        s.close()
        return ip
    except:
        return "127.0.0.1"

# Configuración
PORT = 8000
DIRECTORY = os.path.dirname(os.path.abspath(__file__))

# Cambiar al directorio del proyecto
os.chdir(DIRECTORY)

class MyHTTPRequestHandler(http.server.SimpleHTTPRequestHandler):
    def end_headers(self):
        self.send_header('Cache-Control', 'no-store, no-cache, must-revalidate')
        super().end_headers()

# Crear el servidor
handler = MyHTTPRequestHandler
httpd = socketserver.TCPServer(("", PORT), handler)

# Obtener IP local
local_ip = get_local_ip()

print("=" * 60)
print(" SERVIDOR HTTP INICIADO")
print("=" * 60)
print(f" Directorio: {DIRECTORY}")
print(f" URL Local: http://localhost:{PORT}")
print(f" URL Red Local: http://{local_ip}:{PORT}")
print(f"  Puerto: {PORT}")
print("=" * 60)
print("Presiona CTRL+C para detener el servidor")
print("=" * 60)

try:
    httpd.serve_forever()
except KeyboardInterrupt:
    print("\n✅ Servidor detenido")
    httpd.server_close()
