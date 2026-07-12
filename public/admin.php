<?php
session_start();
if (!isset($_SESSION['usuarioID'])) {
    header('Location: /login');
    exit;
}
require_once __DIR__ . '/../src/db.php';
$pdo = DB::getPdo(true);

$nombre = strtoupper($_SESSION['nombre']);

// Manejar actualizaciones rápidas de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status' && isset($_POST['pedidoID']) && isset($_POST['nuevo_estado'])) {
        $pedidoID = (int) $_POST['pedidoID'];
        $nuevo_estado = $_POST['nuevo_estado'];

        $upd = $pdo->prepare('UPDATE pedidos SET estado = ?, fecha_modificacion = NOW() WHERE pedidoID = ?');
        $upd->execute([$nuevo_estado, $pedidoID]);

        // Si se acepta el pedido, marcar aceptado_en
        if ($nuevo_estado === 'aceptado') {
            $pdo->prepare('UPDATE pedidos SET aceptado_en = NOW() WHERE pedidoID = ?')->execute([$pedidoID]);
        }

        // Agregar registro de auditoría
        $insReg = $pdo->prepare('INSERT INTO registros_pedidos (pedidoID, evento, detalles) VALUES (?, ?, ?)');
        $insReg->execute([$pedidoID, 'CAMBIO_ESTADO', 'ESTADO ACTUALIZADO A: ' . strtoupper($nuevo_estado)]);

        // Si se aceptó, redirigir a vista del ticket
        if ($nuevo_estado === 'aceptado') {
            header('Location: ticket.php?id=' . $pedidoID);
            exit;
        }

        header('Location: admin.php');
        exit;
    }
}

// Obtener estadísticas en tiempo real
$stat_pendientes = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'")->fetchColumn();
$stat_totales = $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
$stat_monto_total = $pdo->query("SELECT SUM(monto_total) FROM pedidos")->fetchColumn() ?: 0;

