<?php
// Router frontal para ejecutar PHP en Vercel
// Vercel requiere que las funciones serverless estén dentro de /api/
// Este router captura la ruta y requiere el archivo PHP correspondiente de /public/

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);

// Limpiar barra diagonal final
$path = rtrim($path, '/');
if (empty($path)) {
    $path = '/menu';
}

// Especiales
if ($path === '/admin') {
    $path = '/index'; // El admin de Rico Pollo es /public/index.php
}

$file = __DIR__ . '/../public' . $path;

// Si existe el archivo PHP exacto
if (file_exists($file . '.php')) {
    require_once $file . '.php';
    exit;
}

// Si se pide una URL física directa como /login.php
if (file_exists($file) && is_file($file) && str_ends_with($file, '.php')) {
    require_once $file;
    exit;
}

// Si no coincide con nada, cargar menú público por defecto o retornar 404
if (file_exists(__DIR__ . '/../public/menu.php')) {
    require_once __DIR__ . '/../public/menu.php';
    exit;
}

http_response_code(404);
echo "404 - Página no encontrada.";
