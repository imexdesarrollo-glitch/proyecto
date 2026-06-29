<?php

require 'coneccion.php';

$sql = "
INSERT INTO quinielas
(nombre, correo, empresa, telefono, quiniela_json, ip)
VALUES
(
'Juan Prueba',
'prueba@isselmexico.com.mx',
'ISSELMEX',
'5512345678',
'{}',
'127.0.0.1'
)
";

if($conn->query($sql)){
    echo "REGISTRO GUARDADO";
}else{
    echo "ERROR: " . $conn->error;
}