// Obtener pedidos recientes
$stmt = $pdo->query("SELECT * FROM pedidos ORDER BY fecha_creacion DESC LIMIT 15");
$pedidos = $stmt->fetchAll();
?>
<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>DASHBOARD - RICO POLLO</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FFE66D', /* amarillo */
                        accent: '#E23E1A',  /* rojo */
                        dark: '#09090c'
                    }
                }
            }
        }
    </script>
    <!-- Custom CSS Styles -->
    <link rel="stylesheet" href="css/custom.css">
    <!-- FontAwesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body style="background-color:var(--color-bg);color:var(--color-text);" class="min-h-screen">
    <div class="max-w-6xl mx-auto p-4 md:p-6">
        <!-- Navigation Header -->
        <header class="glass-card p-4 md:p-6 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-32 h-20">
                    <?php if (file_exists(__DIR__ . '/../assets/logo.png')): ?>
                        <img src="../assets/logo.png" alt="LOGO" class="w-full h-full object-contain">
                    <?php else: ?>
                        <img src="../assets/logo.svg" alt="LOGO" class="w-full h-full object-contain">
                    <?php endif; ?>
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-extrabold tracking-wide uppercase">
                        <?php echo strtoupper('PANEL ADMINISTRADOR'); ?>
                    </h1>
                    <p class="text-xs text-gray-400 font-medium">
                        <i class="fa-solid fa-circle-user text-green-400 mr-1.5 animate-pulse"></i>
                        <?php echo strtoupper('SESIÓN ACTIVA'); ?>: <span
                            class="text-white font-bold"><?php echo $nombre; ?></span>
                    </p>
                </div>
            </div>
            <div>
                <a href="logout.php" class="btn-accent text-xs">
                    <i class="fa-solid fa-right-from-bracket mr-1.5"></i><?php echo strtoupper('CERRAR SESIÓN'); ?>
                </a>
            </div>
        </header>

        <!-- Metrics Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <!-- Pending Orders Widget -->
            <div class="glass-card p-5 flex items-center justify-between">
                <div>
                    <span
                        class="text-xs font-semibold text-gray-400 block tracking-wider uppercase"><?php echo strtoupper('Pendientes'); ?></span>
                    <span class="text-3xl font-extrabold text-[#FFE66D]"><?php echo $stat_pendientes; ?></span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-[#FFE66D]/10 flex items-center justify-center text-[#FFE66D]">
                    <i class="fa-solid fa-clock-rotate-left text-xl"></i>
                </div>
            </div>

            <!-- Total Monto -->
            <div class="glass-card p-5 flex items-center justify-between">
                <div>
                    <span
                        class="text-xs font-semibold text-gray-400 block tracking-wider uppercase"><?php echo strtoupper('Monto Total Pedidos'); ?></span>
                    <span
                        class="text-3xl font-extrabold text-green-400">Bs.<?php echo number_format($stat_monto_total, 2); ?></span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center text-green-400">
                    <i class="fa-solid fa-wallet text-xl"></i>
                </div>
            </div>

            <!-- Total Orders -->
            <div class="glass-card p-5 flex items-center justify-between">
                <div>
                    <span
                        class="text-xs font-semibold text-gray-400 block tracking-wider uppercase"><?php echo strtoupper('Pedidos Registrados'); ?></span>
                    <span class="text-3xl font-extrabold text-blue-400"><?php echo $stat_totales; ?></span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400">
                    <i class="fa-solid fa-receipt text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Main Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Orders Section -->
            <div class="lg:col-span-2 glass-card p-5">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/5">
                    <h2 class="text-lg font-bold tracking-wide uppercase text-white">
                        <i
                            class="fa-solid fa-utensils text-[#FFE66D] mr-2"></i><?php echo strtoupper('PEDIDOS RECIENTES'); ?>
                    </h2>
                    <span
                        class="text-[10px] bg-white/5 px-2.5 py-1 rounded-full uppercase tracking-wider text-gray-400 font-semibold">
                        ÚLTIMOS 15
                    </span>
                </div>

                <?php if (empty($pedidos)): ?>
                    <div class="py-12 text-center text-gray-500">
                        <i class="fa-regular fa-folder-open text-4xl mb-3 block"></i>
                        <p class="text-sm font-semibold uppercase">
                            <?php echo strtoupper('No se encontraron pedidos registrados'); ?>
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            <?php echo strtoupper('Usa el menú de clientes para generar pedidos de prueba.'); ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th><?php echo strtoupper('Pedido'); ?></th>
                                    <th><?php echo strtoupper('Cliente'); ?></th>
                                    <th><?php echo strtoupper('Dirección'); ?></th>
                                    <th><?php echo strtoupper('Total'); ?></th>
                                    <th><?php echo strtoupper('Estado'); ?></th>
                                    <th class="text-right"><?php echo strtoupper('Acciones'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidos as $p): ?>
                                    <tr class="align-middle">
                                        <td>
                                            <div class="font-bold text-white"><?php echo $p['numero_pedido']; ?></div>
                                            <span class="text-[10px] text-gray-500 block font-medium">
                                                <?php echo date('d/m/Y H:i', strtotime($p['fecha_creacion'])); ?>
                                            </span>
                                            <span class="inline-block mt-1 text-[9px] px-2 py-0.5 rounded font-bold uppercase <?php
                                            echo $p['tipo_pedido'] === 'domicilio' ? 'bg-purple-950/40 text-purple-300 border border-purple-800/30' :
                                                ($p['tipo_pedido'] === 'mesa' ? 'bg-cyan-950/40 text-cyan-300 border border-cyan-800/30' : 'bg-gray-800 text-gray-300');
                                            ?>">
                                                <?php echo strtoupper($p['tipo_pedido']); ?>
                                                <?php echo $p['numero_mesa'] ? '#' . $p['numero_mesa'] : ''; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="font-semibold text-gray-200">
                                                <?php echo htmlspecialchars($p['cliente_nombre']); ?>
                                            </div>
                                            <div class="text-xs text-gray-400"><i
                                                    class="fa-solid fa-phone text-[10px] mr-1"></i><?php echo htmlspecialchars($p['cliente_telefono']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-xs text-gray-300 max-w-[150px] truncate">
                                                <?php echo htmlspecialchars($p['direccion_entrega'] ?: '-'); ?>
                                            </div>
                                            <?php if (!empty($p['latitud']) && !empty($p['longitud'])): ?>
                                                <a href="https://maps.google.com?q=<?php echo $p['latitud']; ?>,<?php echo $p['longitud']; ?>"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1 text-[10px] text-blue-400 hover:text-blue-300 font-semibold mt-1">
                                                    <i class="fa-solid fa-map-location-dot"></i>ABRIR EN MAPS
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="font-bold text-[#FFE66D]">
                                                Bs.<?php echo number_format($p['monto_total'], 2); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge-status <?php
                                            echo in_array($p['estado'], ['entregado', 'completado']) ? 'badge-success' :
                                                (in_array($p['estado'], ['cancelado']) ? 'badge-error' : 'badge-pending');
                                            ?>">
                                                <?php echo strtoupper($p['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex flex-col gap-1 items-end">
                                                <!-- Formulario para Cambiar Estado -->
                                                <form method="post" class="flex gap-1 items-center">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="pedidoID" value="<?php echo $p['pedidoID']; ?>">
                                                    <select name="nuevo_estado" onchange="this.form.submit()"
                                                        class="bg-gray-900 border border-white/10 rounded text-[10px] px-1.5 py-1 text-white focus:outline-none focus:border-[#FFE66D]">
                                                        <option value=""><?php echo strtoupper('Cambiar Estado'); ?></option>
                                                        <option value="pendiente" <?php echo $p['estado'] === 'pendiente' ? 'selected' : ''; ?>>PENDIENTE</option>
                                                        <option value="aceptado" <?php echo $p['estado'] === 'aceptado' ? 'selected' : ''; ?>>ACEPTADO</option>
                                                        <option value="preparando" <?php echo $p['estado'] === 'preparando' ? 'selected' : ''; ?>>PREPARANDO</option>
                                                        <option value="listo" <?php echo $p['estado'] === 'listo' ? 'selected' : ''; ?>>LISTO</option>
                                                        <option value="entregado" <?php echo $p['estado'] === 'entregado' ? 'selected' : ''; ?>>ENTREGADO</option>
                                                        <option value="cancelado" <?php echo $p['estado'] === 'cancelado' ? 'selected' : ''; ?>>CANCELADO</option>
                                                    </select>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidemenu -->
            <aside class="space-y-6">
                <!-- Quick Actions Panel -->
                <div class="glass-card p-5">
                    <h3
                        class="text-sm font-bold uppercase tracking-wider text-[#FFE66D] mb-4 pb-2 border-b border-white/5">
                        <i class="fa-solid fa-bolt mr-2"></i><?php echo strtoupper('Acciones Rápidas'); ?>
                    </h3>
                    <div class="space-y-3">
                        <a href="/products" class="btn-primary w-full text-xs">
                            <i class="fa-solid fa-burger"></i>
                            <?php echo strtoupper('PRODUCTOS'); ?>
                        </a>
                        <a href="/categories" class="btn-outline w-full text-xs">
                            <i class="fa-solid fa-tags"></i>
                            <?php echo strtoupper('CATEGORÍAS'); ?>
                        </a>
                    </div>
                </div>

                <!-- Simulation Tools -->
                <div class="glass-card p-5">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-300 mb-3">
                        <i class="fa-solid fa-vial mr-2 text-cyan-400"></i><?php echo strtoupper('Entorno Prueba'); ?>
                    </h3>
                    <p class="text-xs text-gray-400 mb-4 leading-relaxed">
                        <?php echo strtoupper('Puedes abrir la vista del cliente para realizar simulación de pedidos y ver cómo se actualiza este dashboard.'); ?>
                    </p>
                    <a href="menu.php" target="_blank"
                        class="btn-outline w-full text-xs border-cyan-800 text-cyan-400 hover:bg-cyan-950/20">
                        <i class="fa-solid fa-external-link"></i>
                        <?php echo strtoupper('ABRIR VISTA CLIENTE'); ?>
                    </a>
                </div>
            </aside>
        </div>
    </div>
    <?php require_once __DIR__ . '/../src/theme_toggle.php'; ?>
</body>

</html>