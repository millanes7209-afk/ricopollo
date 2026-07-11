<?php
require_once __DIR__ . '/src/db.php';

$pdo = DB::getPdo(true);

echo "=== PRODUCTOS EN BD ===\n";
$stmt = $pdo->query('SELECT productoID, nombre, disponible, categoriaID FROM productos');
while ($row = $stmt->fetch()) {
    echo "ID: {$row['productoID']} | {$row['nombre']} | disponible: " . ($row['disponible'] ?? 'NULL') . " | categoriaID: {$row['categoriaID']}\n";
}

echo "\n=== CATEGORÍAS ===\n";
$stmt = $pdo->query('SELECT categoriaID, nombre FROM categorias');
while ($row = $stmt->fetch()) {
    echo "ID: {$row['categoriaID']} | {$row['nombre']}\n";
}

echo "\n=== CONSULTA ACTUAL DEL MENÚ ===\n";
$stmt = $pdo->query('
    SELECT p.productoID, p.nombre, p.descripcion, p.precio, p.precio_promo, p.dias_promo, p.imagen, p.disponible,
           COALESCE(c.nombre, "NUESTROS CLÁSICOS") AS categoria_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.categoriaID = c.categoriaID
    WHERE p.disponible = 1 OR c.nombre = "BEBIDA"
    ORDER BY c.nombre DESC, p.orden_mostrado ASC
');
while ($row = $stmt->fetch()) {
    echo "ID: {$row['productoID']} | {$row['nombre']} | cat: {$row['categoria_nombre']} | disponible: " . ($row['disponible'] ?? 'NULL') . "\n";
}
