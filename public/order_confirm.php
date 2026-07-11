<?php
require_once __DIR__ . '/../src/db.php';
$pdo = DB::getPdo(true);
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT * FROM pedidos WHERE pedidoID = ?');
$stmt->execute([$id]);
$pedido = $stmt->fetch();

$items = [];
if ($pedido) {
  $stmtItems = $pdo->prepare('
        SELECT i.cantidad, i.precio_unitario, i.precio_total, i.nombre_variante, p.nombre 
        FROM pedido_items i 
        JOIN productos p ON i.productoID = p.productoID 
        WHERE i.pedidoID = ?
    ');
  $stmtItems->execute([$pedido['pedidoID']]);
  $items = $stmtItems->fetchAll();
}
?>
<!doctype html>
<html lang="es" class="dark-mode">

<head>
  <meta charset="utf-8">
  <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>CONFIRMACIÓN - RICO POLLO</title>
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

<body style="background-color:var(--color-bg);color:var(--color-text);"
  class="min-h-screen flex items-center justify-center p-4">
  <div class="max-w-xl w-full mx-auto">
    <!-- Brand Logo -->
    <div class="flex justify-center mb-6">
      <div class="w-40 h-24">
        <img src="../assets/logo.svg" alt="LOGO" class="w-full h-full object-contain">
      </div>
    </div>

    <?php if ($pedido): ?>
      <!-- Success Glass Card -->
      <div class="glass-card p-6 md:p-8 text-center space-y-6">
        <div
          class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-500/10 text-green-400 border border-green-500/30">
          <i class="fa-solid fa-circle-check text-3xl animate-bounce"></i>
        </div>

        <div>
          <h1 class="text-xl md:text-2xl font-extrabold uppercase tracking-wide text-white">
            <?php echo strtoupper('¡Pedido Recibido!'); ?>
          </h1>
          <p class="text-xs text-gray-400 mt-1 uppercase">
            <?php echo strtoupper('Prepararemos tu orden en unos instantes'); ?>
          </p>
        </div>

        <!-- Receipt Details -->
        <div class="bg-black/40 border border-white/5 rounded-2xl p-5 text-left space-y-4">
          <div class="flex justify-between items-center text-xs pb-3 border-b border-white/5 font-semibold text-gray-400">
            <span><?php echo strtoupper('Nº Pedido'); ?>: <span
                class="text-white font-extrabold"><?php echo $pedido['numero_pedido']; ?></span></span>
            <span><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])); ?></span>
          </div>

          <div class="space-y-1 text-xs">
            <p class="text-gray-400"><?php echo strtoupper('Cliente'); ?>: <span
                class="text-white font-bold"><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></span></p>
            <p class="text-gray-400"><?php echo strtoupper('Teléfono'); ?>: <span
                class="text-white font-bold"><?php echo htmlspecialchars($pedido['cliente_telefono']); ?></span></p>
            <p class="text-gray-400"><?php echo strtoupper('Tipo de pedido'); ?>: <span
                class="text-white font-bold uppercase"><?php echo $pedido['tipo_pedido']; ?>
                <?php echo $pedido['numero_mesa'] ? ' - Mesa ' . $pedido['numero_mesa'] : ''; ?></span></p>
            <?php if ($pedido['direccion_entrega']): ?>
              <p class="text-gray-400"><?php echo strtoupper('Dirección'); ?>: <span
                  class="text-white font-bold"><?php echo htmlspecialchars($pedido['direccion_entrega']); ?></span></p>
            <?php endif; ?>
          </div>

          <!-- Items Table -->
          <div class="border-t border-white/5 pt-3">
            <h3 class="text-xs font-bold text-[#FFE66D] uppercase mb-2"><i
                class="fa-solid fa-burger mr-1.5"></i><?php echo strtoupper('Detalle de compra'); ?></h3>
            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
              <?php foreach ($items as $item): ?>
                <div class="flex justify-between items-center text-xs py-1 border-b border-white/5">
                  <div class="text-gray-300">
                    <span class="font-extrabold text-[#FFE66D]"><?php echo $item['cantidad']; ?>x</span>
                    <?php
                    $dispNombre = htmlspecialchars(strtoupper($item['nombre']));
                    if (!empty($item['nombre_variante'])) {
                      $dispNombre .= ' - ' . htmlspecialchars(strtoupper($item['nombre_variante']));
                    }
                    echo $dispNombre;
                    ?>
                  </div>
                  <div class="font-bold text-white">
                    Bs.<?php echo number_format($item['precio_total'], 2); ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Grand Total -->
          <div class="flex justify-between items-center text-sm pt-3 border-t border-white/5 font-extrabold text-white">
            <span class="uppercase tracking-wider"><?php echo strtoupper('Total'); ?></span>
            <span class="text-[#FFE66D] text-lg">Bs.<?php echo number_format($pedido['monto_total'], 2); ?></span>
          </div>
        </div>

        <!-- Navigation buttons -->
        <div class="flex flex-col sm:flex-row gap-3 pt-2">
          <a href="menu.php" class="btn-primary w-full text-xs">
            <i class="fa-solid fa-house"></i><?php echo strtoupper('VOLVER AL MENÚ'); ?>
          </a>
        </div>
      </div>
    <?php else: ?>
      <!-- Error Card -->
      <div class="glass-card p-8 text-center space-y-4">
        <div
          class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-500/10 text-red-400 border border-red-500/30">
          <i class="fa-solid fa-circle-xmark text-3xl"></i>
        </div>
        <h1 class="text-lg font-bold uppercase text-white"><?php echo strtoupper('Error'); ?></h1>
        <p class="text-xs text-gray-400">
          <?php echo strtoupper('El pedido solicitado no fue encontrado en el sistema.'); ?>
        </p>
        <div class="pt-4">
          <a href="menu.php" class="btn-primary text-xs"><i
              class="fa-solid fa-house"></i><?php echo strtoupper('Ir al Menú'); ?></a>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <?php require_once __DIR__ . '/../src/theme_toggle.php'; ?>
</body>

</html>