<?php
require_once __DIR__ . '/../src/db.php';
session_start();
$pdo = DB::getPdo(true);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  error_log('🔍 POST recibido en order.php');
  error_log('🔍 POST data: ' . print_r($_POST, true));
  
  $cartItems = $_POST['cart_items'] ?? [];
  $cartFinal = $_POST['cart_items_final'] ?? [];

  // ── GUARDAR carrito en session para rellenar el formulario de revisión
  // Si llega cart_items desde menu.php, lo guardamos y mostramos confirmación
  $confirmando = !empty($cartItems);
  
  error_log('🔍 confirmando: ' . ($confirmando ? 'SI' : 'NO'));
  error_log('🔍 confirmar_pedido seteado: ' . (isset($_POST['confirmar_pedido']) ? 'SI' : 'NO'));
  error_log('🔍 cart_items_final tiene items: ' . count($cartFinal));

  // Procesar pedido si viene confirmar_pedido (con cart_items_final del formulario)
  if (isset($_POST['confirmar_pedido']) && !empty($cartFinal)) {
    error_log('🔍 Entrando a procesar pedido final');
    // ── PROCESAR ORDEN FINAL
    $cliente_nombre = strtoupper(trim($_POST['cliente_nombre'] ?? 'CLIENTE'));
    $cliente_telefono = strtoupper(trim($_POST['cliente_telefono'] ?? ''));
    $tipo_pedido = 'domicilio';
    $direccion = strtoupper(trim($_POST['direccion_entrega'] ?? ''));
    $numero_mesa = '';
    $nota = strtoupper(trim($_POST['nota'] ?? ''));
    $latitud = !empty($_POST['latitud']) ? (float)$_POST['latitud'] : null;
    $longitud = !empty($_POST['longitud']) ? (float)$_POST['longitud'] : null;
    $numero_pedido = 'RPO-' . time();

    // Validaciones
    if (empty($cliente_telefono)) {
      $error = 'EL TELÉFONO ES OBLIGATORIO';
    }
    if (empty($cliente_nombre)) {
      $error = 'EL NOMBRE ES OBLIGATORIO';
    }
    if (empty($direccion)) {
      $error = 'LA DIRECCIÓN ES OBLIGATORIA';
    }
    
    if ($error) {
      // Si hay error, no procesar
    } else {
      // Rebuild cart from POST hidden inputs
      $cartRaw = $_POST['cart_items_final'] ?? [];

      $pdo->beginTransaction();
      try {
        $ins = $pdo->prepare('INSERT INTO pedidos (numero_pedido,cliente_nombre,cliente_telefono,tipo_pedido,numero_mesa,direccion_entrega,nota,monto_total,estado,latitud,longitud) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $ins->execute([$numero_pedido, $cliente_nombre, $cliente_telefono, $tipo_pedido, $numero_mesa, $direccion, $nota, 0, 'pendiente', $latitud, $longitud]);
        $pedidoID = $pdo->lastInsertId();

        $total = 0;
        foreach ($cartRaw as $lineKey => $line) {
          $qty = (int) ($line['qty'] ?? 0);
          if ($qty <= 0)
            continue;

          $type = $line['type'] ?? 'producto';
          $productoID = (int) ($line['productoID'] ?? 0);
          $nombre = strtoupper($line['nombre'] ?? '');
          $precio = (float) ($line['precio'] ?? 0);
          $lineTotal = $precio * $qty;

          $nombre_variante = null;
          if ($type === 'variante' && strpos($nombre, ' - ') !== false) {
            $parts = explode(' - ', $nombre, 2);
            $nombre_variante = trim($parts[1]);
          }

          $insItem = $pdo->prepare('INSERT INTO pedido_items (pedidoID,productoID,nombre_variante,cantidad,precio_unitario,precio_total) VALUES (?,?,?,?,?,?)');
          $insItem->execute([$pedidoID, $productoID ?: null, $nombre_variante, $qty, $precio, $lineTotal]);
          $total += $lineTotal;
        }

        if ($total <= 0)
          throw new Exception('DEBES AGREGAR AL MENOS UN PRODUCTO.');

        $pdo->prepare('UPDATE pedidos SET monto_total = ? WHERE pedidoID = ?')->execute([$total, $pedidoID]);
        $pdo->prepare('INSERT INTO registros_pedidos (pedidoID, evento, detalles) VALUES (?, ?, ?)')->execute([$pedidoID, 'CREACION_PEDIDO', 'PEDIDO CREADO POR EL CLIENTE']);
        $pdo->commit();

        // Limpiar sesión de carrito si hay
        header('Location: order_confirm.php?id=' . $pedidoID);
        exit;
      } catch (Exception $e) {
        $pdo->rollBack();
        $error = strtoupper($e->getMessage() ?: 'ERROR AL CREAR PEDIDO');
      }
    }
  }
}

