<?php

echo "<h2>Contenido de /libs</h2>";
echo "<pre>";
print_r(scandir(__DIR__ . '/libs'));
echo "</pre>";

echo "<h2>Contenido de /libs/dompdf</h2>";
echo "<pre>";
print_r(scandir(__DIR__ . '/libs/dompdf'));
echo "</pre>";