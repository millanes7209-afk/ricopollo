<?php
// Variables pasadas por el controlador:
// $productos - array de productos
// $variantesMap - array de variantes agrupadas por productoID
?>
<!doctype html>
<html lang="es" class="dark-mode">

<head>
  <meta charset="utf-8">
  <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PRODUCTOS - RICO POLLO</title>
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
  <div class="max-w-4xl mx-auto p-4 md:p-6">
    <!-- Header -->
    <header class="glass-card p-4 md:p-6 mb-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-16 h-10">
          <img src="../assets/logo.svg" alt="LOGO" class="w-full h-full object-contain">
        </div>
        <div>
          <h1 class="text-lg md:text-xl font-extrabold tracking-wide uppercase">
            <?php echo strtoupper('GESTIÓN DE PRODUCTOS'); ?>
          </h1>
          <p class="text-xs text-gray-400 font-medium uppercase">
            <?php echo strtoupper('Catálogo de ventas'); ?>
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a href="/admin" class="btn-outline text-xs !py-1.5 !px-3">
          <i class="fa-solid fa-arrow-left mr-1"></i><?php echo strtoupper('VOLVER'); ?>
        </a>
        <a href="/product_form" class="btn-primary text-xs !py-1.5 !px-3">
          <i class="fa-solid fa-plus-circle mr-1"></i><?php echo strtoupper('CREAR'); ?>
        </a>
      </div>
    </header>

    <!-- Product Table Wrapper -->
    <div class="glass-card p-5">
      <?php if (empty($productos)): ?>
        <div class="py-12 text-center text-gray-500">
          <i class="fa-solid fa-burger text-5xl mb-4 block text-[#FFE66D]/30"></i>
          <p class="text-sm font-semibold uppercase"><?php echo strtoupper('No hay productos creados'); ?></p>
          <p class="text-xs text-gray-400 mt-1">
            <?php echo strtoupper('Comienza agregando un nuevo platillo al catálogo.'); ?>
          </p>
          <div class="mt-4">
            <a href="/product_form" class="btn-primary text-xs"><i
                class="fa-solid fa-plus mr-1"></i><?php echo strtoupper('Crear Producto'); ?></a>
          </div>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="custom-table">
            <thead>
              <tr>
                <th><?php echo strtoupper('Imagen'); ?></th>
                <th><?php echo strtoupper('Nombre del platillo'); ?></th>
                <th><?php echo strtoupper('Precio'); ?></th>
                <th><?php echo strtoupper('Disponible'); ?></th>
                <th class="text-right"><?php echo strtoupper('Acciones'); ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($productos as $p): ?>
                <?php $tieneVariantes = !empty($variantesMap[$p['productoID']]); ?>
                <tr class="align-middle">
                  <td class="w-20">
                    <?php if (!empty($p['imagen']) && file_exists(__DIR__ . '/../assets/productos/' . $p['imagen'])): ?>
                      <img src="../assets/productos/<?php echo htmlspecialchars($p['imagen']); ?>" alt="IMG"
                        class="w-14 h-10 object-cover rounded-lg border border-white/10" />
                    <?php else: ?>
                      <div
                        class="w-14 h-10 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-[10px] uppercase text-gray-400">
                        <?php echo strtoupper('SIN IMG'); ?>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="font-bold text-white"><?php echo htmlspecialchars(strtoupper($p['nombre'])); ?></div>
                    <div class="text-[10px] text-gray-400 uppercase font-semibold mt-0.5">
                      <i
                        class="fa-solid fa-list mr-1"></i><?php echo htmlspecialchars(strtoupper($p['categoria_nombre'] ?: 'SIN CATEGORÍA')); ?>
                    </div>
                  </td>
                  <td>
                    <?php if ($tieneVariantes): ?>
                      <div class="text-[10px] text-gray-400 uppercase font-semibold">
                        <?php echo count($variantesMap[$p['productoID']]); ?>       <?php echo strtoupper('VARIANTES'); ?>
                      </div>
                    <?php else: ?>
                      <div class="font-bold text-[#FFE66D]">Bs.<?php echo number_format($p['precio'], 2); ?></div>
                      <?php if (!empty($p['precio_promo'])): ?>
                        <div class="text-[10px] text-green-400 font-semibold mt-0.5">
                          <i class="fa-solid fa-tags text-[9px] mr-0.5"></i>
                          Bs.<?php echo number_format($p['precio_promo'], 2); ?>
                          <span class="text-gray-400 font-normal">(<?php echo htmlspecialchars($p['dias_promo']); ?>)</span>
                        </div>
                      <?php endif; ?>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <a href="/products?action=toggle&id=<?php echo $p['productoID']; ?>"
                      class="inline-flex items-center gap-2 btn-outline text-[11px] !py-1 !px-2.5 <?php echo $p['disponible'] ? 'border-green-500 text-green-300 hover:text-white' : 'border-red-500 text-red-300 hover:text-white'; ?>"
                      title="<?php echo $p['disponible'] ? 'Marcar como no disponible' : 'Marcar como disponible'; ?>">
                      <i class="fa-solid <?php echo $p['disponible'] ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                      <?php echo $p['disponible'] ? strtoupper('DISPONIBLE') : strtoupper('NO DISPONIBLE'); ?>
                    </a>
                  </td>
                  <td class="text-right">
                    <div class="inline-flex gap-2">
                      <a href="/product_form?id=<?php echo $p['productoID']; ?>"
                        class="btn-outline text-[11px] !py-1 !px-2.5 hover:!border-[#FFE66D] hover:!text-[#FFE66D]">
                        <i class="fa-solid fa-pen-to-square mr-1"></i><?php echo strtoupper('Editar'); ?>
                      </a>
                      <a href="/products?action=delete&id=<?php echo $p['productoID']; ?>"
                        onclick="return confirm('¿CONFIRMAR BORRADO DE ESTE PRODUCTO?');"
                        class="btn-outline text-[11px] border-red-500/30 text-red-400 hover:bg-red-950/20 hover:border-red-500 hover:!text-white !py-1 !px-2.5">
                        <i class="fa-regular fa-trash-can mr-1"></i><?php echo strtoupper('Eliminar'); ?>
                      </a>
                    </div>
                  </td>
                </tr>
                <?php if ($tieneVariantes): ?>
                  <?php foreach ($variantesMap[$p['productoID']] as $v): ?>
                    <tr class="bg-black/20">
                      <td class="w-20">
                        <?php if (!empty($v['imagen']) && file_exists(__DIR__ . '/../assets/productos/' . $v['imagen'])): ?>
                          <img src="../assets/productos/<?php echo htmlspecialchars($v['imagen']); ?>" alt="IMG"
                            class="w-14 h-10 object-cover rounded-lg border border-white/10" />
                        <?php else: ?>
                          <div
                            class="w-14 h-10 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-[10px] uppercase text-gray-400">
                            <?php echo strtoupper('SIN IMG'); ?>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td colspan="4">
                        <div class="flex items-center justify-between py-2">
                          <div class="flex items-center gap-3">
                            <span class="text-[10px] text-gray-400 uppercase font-semibold">
                              <i
                                class="fa-solid fa-layer-group mr-1"></i><?php echo htmlspecialchars(strtoupper($v['nombre_variante'])); ?>
                            </span>
                            <span
                              class="text-[11px] text-[#FFE66D] font-bold">Bs.<?php echo number_format($v['precio'], 2); ?></span>
                          </div>
                          <a href="/products?action=toggle_variante&id=<?php echo $p['productoID']; ?>&variante_id=<?php echo $v['varianteID']; ?>"
                            class="inline-flex items-center gap-2 btn-outline text-[10px] !py-0.5 !px-2 <?php echo $v['activo'] ? 'border-green-500 text-green-300 hover:text-white' : 'border-red-500 text-red-300 hover:text-white'; ?>"
                            title="<?php echo $v['activo'] ? 'Desactivar esta variante' : 'Activar esta variante'; ?>">
                            <i class="fa-solid <?php echo $v['activo'] ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                            <?php echo $v['activo'] ? strtoupper('ACTIVA') : strtoupper('INACTIVA'); ?>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php require_once __DIR__ . '/../../theme_toggle.php'; ?>
</body>

</html>