// Cart items para mostrar revisión (vienen del POST inicial del menú)
$cartItemsDisplay = $_POST['cart_items'] ?? [];

// Si no hay items y tampoco se envió el formulario de confirmación, redirigir al menú
if (empty($cartItemsDisplay) && empty($_POST['cart_items_final'])) {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: menu.php');
    exit;
  }
}

// Recalcular total display
$displayTotal = 0;
foreach ($cartItemsDisplay as $line) {
  $displayTotal += (float) ($line['precio'] ?? 0) * (int) ($line['qty'] ?? 0);
}

// Rebuild final cart si estamos en el segundo POST (error de validación)
$cartFinal = $_POST['cart_items_final'] ?? $cartItemsDisplay;
if (!empty($cartFinal)) {
  $displayTotal = 0;
  foreach ($cartFinal as $line) {
    $displayTotal += (float) ($line['precio'] ?? 0) * (int) ($line['qty'] ?? 0);
  }
  $cartItemsDisplay = $cartFinal;
}
?>
<!doctype html>
<html lang="es" class="dark-mode">

<head>
  <meta charset="utf-8">
  <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>CONFIRMAR PEDIDO - RICO POLLO</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { theme: { extend: { colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' } } } }</script>
  <link rel="stylesheet" href="css/custom.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>
    .order-item-row {
      padding: 11px 0;
      border-bottom: 1px solid var(--color-card-border);
      display: flex;
      align-items: flex-start;
      gap: 12px;
    }

    .qty-btn {
      width: 28px;
      height: 28px;
      border-radius: 8px;
      border: 1px solid var(--color-card-border);
      background: var(--color-bg-alt);
      color: var(--color-text);
      cursor: pointer;
      font-size: 0.85rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: background 0.15s;
    }

    .qty-btn:hover {
      background: rgba(255, 230, 109, 0.15);
      color: #FFE66D;
    }

    .qty-display {
      width: 30px;
      text-align: center;
      font-weight: 900;
      font-size: 0.85rem;
      color: var(--color-text);
    }

    #gps-map {
      height: 250px;
      border-radius: 8px;
      margin-top: 12px;
    }
  </style>
</head>

<body class="min-h-screen" style="background-color:var(--color-bg);color:var(--color-text);">

  <button id="modeToggle" class="mode-toggle-btn" style="position:fixed;top:16px;right:16px;z-index:50;"
    title="Cambiar modo">
    <span id="modeIcon">☀️</span>
  </button>

  <div class="max-w-2xl mx-auto px-4 py-10">

    <!-- Back link -->
    <a href="menu.php" class="inline-flex items-center gap-2 text-xs font-bold uppercase mb-6 btn-outline px-4 py-2">
      <i class="fa-solid fa-arrow-left"></i>VOLVER AL MENÚ
    </a>

    <h1 class="text-2xl font-extrabold uppercase mb-1" style="color:var(--color-text)">
      <i class="fa-solid fa-receipt mr-2 text-[#FFE66D]"></i>CONFIRMAR PEDIDO
    </h1>
    <p class="text-xs uppercase font-semibold mb-8" style="color:var(--color-text-muted)">REVISA TU SELECCIÓN ANTES DE
      ENVIAR</p>

    <?php if ($error): ?>
      <div class="mb-6 p-4 rounded-xl text-sm font-semibold flex items-center gap-3"
        style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.4);color:#fca5a5">
        <i class="fa-solid fa-circle-exclamation text-red-500"></i><?php echo $error; ?>
      </div>
    <?php endif; ?>

    <!-- Si el carrito está vacío -->
    <?php if (empty($cartItemsDisplay)): ?>
      <div class="glass-card p-14 text-center">
        <i class="fa-solid fa-cart-shopping text-5xl mb-4 block" style="color:rgba(255,230,109,0.3)"></i>
        <h2 class="text-lg font-bold uppercase mb-3" style="color:var(--color-text)">CARRITO VACÍO</h2>
        <a href="menu.php" class="btn-accent text-sm">
          <i class="fa-solid fa-arrow-left mr-2"></i>IR AL MENÚ
        </a>
      </div>
    <?php else: ?>

      <form method="POST" id="order-form" onsubmit="console.log('🔍 Formulario submit iniciado'); return prepareSubmit()">
        <!-- ── RESUMEN DE PRODUCTOS ── -->
        <div class="glass-card p-5 mb-5">
          <h2 class="text-xs font-extrabold uppercase tracking-wider mb-4 pb-2 border-b"
            style="border-color:var(--color-card-border);color:var(--color-text-muted)">
            <i class="fa-solid fa-list-ul mr-1.5 text-[#FFE66D]"></i>PRODUCTOS SELECCIONADOS
          </h2>

          <div id="order-items-list">
            <?php foreach ($cartItemsDisplay as $lineKey => $line):
              $qty = (int) ($line['qty'] ?? 1);
              $precio = (float) ($line['precio'] ?? 0);
              $nombre = strtoupper($line['nombre'] ?? '');
              $type = $line['type'] ?? 'producto';
              $prodID = (int) ($line['productoID'] ?? 0);
              $varID = (int) ($line['varianteID'] ?? 0);
              $lineTotal = $precio * $qty;
              ?>
              <div class="order-item-row" id="row_<?php echo htmlspecialchars($lineKey); ?>">
                <div style="flex:1">
                  <div class="font-bold text-sm uppercase" style="color:var(--color-text)">
                    <?php echo htmlspecialchars($nombre); ?>
                  </div>
                  <div class="text-xs mt-0.5" style="color:var(--color-text-muted)">
                    Bs.<?php echo number_format($precio, 2); ?> c/u</div>
                </div>
                <div class="flex items-center gap-2">
                  <button type="button" class="qty-btn"
                    onclick="changeOrderQty('<?php echo htmlspecialchars($lineKey); ?>', -1)">
                    <i class="fa-solid fa-minus text-[10px]"></i>
                  </button>
                  <span class="qty-display" id="disp_<?php echo htmlspecialchars($lineKey); ?>"><?php echo $qty; ?></span>
                  <button type="button" class="qty-btn"
                    onclick="changeOrderQty('<?php echo htmlspecialchars($lineKey); ?>', 1)">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                  </button>
                </div>
                <div class="text-sm font-extrabold text-[#FFE66D] ml-1 min-w-[72px] text-right"
                  id="linetotal_<?php echo htmlspecialchars($lineKey); ?>">
                  Bs.<?php echo number_format($lineTotal, 2); ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Total -->
          <div class="flex justify-between items-center pt-4 mt-2 border-t" style="border-color:var(--color-card-border)">
            <span class="text-sm font-extrabold uppercase" style="color:var(--color-text-muted)">TOTAL</span>
            <span class="text-2xl font-black text-[#FFE66D]">Bs. <span
                id="grand-total"><?php echo number_format($displayTotal, 2); ?></span></span>
          </div>
        </div>

        <!-- ── DATOS DEL CLIENTE ── -->
        <div class="glass-card p-5 mb-5 space-y-4">
          <h2 class="text-xs font-extrabold uppercase tracking-wider pb-2 border-b"
            style="border-color:var(--color-card-border);color:var(--color-text-muted)">
            <i class="fa-solid fa-user mr-1.5 text-[#FFE66D]"></i>TUS DATOS
          </h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                style="color:var(--color-text-muted)">NOMBRE *</label>
              <div class="relative">
                <span class="absolute left-3.5 top-3.5" style="color:var(--color-text-subtle)"><i
                    class="fa-solid fa-user text-xs"></i></span>
                <input name="cliente_nombre" required oninput="this.value=this.value.toUpperCase()"
                  class="form-input pl-9" placeholder="EJ: JUAN PÉREZ"
                  value="<?php echo htmlspecialchars($_POST['cliente_nombre'] ?? ''); ?>" />
              </div>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                style="color:var(--color-text-muted)">TELÉFONO *</label>
              <div class="relative">
                <span class="absolute left-3.5 top-3.5" style="color:var(--color-text-subtle)"><i
                    class="fa-solid fa-phone text-xs"></i></span>
                <input name="cliente_telefono" required oninput="this.value=this.value.toUpperCase()" class="form-input pl-9"
                  placeholder="EJ: 70012345" value="<?php echo htmlspecialchars($_POST['cliente_telefono'] ?? ''); ?>" />
              </div>
            </div>
          </div>

          <!-- Domicilio (Siempre visible, ya que todo pedido de la App es Domicilio) -->
          <div id="wrapper_direccion">
            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
              style="color:var(--color-text-muted)">DIRECCIÓN DE ENTREGA *</label>
            <div class="relative">
              <span class="absolute left-3.5 top-3.5 pt-0.5" style="color:var(--color-text-subtle)"><i
                  class="fa-solid fa-location-dot text-xs"></i></span>
              <input id="direccion_entrega" name="direccion_entrega" oninput="this.value=this.value.toUpperCase()"
                required class="form-input pl-9" placeholder="EJ: AV. PRINCIPAL 123, EDIFICIO ABC"
                value="<?php echo htmlspecialchars($_POST['direccion_entrega'] ?? ''); ?>" />
            </div>
            
            <!-- Botón GPS -->
            <button type="button" onclick="getLocation()" 
              class="mt-2 text-xs font-bold uppercase btn-outline py-2 px-4 flex items-center gap-2">
              <i class="fa-solid fa-location-crosshairs"></i>USAR MI UBICACIÓN ACTUAL 📍
            </button>
            
            <!-- Contenedor del mapa -->
            <div id="map-container" class="hidden mt-3">
              <div id="gps-map"></div>
              <p class="text-xs mt-2" style="color:var(--color-text-subtle)">
                <i class="fa-solid fa-circle-info mr-1"></i>Arrastra el pin para ajustar tu ubicación exacta
              </p>
            </div>
            
            <!-- Campos ocultos para coordenadas -->
            <input type="hidden" id="latitud" name="latitud" value="<?php echo htmlspecialchars($_POST['latitud'] ?? ''); ?>" />
            <input type="hidden" id="longitud" name="longitud" value="<?php echo htmlspecialchars($_POST['longitud'] ?? ''); ?>" />
          </div>

          <!-- Nota -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
              style="color:var(--color-text-muted)">NOTAS / INSTRUCCIONES (OPCIONAL)</label>
            <textarea name="nota" rows="2" oninput="this.value=this.value.toUpperCase()" class="form-input uppercase"
              placeholder="EJ: SIN CEBOLLA, SALSA EXTRA..."><?php echo htmlspecialchars($_POST['nota'] ?? ''); ?></textarea>
          </div>
        </div>

        <!-- ── CAMPOS OCULTOS DEL CARRITO (se llenan en JS antes de submit) ── -->
        <div id="cart-hidden-inputs"></div>

        <!-- Bandera para indicar que es confirmación final -->
        <input type="hidden" name="confirmar_pedido" value="1">

        <!-- ── BOTÓN CONFIRMAR ── -->
        <div class="flex items-center gap-4">
          <a href="menu.php" class="btn-outline text-sm flex items-center gap-2 px-5 py-3">
            <i class="fa-solid fa-arrow-left"></i>EDITAR
          </a>
          <button type="submit"
            class="btn-accent flex-1 py-3 text-sm font-black flex items-center justify-center gap-2 uppercase">
            <i class="fa-solid fa-circle-check text-base"></i>CONFIRMAR Y ENVIAR PEDIDO
          </button>
        </div>

      </form>

    <?php endif; ?>
  </div>

  <script>
    // ── Carrito local para edición en esta página
    const cartData = <?php echo json_encode($cartItemsDisplay, JSON_UNESCAPED_UNICODE); ?>;

    // ── Variables GPS
    let map = null;
    let marker = null;

    // ── Obtener ubicación GPS
    function getLocation() {
      if (!navigator.geolocation) {
        alert('TU NAVEGADOR NO SOPORTA GEOLOCALIZACIÓN');
        return;
      }

      const btn = event.target;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>OBTENIENDO UBICACIÓN...';
      btn.disabled = true;

      navigator.geolocation.getCurrentPosition(
        (position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          
          document.getElementById('latitud').value = lat;
          document.getElementById('longitud').value = lng;
          
          initMap(lat, lng);
          
          btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>USAR MI UBICACIÓN ACTUAL 📍';
          btn.disabled = false;
        },
        (error) => {
          console.error('Error GPS:', error);
          let errorMsg = 'NO SE PUDO OBTENER TU UBICACIÓN';
          if (error.code === 1) {
            errorMsg = 'PERMISO DE UBICACIÓN DENEGADO';
          } else if (error.code === 2) {
            errorMsg = 'UBICACIÓN NO DISPONIBLE';
          } else if (error.code === 3) {
            errorMsg = 'TIEMPO DE ESPERA AGOTADO';
          }
          alert(errorMsg + '. PUEDES CONTINUAR CON LA DIRECCIÓN ESCRITA.');
          btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>USAR MI UBICACIÓN ACTUAL 📍';
          btn.disabled = false;
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
      );
    }

    // ── Inicializar mapa Leaflet
    function initMap(lat, lng) {
      const mapContainer = document.getElementById('map-container');
      mapContainer.classList.remove('hidden');

      if (map) {
        map.remove();
      }

      map = L.map('gps-map').setView([lat, lng], 16);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
      }).addTo(map);

      // Pin arrastrable
      marker = L.marker([lat, lng], { draggable: true }).addTo(map);

      // Actualizar coordenadas al mover el pin
      marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        document.getElementById('latitud').value = position.lat;
        document.getElementById('longitud').value = position.lng;
      });
    }

    // ── Cambiar cantidad en pantalla de revisión
    function changeOrderQty(key, delta) {
      if (!cartData[key]) return;
      cartData[key].qty = (cartData[key].qty || 1) + delta;
      if (cartData[key].qty <= 0) {
        delete cartData[key];
        const row = document.getElementById('row_' + key);
        if (row) row.remove();
      } else {
        const disp = document.getElementById('disp_' + key);
        const lt = document.getElementById('linetotal_' + key);
        if (disp) disp.textContent = cartData[key].qty;
        if (lt) lt.textContent = 'Bs.' + (cartData[key].precio * cartData[key].qty).toFixed(2);
      }
      updateGrandTotal();
    }

    function updateGrandTotal() {
      let total = 0;
      for (const k in cartData) total += (cartData[k].precio * cartData[k].qty);
      const el = document.getElementById('grand-total');
      if (el) el.textContent = total.toFixed(2);
    }

    // ── Preparar inputs ocultos del carrito antes de submit
    function prepareSubmit() {
      try {
        const keys = Object.keys(cartData);
        
        if (keys.length === 0) {
          alert('NO QUEDAN PRODUCTOS EN TU PEDIDO. VUELVE AL MENÚ.');
          return false;
        }
        
        const container = document.getElementById('cart-hidden-inputs');
        container.innerHTML = '';
        
        for (const [k, item] of Object.entries(cartData)) {
          const add = (name, value) => {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = name; inp.value = value;
            container.appendChild(inp);
          };
          add('cart_items_final[' + k + '][type]', item.type || 'producto');
          add('cart_items_final[' + k + '][productoID]', item.productoID || '');
          if (item.varianteID) add('cart_items_final[' + k + '][varianteID]', item.varianteID);
          add('cart_items_final[' + k + '][nombre]', item.nombre || '');
          add('cart_items_final[' + k + '][precio]', item.precio || 0);
          add('cart_items_final[' + k + '][qty]', item.qty || 1);
        }

        // Limpiar el carrito de localStorage al confirmar
        localStorage.removeItem('rp_cart');
        
        return true;
      } catch (error) {
        alert('ERROR AL PROCESAR EL PEDIDO: ' + error.message);
        return false;
      }
    }

    // No se necesita JS para cambiar tipo de pedido

    // ── Tema
    const html2 = document.documentElement;
    const modeBtn2 = document.getElementById('modeToggle');
    const modeIcon2 = document.getElementById('modeIcon');
    function applyTheme2(t) {
      html2.className = t === 'light' ? 'light-mode' : 'dark-mode';
      modeIcon2.textContent = t === 'light' ? '🌙' : '☀️';
      localStorage.setItem('rp_theme', t);
    }
    applyTheme2(localStorage.getItem('rp_theme') || 'dark');
    modeBtn2.addEventListener('click', () => applyTheme2(html2.classList.contains('light-mode') ? 'dark' : 'light'));
  </script>
  <?php require_once __DIR__ . '/../src/theme_toggle.php'; ?>
</body>

</html>