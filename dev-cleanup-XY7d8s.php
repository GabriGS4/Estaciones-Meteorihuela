<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cambiar directorio a tu Laravel
chdir(__DIR__ . '/../estaciones'); // Ajusta si tu Laravel está en otra ruta

// Ejecutar comandos Artisan
$commands = [
    'php8.3 artisan config:clear',
    'php8.3 artisan route:clear',
    'php8.3 artisan view:clear',
    'php8.3 artisan cache:clear',
];

echo "<pre>";
foreach ($commands as $cmd) {
    echo "Ejecutando: $cmd\n";
    system($cmd, $ret);
    echo "Resultado: $ret\n\n";
}
echo "</pre>";

// Mensaje final
echo "✅ Laravel caches limpios y comandos ejecutados";
