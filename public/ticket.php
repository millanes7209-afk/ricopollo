<?php
require_once __DIR__ . '/../src/db.php';
session_start();
$pdo = DB::getPdo(true);

// Verificar autenticación
if (!isset($_SESSION['usuarioID'])) {
    header('Location: login.php');
    exit;
}

$pedidoID = (int) ($_GET['id'] ?? 0);

if (!$pedidoID) {
    header('Location: admin.php');
    exit;
}

// Obtener datos del pedido
$stmt = $pdo->prepare('SELECT * FROM pedidos WHERE pedidoID = ?');
$stmt->execute([$pedidoID]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header('Location: admin.php');
    exit;
}

// Obtener items del pedido
$stmtItems = $pdo->prepare('SELECT * FROM pedido_items WHERE pedidoID = ? ORDER BY pedidoItemID ASC');
$stmtItems->execute([$pedidoID]);
$items = $stmtItems->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TICKET - <?php echo $pedido['numero_pedido']; ?></title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: 'Courier New', Courier, monospace;
      background: #1a1a1a;
      padding: 20px;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
    }
    .ticket {
      background: white;
      width: 300px;
      padding: 15px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.5);
    }
    .ticket-header {
      text-align: center;
      border-bottom: 2px dashed #000;
      padding-bottom: 10px;
      margin-bottom: 10px;
    }
    .ticket-header h1 {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 5px;
    }
    .ticket-header p {
      font-size: 10px;
    }
    .ticket-info {
      font-size: 11px;
      margin-bottom: 10px;
    }
    .ticket-info div {
      margin-bottom: 3px;
    }
    .ticket-info strong {
      font-weight: bold;
    }
    .ticket-items {
      border-top: 1px dashed #000;
      border-bottom: 1px dashed #000;
      padding: 10px 0;
      margin-bottom: 10px;
    }
    .ticket-item {
      font-size: 11px;
      margin-bottom: 8px;
    }
    .ticket-item-name {
      font-weight: bold;
    }
    .ticket-item-variant {
      font-size: 10px;
      color: #666;
    }
    .ticket-item-qty-price {
      display: flex;
      justify-content: space-between;
      margin-top: 2px;
    }
    .ticket-total {
      text-align: right;
      font-size: 14px;
      font-weight: bold;
      margin-bottom: 10px;
    }
    .ticket-footer {
      text-align: center;
      font-size: 10px;
      border-top: 1px dashed #000;
      padding-top: 10px;
    }
    .ticket-footer p {
      margin-bottom: 3px;
    }
    .actions {
      margin-top: 20px;
      text-align: center;
    }
    .btn {
      background: #FFE66D;
      color: #000;
      border: none;
      padding: 10px 20px;
      font-size: 14px;
      font-weight: bold;
      cursor: pointer;
      border-radius: 5px;
      text-decoration: none;
      display: inline-block;
      margin: 5px;
    }
    .btn:hover {
      background: #E5D060;
    }
    .btn-secondary {
      background: #666;
      color: white;
    }
    .btn-secondary:hover {
      background: #555;
    }
    @media print {
      body {
        background: white;
        padding: 0;
      }
      .ticket {
        box-shadow: none;
        width: 100%;
        max-width: 300px;
      }
      .actions {
        display: none;
      }
    }
  </style>
</head>
<body>
  <div class="ticket">
    <div class="ticket-header">
      <h1>RICO POLLO</h1>
      <p>TICKET DE PEDIDO</p>
      <p><?php echo $pedido['numero_pedido']; ?></p>
      <p><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])); ?></p>
    </div>

    <div class="ticket-info">
      <div><strong>CLIENTE:</strong> <?php echo htmlspecialchars($pedido['cliente_nombre']); ?></div>
      <div><strong>TELÉFONO:</strong> <?php echo htmlspecialchars($pedido['cliente_telefono']); ?></div>
      <div><strong>TIPO:</strong> <?php echo strtoupper($pedido['tipo_pedido']); ?></div>
      <?php if ($pedido['tipo_pedido'] === 'domicilio'): ?>
        <div><strong>DIRECCIÓN:</strong> <?php echo htmlspecialchars($pedido['direccion_entrega']); ?></div>
        <?php if (!empty($pedido['latitud']) && !empty($pedido['longitud'])): ?>
          <div><strong>GPS:</strong> <?php echo $pedido['latitud']; ?>, <?php echo $pedido['longitud']; ?></div>
        <?php endif; ?>
      <?php endif; ?>
      <?php if ($pedido['tipo_pedido'] === 'mesa' && $pedido['numero_mesa']): ?>
        <div><strong>MESA:</strong> <?php echo htmlspecialchars($pedido['numero_mesa']); ?></div>
      <?php endif; ?>
      <?php if ($pedido['nota']): ?>
        <div><strong>NOTA:</strong> <?php echo htmlspecialchars($pedido['nota']); ?></div>
      <?php endif; ?>
    </div>

    <div class="ticket-items">
      <?php foreach ($items as $item): ?>
        <div class="ticket-item">
          <div class="ticket-item-name"><?php echo htmlspecialchars($item['nombre_variante'] ?: 'PRODUCTO'); ?></div>
          <?php if ($item['nombre_variante']): ?>
            <div class="ticket-item-variant"><?php echo htmlspecialchars($item['nombre_variante']); ?></div>
          <?php endif; ?>
          <div class="ticket-item-qty-price">
            <span><?php echo $item['cantidad']; ?>x</span>
            <span>Bs.<?php echo number_format($item['precio_total'], 2); ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="ticket-total">
      TOTAL: Bs.<?php echo number_format($pedido['monto_total'], 2); ?>
    </div>

    <div class="ticket-footer">
      <p>¡GRACIAS POR SU COMPRA!</p>
      <p>TODO EL SABOR EN UN SOLO LUGAR</p>
    </div>
  </div>

  <div class="actions">
    <button onclick="window.print()" class="btn">IMPRIMIR</button>
    <a href="admin.php" class="btn btn-secondary">VOLVER</a>
  </div>
</body>
</html>
