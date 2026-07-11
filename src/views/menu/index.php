<?php
// Variables pasadas por el controlador:
// $menuGrouped - array agrupado por categoría
// $catalogoJson - catálogo JSON para el carrito
require_once __DIR__ . '/../../db.php';
?>
<!doctype html>
<html lang="es" class="dark-mode">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>RICO POLLO - MENÚ</title>
  <meta name="description" content="Pide en línea tu pollo favorito. Recoger, domicilio o mesa. RICO POLLO.">
  <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="css/custom.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    html.light-mode body {
      background-color: var(--color-bg);
      color: var(--color-text);
    }

    html.light-mode .hero-band {
      background: linear-gradient(135deg, #1a1a24 0%, #0d0d13 100%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    }

    html.dark-mode .hero-band {
      background: rgba(0, 0, 0, 0.45);
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    html.light-mode .product-name {
      color: #1a1a1a;
    }

    html.dark-mode .product-name {
      color: #f3f4f6;
    }

    html.light-mode .price-tag {
      color: var(--color-accent);
    }

    html.dark-mode .price-tag {
      color: #FFE66D;
    }

    html.light-mode .cat-header {
      color: var(--color-accent);
      border-color: rgba(226, 62, 26, 0.25);
    }

    html.dark-mode .cat-header {
      color: #FFE66D;
      border-color: rgba(255, 230, 109, 0.20);
    }

    html.light-mode .footer-text {
      color: #9ca3af;
      border-color: rgba(0, 0, 0, 0.08);
    }

    html.dark-mode .footer-text {
      color: #4b5563;
      border-color: rgba(255, 255, 255, 0.05);
    }

    html.light-mode .desc-text {
      color: #6b7280;
    }

    html.dark-mode .desc-text {
      color: #9ca3af;
    }

    html.light-mode .card-separator {
      border-color: rgba(0, 0, 0, 0.07);
    }

    html.dark-mode .card-separator {
      border-color: rgba(255, 255, 255, 0.05);
    }

    html.light-mode .hero-title,
    html.light-mode .hero-tagline {
      color: #fff;
    }

    html.dark-mode .hero-title,
    html.dark-mode .hero-tagline {
      color: #fff;
    }

    html.dark-mode .hero-tagline {
      color: #FFE66D;
    }

    html.light-mode .hero-tagline {
      color: #FFE66D;
    }

    html.light-mode .cat-fire {
      color: var(--color-accent);
    }

    html.dark-mode .cat-fire {
      color: #E23E1A;
    }

    html.light-mode .price-symbol {
      color: #9ca3af;
    }

    html.dark-mode .price-symbol {
      color: #9ca3af;
    }

    .btn-session {
      border: 1.5px solid var(--color-input-border);
      background: var(--color-card);
      color: var(--color-text-muted);
      border-radius: 10px;
      padding: 7px 14px;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.03em;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.25s ease;
    }

    .btn-session:hover {
      border-color: var(--color-primary);
      color: var(--color-primary);
    }

    /* ── FLOATING CART ── */
    #cart-fab {
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 999;
      transition: transform 0.2s ease, opacity 0.2s ease;
    }

    #cart-fab.hidden-fab {
      transform: scale(0.7);
      opacity: 0;
      pointer-events: none;
    }

    #cart-fab-btn {
      background: #E23E1A;
      color: #fff;
      border: none;
      border-radius: 50px;
      padding: 14px 22px;
      font-size: 0.85rem;
      font-weight: 900;
      letter-spacing: 0.05em;
      box-shadow: 0 8px 30px rgba(226, 62, 26, 0.55);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 10px;
      text-transform: uppercase;
      transition: transform 0.15s, box-shadow 0.15s;
    }

    #cart-fab-btn:hover {
      transform: scale(1.04);
      box-shadow: 0 10px 38px rgba(226, 62, 26, 0.7);
    }

    #cart-fab-badge {
      background: #FFE66D;
      color: #09090c;
      border-radius: 50%;
      width: 22px;
      height: 22px;
      font-size: 0.7rem;
      font-weight: 900;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* ── CART PANEL SIDEBAR ── */
    #cart-panel {
      position: fixed;
      top: 0;
      right: 0;
      height: 100%;
      width: 360px;
      max-width: 95vw;
      z-index: 1000;
      background: var(--color-card);
      border-left: 1px solid var(--color-card-border);
      box-shadow: -10px 0 40px rgba(0, 0, 0, 0.4);
      transform: translateX(100%);
      transition: transform 0.32s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      flex-direction: column;
    }

    #cart-panel.open {
      transform: translateX(0);
    }

    #cart-overlay {
      position: fixed;
      inset: 0;
      z-index: 999;
      background: rgba(0, 0, 0, 0.55);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s;
    }

    #cart-overlay.open {
      opacity: 1;
      pointer-events: auto;
    }

    .cart-item-row {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 12px 0;
      border-bottom: 1px solid var(--color-card-border);
    }

    .cart-item-info {
      flex: 1;
      font-size: 0.78rem;
      color: var(--color-text);
      font-weight: 700;
      line-height: 1.4;
    }

    .cart-item-price {
      font-size: 0.78rem;
      color: #FFE66D;
      font-weight: 900;
      white-space: nowrap;
    }

    .qty-btn {
      width: 26px;
      height: 26px;
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
      width: 28px;
      text-align: center;
      font-weight: 900;
      font-size: 0.82rem;
      color: var(--color-text);
    }

    /* Select de variantes */
    .variant-select {
      width: 100%;
      margin-top: 8px;
      padding: 6px 10px;
      font-size: 0.75rem;
      font-weight: 700;
      border-radius: 10px;
      border: 1px solid var(--color-input-border);
      background: var(--color-input-bg);
      color: var(--color-text);
      text-transform: uppercase;
      appearance: none;
      cursor: pointer;
    }
  </style>
