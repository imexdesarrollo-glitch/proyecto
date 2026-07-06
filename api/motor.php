<?php
header('Content-Type: application/json; charset=utf-8');

$request = $_SERVER['REQUEST_URI'];

echo json_encode([
    "uri" => $request
]);