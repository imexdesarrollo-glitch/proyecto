<?php

$host = "sqlc75a.carrierzone.com";
$usuario = "isselmexic744610";
$password = "San2112*";
$bd = "mundial_isselmexic744610";

$conn = new mysqli(
    $host,
    $usuario,
    $password,
    $bd
);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");