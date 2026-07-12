<?php
session_start();
require_once __DIR__ . '/../src/db.php';
if (!isset($_SESSION['usuarioID'])) {
    header('Location: /login');
    exit;
}
$pdo = DB::getPdo(true);

// Eliminar categoría
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    // Desvincular productos antes de borrar
    $pdo->prepare('UPDATE productos SET categoriaID = NULL WHERE categoriaID = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM categorias WHERE categoriaID = ?')->execute([$id]);
    header('Location: /categories');
    exit;
}

$cats = $pdo->query('SELECT c.categoriaID, c.nombre, c.descripcion, c.activo, COUNT(p.productoID) AS total_productos
    FROM categorias c
    LEFT JOIN productos p ON p.categoriaID = c.categoriaID
    GROUP BY c.categoriaID
    ORDER BY c.nombre ASC')->fetchAll();
?>
<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CATEGORÍAS - RICO POLLO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' } } } }</script>
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body style="background-color:var(--color-bg);color:var(--color-text);" class="min-h-screen">
    <div class="max-w-4xl mx-auto p-4 md:p-6">

        <!-- Header -->
        <header class="glass-card p-4 md:p-5 mb-6 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-14 h-9">
                    <img src="../assets/logo.svg" alt="LOGO" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-lg font-extrabold tracking-wide uppercase">GESTIÓN DE CATEGORÍAS</h1>
                    <p class="text-xs font-medium uppercase" style="color:var(--color-text-muted)">ORGANIZA TU MENÚ POR
                        SECCIONES</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="/admin" class="btn-outline text-xs !py-1.5 !px-3">
                    <i class="fa-solid fa-arrow-left mr-1"></i>VOLVER
                </a>
                <a href="/category_form" class="btn-primary text-xs !py-1.5 !px-3">
                    <i class="fa-solid fa-plus-circle mr-1"></i>NUEVA CATEGORÍA
                </a>
            </div>
        </header>

        <!-- Table -->
        <div class="glass-card p-5">
            <?php if (empty($cats)): ?>
                <div class="py-14 text-center">
                    <i class="fa-solid fa-tags text-5xl mb-4 block" style="color:rgba(255,230,109,0.3)"></i>
                    <p class="text-sm font-bold uppercase">SIN CATEGORÍAS CREADAS</p>
                    <p class="text-xs mt-1" style="color:var(--color-text-muted)">CREA TU PRIMERA CATEGORÍA PARA ORGANIZAR
                        EL MENÚ.</p>
                    <div class="mt-5">
                        <a href="/category_form" class="btn-primary text-xs">
                            <i class="fa-solid fa-plus mr-1"></i>CREAR CATEGORÍA
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>NOMBRE</th>
                                <th>DESCRIPCIÓN</th>
                                <th>PRODUCTOS</th>
                                <th>ACTIVA</th>
                                <th class="text-right">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cats as $c): ?>
                                <tr>
                                    <td>
                                        <div class="font-bold" style="color:var(--color-text)">
                                            <?php echo strtoupper(htmlspecialchars($c['nombre'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-xs" style="color:var(--color-text-muted)">
                                            <?php echo $c['descripcion'] ? strtoupper(htmlspecialchars($c['descripcion'])) : '—'; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-pending">
                                            <?php echo $c['total_productos']; ?> PLATILLO
                                            <?php echo $c['total_productos'] != 1 ? 'S' : ''; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge-status <?php echo $c['activo'] ? 'badge-success' : 'badge-error'; ?>">
                                            <?php echo $c['activo'] ? 'SÍ' : 'NO'; ?>
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div class="inline-flex gap-2">
                                            <a href="/category_form?id=<?php echo $c['categoriaID']; ?>"
                                                class="btn-outline text-[11px] !py-1 !px-2.5 hover:!border-[#FFE66D] hover:!text-[#FFE66D]">
                                                <i class="fa-solid fa-pen-to-square mr-1"></i>EDITAR
                                            </a>
                                            <a href="/categories?action=delete&id=<?php echo $c['categoriaID']; ?>"
                                                onclick="return confirm('¿ELIMINAR ESTA CATEGORÍA? LOS PRODUCTOS PERDERÁN SU CATEGORÍA.');"
                                                class="btn-outline text-[11px] !py-1 !px-2.5"
                                                style="border-color:rgba(239,68,68,0.3);color:#ef4444;">
                                                <i class="fa-regular fa-trash-can mr-1"></i>ELIMINAR
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php require_once __DIR__ . '/../src/theme_toggle.php'; ?>
</body>

</html>