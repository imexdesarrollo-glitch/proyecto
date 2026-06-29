<?php
// 1. Activar reporte de errores correctamente
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); //
ob_start();
// ... el resto de tu código (require 'libs/...)
// 1. Cargar librerías obligatorias (Ajusta las rutas según tu servidor)
// Si usas Composer sería: require 'vendor/autoload.php';
require 'libs/dompdf/autoload.inc.php'; 
require 'libs/phpmailer/Exception.php';
require 'libs/phpmailer/PHPMailer.php';
require 'libs/phpmailer/SMTP.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'coneccion.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    die("ID de quiniela no válido.");
}

// 2. Consultar la quiniela guardada en la base de datos
$stmt = $conn->prepare("SELECT * FROM quinielas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario) {
    die("No se encontró el registro.");
}

// Descomprimir el JSON de la Quiniela
$quiniela_data = json_decode($usuario['quiniela_json'], true);

// 3. Diseñar la estructura estética del PDF usando HTML y estilos embebidos
// ... (Todo el inicio del archivo, conexión y el HTML inicial se quedan igual) ...

    <div class="titulo-seccion">Pronósticos Registrados</div>
    
    <table class="resultados">
        <thead>
            <tr>
                <th style="width: 25%;">Grupo</th>
                <th style="width: 50%;">Partido</th>
                <th style="width: 25%;">Predicción</th>
            </tr>
        </thead>
        <tbody>';

// Definimos los nombres de los grupos para identificarlos igual que en tu JS
$nombres_grupos = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L"];

// RECORRER LA MATRIZ BIDIMENSIONAL CORRECTAMENTE
foreach ($quiniela_data as $gi => $partidos_grupo) {
    $letra_grupo = $nombres_grupos[$gi] ?? "Desconocido";

    foreach ($partidos_grupo as $pi => $datos_partido) {
        // Solo mostrar los partidos donde el usuario sí hizo un pronóstico
        if (!empty($datos_partido['res'])) {
            $voto = $datos_partido['res']; // '1', 'x' o '2'
            $goles_local = $datos_partido['gl'];
            $goles_visita = $datos_partido['gv'];
            
            $clase_badge = 'badge-' . strtolower($voto);
            $texto_voto = ($voto == '1') ? 'Local (1)' : (($voto == 'x' || $voto == 'X') ? 'Empate (X)' : 'Visitante (2)');
            
            // Formatear el marcador si tiene goles anotados
            $marcador = ($goles_local !== '' && $goles_visita !== '') ? " ($goles_local - $goles_visita)" : "";

            $html .= '
            <tr>
                <td><strong>Grupo ' . $letra_grupo . '</strong></td>
                <td>Partido ' . ($pi + 1) . $marcador . '</td>
                <td><span class="badge ' . $clase_badge . '">' . $texto_voto . '</span></td>
            </tr>';
        }
    }
}

$html .= '
        </tbody>
    </table>

</body>
</html>';

// ... (Todo el resto del código hacia abajo con Dompdf y PHPMailer se queda igual) ...

// 4. Compilar el HTML para convertirlo a un PDF binario con Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Guardar el PDF generado en una variable binaria
$pdfOutput = $dompdf->output();

// 5. ENVIAR EL CORREO AUTOMÁTICO CON EL ARCHIVO ADJUNTO (PHPMailer)
$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP Corporativo
    $mail->isSMTP();
    $mail->Host       = 'sqlc75a.carrierzone.com'; 
    $mail->SMTPAuth   = true;
    $mail->Username   = 'ventas@isselmexico.com.mx'; // Tu correo de salida
    $mail->Password   = 'San2112*'; // Tu contraseña
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // O ENCRYPTION_SMTPS según tu servidor
    $mail->Port       = 587; // Puerto SMTP común

    // Destinatarios
    $mail->setFrom('ventas@isselmexico.com.mx', 'Quiniela ISSEL MÉXICO');
    $mail->addAddress('ventas@isselmexico.com.mx'); // Tu correo corporativo donde quieres recibirlo
    $mail->addReplyTo($usuario['correo'], $usuario['nombre']); // Responder directamente al usuario si es necesario

    // Adjuntar el archivo PDF directamente desde memoria de manera limpia
    $mail->addStringAttachment($pdfOutput, 'quiniela_' . $id . '.pdf');

    // Contenido del Correo Electrónico
    $mail->isHTML(true);
    $mail->Subject = 'Nueva Quiniela Registrada - ' . $usuario['nombre'];
    $mail->Body    = 'Hola Administrador,<br><br>Se ha registrado una nueva quiniela en el sitio web.<br><strong>Participante:</strong> ' . htmlspecialchars($usuario['nombre']) . '<br>Adjunto encontrarás el PDF con sus pronósticos correspondientes.<br><br>Saludos.';

    $mail->send();
} catch (Exception $e) {
    // Puedes registrar el error en un log si el envío de correo falla, pero dejar que la descarga continúe
}

// 6. ENVIAR AL NAVEGADOR DEL USUARIO (FORZAR DESCARGA)
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Tu_Quiniela_Mundial_2026.pdf"');
header('Content-Length: ' . strlen($pdfOutput));
echo $pdfOutput;
exit;