</head>

<body class="min-h-screen">

  <!-- ══════════════════════════════ HERO BAND ═══════════════════════════════ -->
  <div class="hero-band relative overflow-hidden py-10">
    <div class="absolute inset-0 pointer-events-none"
      style="background:radial-gradient(circle at center,rgba(226,62,26,0.12) 0%,transparent 65%)"></div>
    <!-- TOP BAR -->
    <div class="absolute top-4 right-4 z-20 flex items-center gap-2">
      <button id="modeToggle" class="mode-toggle-btn" title="Cambiar modo"><span id="modeIcon">☀️</span></button>
      <a href="login.php" class="btn-session">
        <i class="fa-solid fa-user-shield"></i>
        <span class="hidden sm:inline">INICIAR SESIÓN</span>
      </a>
    </div>
    <div class="max-w-4xl mx-auto px-4 flex flex-col items-center text-center relative z-10">
      <div class="w-52 h-32 mb-3 hover:scale-105 transition-transform duration-300">
        <?php if (file_exists(__DIR__ . '/../assets/logo.png')): ?>
          <img src="../assets/logo.png" alt="RICO POLLO" class="w-full h-full object-contain">
        <?php else: ?>
          <img src="../assets/logo.svg" alt="RICO POLLO" class="w-full h-full object-contain">
        <?php endif; ?>
      </div>
      <p class="hero-tagline font-bold text-sm tracking-widest uppercase mb-1">
        <?php echo strtoupper('Sabor que cruje, pasión que deleita'); ?>
      </p>
      <h1 class="hero-title text-3xl md:text-4xl font-extrabold uppercase mb-3">
        <?php echo strtoupper('NUESTRO MENÚ'); ?>
      </h1>
      <div class="w-16 h-1 bg-[#E23E1A] rounded"></div>
    </div>
  </div>

  <!-- ══════════════════════════ CONTENIDO PRINCIPAL ══════════════════════ -->
  <div class="max-w-4xl mx-auto px-4 pb-32 pt-8">

    <?php if (empty($menuGrouped)): ?>
      <div class="glass-card p-14 text-center">
        <i class="fa-solid fa-utensils text-5xl mb-4 block" style="color:rgba(255,230,109,0.40)"></i>
        <h2 class="text-lg font-bold uppercase mb-2" style="color:var(--color-text)">
          <?php echo strtoupper('MENÚ EN PREPARACIÓN'); ?></h2>
        <p class="text-sm" style="color:var(--color-text-muted)">
          <?php echo strtoupper('Muy pronto tendremos deliciosas opciones para ti.'); ?></p>
      </div>
    <?php else: ?>
      <div class="space-y-12">
        <?php foreach ($menuGrouped as $categoria => $items): ?>
          <div>
            <h2
              class="cat-header text-lg font-extrabold tracking-wider uppercase mb-5 flex items-center gap-2 border-b pb-2">
              <i class="fa-solid fa-fire cat-fire"></i>
              <?php echo $categoria; ?>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <?php foreach ($items as $p):
                $tieneVariantes = $p['tieneVariantes'] ?? false;
                ?>
                <div class="glass-card p-5 flex flex-col justify-between relative overflow-hidden group">
                  <!-- Glow decorativo -->
                  <div class="absolute -right-16 -top-16 w-32 h-32 rounded-full blur-2xl pointer-events-none"
                    style="background:rgba(226,62,26,0.08)"></div>

                  <div>
                    <div class="mb-4 overflow-hidden rounded-[28px] h-36">
                      <img
                        src="<?php
                          if (!empty($p['imagen'])) {
                            echo '../assets/productos/' . htmlspecialchars($p['imagen']);
                          } else {
                            echo 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\' viewBox=\'0 0 100 100\'%3E%3Crect width=\'100\' height=\'100\' fill=\'%23333\'/%3E%3Ctext x=\'50%\' y=\'50%\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%23666\' font-size=\'12\'%3ESIN IMG%3C/text%3E%3C/svg%3E';
                          }
                        ?>"
                        alt="<?php echo htmlspecialchars(strtoupper($p['nombre'])); ?>"
                        class="w-full h-full object-cover transition-all duration-300" />
                    </div>
                    <h3 class="product-name font-bold text-base md:text-lg mb-1 transition-colors duration-200 pr-2">
                      <?php echo htmlspecialchars(strtoupper($p['nombre'])); ?>
                    </h3>
                    <p class="desc-text text-xs md:text-sm mb-4 leading-relaxed">
                      <?php echo htmlspecialchars(strtoupper($p['descripcion'] ?: 'SIN DESCRIPCIÓN DISPONIBLE')); ?>
                    </p>
                  </div>

                  <div class="card-separator mt-4 pt-4 border-t">
                    <?php if ($tieneVariantes): ?>
                      <!-- Chips de variantes -->
                      <div class="mb-4">
                        <div class="flex flex-wrap gap-2" id="variant-chips-<?php echo $p['productoID']; ?>">
                          <?php 
                          $firstVariant = true;
                          foreach ($p['variantes'] as $v): 
                            $vPrecioActivo = DB::obtenerPrecioActivo($v);
                            $vEnPromo = $vPrecioActivo < $v['precio'];
                            $vImagen = $v['imagen'] ?? $p['imagen'];
                          ?>
                            <button type="button"
                              onclick="selectVariant(<?php echo $p['productoID']; ?>, <?php echo $v['varianteID']; ?>, <?php echo $vPrecioActivo; ?>, <?php echo $v['precio']; ?>, <?php echo $vEnPromo ? 'true' : 'false'; ?>, '<?php echo addslashes(strtoupper($p['nombre'] . ' - ' . $v['nombre_variante'])); ?>', '<?php echo addslashes($vImagen ?? ''); ?>')"
                              class="variant-chip px-3 py-1.5 text-xs font-bold rounded-full border transition-all <?php echo $firstVariant ? 'bg-green-500 border-green-500 text-white' : 'bg-transparent border-white/20 text-gray-300 hover:border-white/40'; ?>"
                              data-producto-id="<?php echo $p['productoID']; ?>"
                              data-variante-id="<?php echo $v['varianteID']; ?>"
                              data-precio="<?php echo $vPrecioActivo; ?>"
                              data-precio-orig="<?php echo $v['precio']; ?>"
                              data-en-promo="<?php echo $vEnPromo ? '1' : '0'; ?>"
                              data-nombre-completo="<?php echo addslashes(strtoupper($p['nombre'] . ' - ' . $v['nombre_variante'])); ?>"
                              data-imagen="<?php echo addslashes($vImagen ?? ''); ?>">
                              <?php echo htmlspecialchars(strtoupper($v['nombre_variante'])); ?>
                            </button>
                            <?php $firstVariant = false; ?>
                          <?php endforeach; ?>
                        </div>
                      </div>
                      
                      <!-- Precio dinámico -->
                      <div class="flex items-center justify-between">
                        <div class="price-tag text-xl font-extrabold flex items-center gap-1.5 flex-wrap" id="precio-display-<?php echo $p['productoID']; ?>">
                          <?php 
                          $firstV = $p['variantes'][0];
                          $firstPrecioActivo = DB::obtenerPrecioActivo($firstV);
                          $firstEnPromo = $firstPrecioActivo < $firstV['precio'];
                          if ($firstEnPromo): ?>
                            <span class="text-xs line-through text-gray-500">Bs.<?php echo number_format($firstV['precio'], 2); ?></span>
                            <span class="text-green-500 font-extrabold">Bs.<?php echo number_format($firstPrecioActivo, 2); ?></span>
                            <span class="text-[9px] bg-green-500/20 text-green-400 px-1.5 py-0.5 rounded font-black uppercase">PROMO</span>
                          <?php else: ?>
                            <span class="price-symbol text-xs font-semibold">Bs.</span><?php echo number_format($firstPrecioActivo, 2); ?>
                          <?php endif; ?>
                        </div>
                        <button type="button"
                          onclick="addSelectedVariantToCart(<?php echo $p['productoID']; ?>)"
                          class="btn-primary text-xs !py-1.5 !px-4">
                          <i class="fa-solid fa-plus mr-1"></i><?php echo strtoupper('AGREGAR'); ?>
                        </button>
                      </div>
                    <?php else: ?>
                      <!-- Producto sin variantes -->
                      <div class="flex items-center justify-between">
                        <div class="price-tag text-xl font-extrabold flex items-center gap-1.5 flex-wrap">
                          <?php 
                          $precioOriginal = (float) $p['precio'];
                          $precioActivo = DB::obtenerPrecioActivo($p);
                          $enPromo = $precioActivo < $precioOriginal;
                          if ($enPromo): ?>
                            <span class="text-xs line-through text-gray-500">Bs.<?php echo number_format($precioOriginal, 2); ?></span>
                            <span class="text-green-500 font-extrabold">Bs.<?php echo number_format($precioActivo, 2); ?></span>
                            <span class="text-[9px] bg-green-500/20 text-green-400 px-1.5 py-0.5 rounded font-black uppercase">PROMO</span>
                          <?php else: ?>
                            <span class="price-symbol text-xs font-semibold">Bs.</span><?php echo number_format($precioOriginal, 2); ?>
                          <?php endif; ?>
                        </div>
                        <button type="button"
                          onclick="addToCart(<?php echo $p['productoID']; ?>, <?php echo $precioActivo; ?>, '<?php echo addslashes(strtoupper($p['nombre'])); ?>')"
                          class="btn-primary text-xs !py-1.5 !px-4">
                          <i class="fa-solid fa-plus mr-1"></i><?php echo strtoupper('AGREGAR'); ?>
                        </button>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <footer class="footer-text mt-16 text-center text-xs border-t pt-5">
      <p class="uppercase font-semibold tracking-widest">&copy; <?php echo date('Y'); ?> RICO POLLO &mdash; TODO EL
        SABOR EN UN SOLO LUGAR</p>
    </footer>
  </div>

  <!-- ═══════════════════════════════ CART FAB ════════════════════════════ -->
  <div id="cart-fab" class="hidden-fab">
    <button id="cart-fab-btn" onclick="openCart()">
      <i class="fa-solid fa-cart-shopping"></i>
      <span>VER PEDIDO</span>
      <div id="cart-fab-badge">0</div>
    </button>
  </div>

  <!-- ═════════════════════════════ CART OVERLAY ══════════════════════════ -->
  <div id="cart-overlay" onclick="closeCart()"></div>

  <!-- ═════════════════════════════ TOAST NOTIFICATION ══════════════════════════ -->
  <div id="toast-container" class="fixed top-4 right-4 z-[2000] space-y-2"></div>

  <!-- ══════════════════════════════ CART PANEL ═══════════════════════════ -->
  <div id="cart-panel">
    <!-- Header -->
    <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-card-border)">
      <h2 class="text-sm font-black uppercase tracking-wider" style="color:var(--color-text)">
        <i class="fa-solid fa-cart-shopping mr-2 text-[#FFE66D]"></i>TU PEDIDO
      </h2>
      <button onclick="closeCart()" class="qty-btn text-lg">✕</button>
    </div>

    <!-- Items list -->
    <div id="cart-items-list" class="flex-1 overflow-y-auto px-5 py-2"></div>

    <!-- Empty state -->
    <div id="cart-empty" class="flex-1 flex flex-col items-center justify-center text-center px-6 py-10">
      <i class="fa-solid fa-cart-shopping text-4xl mb-4" style="color:rgba(255,230,109,0.3)"></i>
      <p class="text-sm font-bold uppercase" style="color:var(--color-text-muted)">TU CARRITO ESTÁ VACÍO</p>
      <p class="text-xs mt-1" style="color:var(--color-text-subtle)">AGREGA PLATOS O BEBIDAS DEL MENÚ</p>
    </div>

    <!-- Footer total + actions -->
    <div class="px-5 pb-5 pt-3 border-t" style="border-color:var(--color-card-border)">
      <div class="flex justify-between items-center mb-4">
        <span class="text-xs font-bold uppercase" style="color:var(--color-text-muted)">TOTAL</span>
        <span class="text-2xl font-black text-[#FFE66D]">Bs. <span id="cart-total-display">0.00</span></span>
      </div>
      <div class="flex gap-3">
        <button onclick="clearCart()"
          class="btn-outline text-xs flex-1 py-2.5 flex items-center justify-center gap-1.5">
          <i class="fa-solid fa-trash-can"></i>VACIAR
        </button>
        <button id="checkout-btn" onclick="goToCheckout()"
          class="btn-accent text-xs flex-1 py-2.5 flex items-center justify-center gap-2 font-black">
          <i class="fa-solid fa-circle-check"></i>HACER PEDIDO
        </button>
      </div>
    </div>
  </div>

  <!-- ═════════════════════════════ SCRIPTS ════════════════════════════════ -->
  <script>
    // ── Catálogo de productos desde PHP
    const CATALOGO = <?php echo json_encode($catalogoJson, JSON_UNESCAPED_UNICODE); ?>;

    // ── Estado del carrito en localStorage
    const CART_KEY = 'rp_cart';

    // ── Estado de variantes seleccionadas por producto
    const selectedVariants = {};

    function getCart() {
      try { return JSON.parse(localStorage.getItem(CART_KEY)) || {}; } catch { return {}; }
    }
    function saveCart(cart) { localStorage.setItem(CART_KEY, JSON.stringify(cart)); }

    // ── Toast notification moderno
    function showToast(message, type = 'error') {
      const container = document.getElementById('toast-container');
      const toast = document.createElement('div');
      const bgColor = type === 'error' ? 'bg-red-500' : 'bg-green-500';
      const icon = type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check';
      
      toast.className = `${bgColor} text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 transform transition-all duration-300 translate-x-full opacity-0`;
      toast.innerHTML = `
        <i class="fa-solid ${icon}"></i>
        <span class="text-sm font-bold uppercase">${message}</span>
      `;
      
      container.appendChild(toast);
      
      // Animación de entrada
      requestAnimationFrame(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
      });
      
      // Auto eliminar después de 3 segundos
      setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    // ── Inicializar variantes seleccionadas con la primera variante de cada producto
    function initializeSelectedVariants() {
      document.querySelectorAll('[data-producto-id]').forEach(chip => {
        const productoID = parseInt(chip.dataset.productoId);
        // Solo inicializar si no está ya seleccionado (primera variante)
        if (!selectedVariants[productoID]) {
          const precio = parseFloat(chip.dataset.precio);
          const precioOrig = parseFloat(chip.dataset.precioOrig);
          const enPromo = chip.dataset.enPromo === '1';
          const nombreCompleto = chip.dataset.nombreCompleto;
          const imagen = chip.dataset.imagen;
          const varianteID = parseInt(chip.dataset.varianteId);
          
          selectedVariants[productoID] = {
            varianteID,
            precio,
            precioOrig,
            enPromo,
            nombreCompleto,
            imagen
          };
        }
      });
    }

    // ── Seleccionar variante (actualizar UI)
    function selectVariant(productoID, varianteID, precio, precioOrig, enPromo, nombreCompleto, imagen) {
      selectedVariants[productoID] = {
        varianteID,
        precio,
        precioOrig,
        enPromo,
        nombreCompleto,
        imagen
      };

      // Actualizar estilo de chips
      const chips = document.querySelectorAll(`[data-producto-id="${productoID}"]`);
      chips.forEach(chip => {
        if (parseInt(chip.dataset.varianteId) === varianteID) {
          chip.classList.remove('bg-transparent', 'border-white/20', 'text-gray-300');
          chip.classList.add('bg-green-500', 'border-green-500', 'text-white');
        } else {
          chip.classList.remove('bg-green-500', 'border-green-500', 'text-white');
          chip.classList.add('bg-transparent', 'border-white/20', 'text-gray-300');
        }
      });

      // Actualizar precio
      const precioDisplay = document.getElementById(`precio-display-${productoID}`);
      if (precioDisplay) {
        if (enPromo) {
          precioDisplay.innerHTML = `
            <span class="text-xs line-through text-gray-500">Bs.${precioOrig.toFixed(2)}</span>
            <span class="text-green-500 font-extrabold">Bs.${precio.toFixed(2)}</span>
            <span class="text-[9px] bg-green-500/20 text-green-400 px-1.5 py-0.5 rounded font-black uppercase">PROMO</span>
          `;
        } else {
          precioDisplay.innerHTML = `<span class="price-symbol text-xs font-semibold">Bs.</span>${precio.toFixed(2)}`;
        }
      }

      // Actualizar imagen si la variante tiene una
      if (imagen) {
        const img = document.querySelector(`[alt="${nombreCompleto}"]`) || 
                   document.querySelector(`.glass-card:has(#variant-chips-${productoID}) img`);
        if (img) {
          img.src = `../assets/productos/${imagen}`;
        }
      }
    }

    // ── Agregar variante seleccionada al carrito
    function addSelectedVariantToCart(productoID) {
      const selected = selectedVariants[productoID];
      if (!selected) {
        showToast('POR FAVOR SELECCIONA UNA VARIANTE', 'error');
        // Hacer focus en el contenedor de variantes
        const chipsContainer = document.getElementById(`variant-chips-${productoID}`);
        if (chipsContainer) {
          chipsContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
          chipsContainer.classList.add('animate-pulse');
          setTimeout(() => chipsContainer.classList.remove('animate-pulse'), 1000);
        }
        return;
      }

      const varianteKey = 'v' + selected.varianteID;
      const cart = getCart();
      
      if (cart[varianteKey]) {
        cart[varianteKey].qty += 1;
      } else {
        cart[varianteKey] = {
          type: 'variante',
          productoID,
          varianteID: selected.varianteID,
          nombre: selected.nombreCompleto,
          precio: selected.precio,
          qty: 1
        };
      }
      
      saveCart(cart);
      renderCart();
      openCart();
      showToast('PRODUCTO AGREGADO AL CARRITO', 'success');
    }

    // ── Agregar producto simple (sin variantes)
    function addToCart(productoID, precio, nombre) {
      const cart = getCart();
      const key = 'p' + productoID;
      if (cart[key]) {
        cart[key].qty += 1;
      } else {
        cart[key] = { type: 'producto', productoID, nombre, precio: parseFloat(precio), qty: 1 };
      }
      saveCart(cart);
      renderCart();
      openCart();
    }

    // ── Render del carrito
    function renderCart() {
      const cart = getCart();
      const keys = Object.keys(cart);
      const listEl = document.getElementById('cart-items-list');
      const emptyEl = document.getElementById('cart-empty');
      const badge = document.getElementById('cart-fab-badge');
      const fab = document.getElementById('cart-fab');
      const totalEl = document.getElementById('cart-total-display');

      let total = 0;
      let totalQty = 0;

      if (keys.length === 0) {
        listEl.innerHTML = '';
        listEl.classList.add('hidden');
        emptyEl.classList.remove('hidden');
        fab.classList.add('hidden-fab');
        badge.textContent = '0';
        totalEl.textContent = '0.00';
        return;
      }

      listEl.classList.remove('hidden');
      emptyEl.classList.add('hidden');
      fab.classList.remove('hidden-fab');

      let html = '';
      for (const key of keys) {
        const item = cart[key];
        const lineTotal = item.precio * item.qty;
        total += lineTotal;
        totalQty += item.qty;
        html += `
          <div class="cart-item-row">
            <div class="cart-item-info">
              <div class="uppercase">${item.nombre}</div>
              <div style="color:var(--color-text-muted);font-weight:500;font-size:0.7rem">Bs.${item.precio.toFixed(2)} c/u</div>
            </div>
            <div class="flex items-center gap-1.5 mt-0.5">
              <button class="qty-btn" onclick="changeQty('${key}', -1)"><i class="fa-solid fa-minus text-[10px]"></i></button>
              <span class="qty-display">${item.qty}</span>
              <button class="qty-btn" onclick="changeQty('${key}', 1)"><i class="fa-solid fa-plus text-[10px]"></i></button>
            </div>
            <div class="cart-item-price ml-2">Bs.${lineTotal.toFixed(2)}</div>
          </div>`;
      }

      listEl.innerHTML = html;
      badge.textContent = totalQty;
      totalEl.textContent = total.toFixed(2);
    }

    function changeQty(key, delta) {
      const cart = getCart();
      if (!cart[key]) return;
      cart[key].qty += delta;
      if (cart[key].qty <= 0) delete cart[key];
      saveCart(cart);
      renderCart();
    }

    function clearCart() {
      localStorage.removeItem(CART_KEY);
      renderCart();
    }

    // ── Pasar carrito a order.php via formulario POST
    function goToCheckout() {
      const cart = getCart();
      if (Object.keys(cart).length === 0) {
        alert('AGREGA AL MENOS UN PRODUCTO ANTES DE CONTINUAR.');
        return;
      }
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = 'order.php';

      for (const [key, item] of Object.entries(cart)) {
        const addInput = (name, value) => {
          const inp = document.createElement('input');
          inp.type = 'hidden'; inp.name = name; inp.value = value;
          form.appendChild(inp);
        };
        if (item.type === 'variante') {
          addInput('cart_items[' + key + '][type]', 'variante');
          addInput('cart_items[' + key + '][varianteID]', item.varianteID);
          addInput('cart_items[' + key + '][productoID]', item.productoID);
          addInput('cart_items[' + key + '][nombre]', item.nombre);
          addInput('cart_items[' + key + '][precio]', item.precio);
          addInput('cart_items[' + key + '][qty]', item.qty);
        } else {
          addInput('cart_items[' + key + '][type]', 'producto');
          addInput('cart_items[' + key + '][productoID]', item.productoID);
          addInput('cart_items[' + key + '][nombre]', item.nombre);
          addInput('cart_items[' + key + '][precio]', item.precio);
          addInput('cart_items[' + key + '][qty]', item.qty);
        }
      }
      document.body.appendChild(form);
      form.submit();
    }

    // ── Abrir / cerrar panel
    function openCart() {
      document.getElementById('cart-panel').classList.add('open');
      document.getElementById('cart-overlay').classList.add('open');
    }
    function closeCart() {
      document.getElementById('cart-panel').classList.remove('open');
      document.getElementById('cart-overlay').classList.remove('open');
    }

    // ── Tema claro/oscuro
    const html = document.documentElement;
    const modeBtn = document.getElementById('modeToggle');
    const modeIcon = document.getElementById('modeIcon');
    function applyTheme(theme) {
      if (theme === 'light') {
        html.classList.add('light-mode'); html.classList.remove('dark-mode');
        modeIcon.textContent = '🌙';
      } else {
        html.classList.add('dark-mode'); html.classList.remove('light-mode');
        modeIcon.textContent = '☀️';
      }
      localStorage.setItem('rp_theme', theme);
    }
    applyTheme(localStorage.getItem('rp_theme') || 'dark');
    modeBtn.addEventListener('click', () => {
      applyTheme(html.classList.contains('light-mode') ? 'dark' : 'light');
    });

    // ── Inicializar carrito al cargar
    renderCart();
    
    // ── Inicializar variantes seleccionadas
    initializeSelectedVariants();
  </script>
</body>

</html>