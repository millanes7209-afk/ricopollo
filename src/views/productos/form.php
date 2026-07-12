<?php
// Variables pasadas por el controlador:
// $producto  - producto actual (null si es nuevo)
// $variantes - array de variantes existentes
// $categorias - array de categorías disponibles
// $error     - mensaje de error si existe
// $id        - ID del producto (null si es nuevo)
$error = $error ?? null;
$id    = $id ?? null;
$cats  = $categorias ?? [];
?>
<!doctype html>
<html lang="es" class="dark-mode">

<head>
  <meta charset="utf-8">
  <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo $producto ? 'EDITAR' : 'CREAR'; ?> PRODUCTO - RICO POLLO</title>
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
  <div class="max-w-2xl w-full mx-auto">
    <!-- Header / Logo -->
    <div class="flex justify-center mb-6">
      <div class="w-36 h-20">
        <img src="../assets/logo.svg" alt="LOGO" class="w-full h-full object-contain">
      </div>
    </div>

    <!-- Product Form Card -->
    <div class="glass-card p-6 md:p-8">
      <h2 class="text-lg font-extrabold tracking-wide uppercase text-white pb-3 border-b border-white/5 mb-6">
        <i class="fa-solid fa-cookie-bite text-[#FFE66D] mr-2"></i>
        <?php echo $id ? 'EDITAR PRODUCTO' : 'CREAR PRODUCTO'; ?>
      </h2>

      <?php if ($error): ?>
        <div
          class="mb-5 p-3.5 bg-red-950/40 border border-red-500/50 rounded-xl text-red-200 text-sm font-semibold flex items-center gap-3">
          <i class="fa-solid fa-circle-exclamation text-red-500"></i>
          <span><?php echo $error; ?></span>
        </div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="space-y-4">
        <!-- 1. CATEGORÍA (MANDATO DEL USUARIO - DEBE SER LO PRIMERO EN VERSE) -->
        <div>
          <label
            class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5"><?php echo strtoupper('Categoría'); ?></label>
          <div class="relative">
            <span class="absolute left-3.5 top-3.5 text-gray-500">
              <i class="fa-solid fa-list"></i>
            </span>
            <select name="categoriaID" id="categoriaID" required onchange="handleCategoryChange()"
              class="form-input pl-10 focus:bg-gray-900">
              <option value="" disabled <?php echo (!isset($producto) || empty($producto['categoriaID'])) ? 'selected' : ''; ?> style="background: var(--color-card); color: var(--color-text);">
                <?php echo strtoupper('-- SELECCIONE CATEGORÍA --'); ?>
              </option>
              <?php foreach ($cats as $c): ?>
                <option value="<?php echo $c['categoriaID']; ?>" data-nombre="<?php echo strtoupper($c['nombre']); ?>"
                  <?php echo (isset($producto) && $producto['categoriaID'] == $c['categoriaID']) ? 'selected' : ''; ?>
                  style="background: var(--color-card); color: var(--color-text);">
                  <?php echo htmlspecialchars(strtoupper($c['nombre'])); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- 2. NOMBRE DEL PRODUCTO -->
        <div>
          <label
            class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5"><?php echo strtoupper('Nombre Del Producto'); ?></label>
          <div class="relative">
            <span class="absolute left-3.5 top-3.5 text-gray-500">
              <i class="fa-solid fa-utensils"></i>
            </span>
            <input id="input-nombre" name="nombre" required
              value="<?php echo htmlspecialchars(strtoupper($producto['nombre'] ?? '')); ?>"
              oninput="this.value = this.value.toUpperCase();" class="form-input pl-10 uppercase"
              placeholder="EJ: CUARTO DE POLLO AL CARBÓN" />
          </div>
        </div>

        <div>
          <label
            class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5"><?php echo strtoupper('Imagen Del Producto'); ?></label>
          <input type="file" name="imagen" accept="image/png,image/jpeg,image/webp,image/gif"
            class="form-input" />
          <?php if (!empty($imagenActual) && file_exists(__DIR__ . '/../assets/productos/' . $imagenActual)): ?>
            <div class="mt-4 flex items-center gap-3">
              <img src="../assets/productos/<?php echo htmlspecialchars($imagenActual); ?>"
                alt="IMAGEN ACTUAL" class="w-24 h-24 object-cover rounded-xl border border-white/10" />
              <span class="text-[11px] uppercase text-gray-400 font-semibold"><?php echo strtoupper('Imagen actual'); ?></span>
            </div>
          <?php endif; ?>
        </div>

        <!-- DESCRIPCIÓN -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Descripción</label>
          <textarea name="descripcion" class="form-input text-sm" rows="3" placeholder="EJ: DELICIOSO POLLO A LA BRASA CON PAPAS..."><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <!-- ORDEN DE VISUALIZACIÓN -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Orden de Visualización</label>
            <input type="number" name="orden_mostrado" value="<?php echo htmlspecialchars($producto['orden_mostrado'] ?? '0'); ?>" class="form-input text-sm" min="0" />
            <p class="text-[10px] text-gray-500 mt-1">Más bajo aparece primero.</p>
          </div>

          <!-- DISPONIBLE -->
          <div class="flex flex-col items-center justify-center">
            <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Disponible en Menú</span>
            <label class="inline-flex items-center cursor-pointer select-none">
              <input type="checkbox" name="disponible" value="1" class="sr-only peer" <?php echo (!isset($producto) || $producto['disponible'] == 1) ? 'checked' : ''; ?>>
              <div class="w-11 h-6 bg-slate-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500 relative"></div>
            </label>
          </div>
        </div>

        <!-- 3. TOGGLE DE VARIANTES -->
        <div class="border-t border-white/5 pt-4">
          <label class="inline-flex items-center cursor-pointer select-none">
            <input type="checkbox" id="tiene_variantes" name="tiene_variantes" value="1" class="sr-only peer" onchange="toggleVariantesSection()"
              <?php echo (!empty($variantes)) ? 'checked' : ''; ?>>
            <div class="w-11 h-6 bg-slate-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#FFE66D] relative"></div>
            <span class="ml-3 text-sm font-bold uppercase tracking-wider text-gray-300">¿ESTE PRODUCTO TIENE VARIANTES?</span>
          </label>
        </div>

        <!-- 4. SECCIÓN PRECIO GENERAL Y PROMOCIÓN -->
        <div id="general-price-section" class="space-y-4">
          <!-- Precio de Venta -->
          <div>
            <label
              class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5"><?php echo strtoupper('Precio de Venta (Bs.)'); ?></label>
            <div class="relative">
              <span class="absolute left-3.5 top-3.5 text-[#FFE66D] font-bold text-sm">Bs.</span>
              <input id="main-precio" name="precio" type="number" step="0.01" value="<?php echo htmlspecialchars($producto['precio'] ?? '0.00'); ?>"
                class="form-input pl-14" placeholder="0.00" />
            </div>
            <p class="text-[10px] text-gray-500 mt-1">
              <?php echo strtoupper('Este precio se usa si el producto NO tiene variantes. Si tiene variantes, se ignora.'); ?>
            </p>
          </div>

          <!-- Promociones Especiales Semanales -->
          <div class="border-t border-white/5 pt-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#FFE66D] mb-4">
              <i class="fa-solid fa-tags mr-2"></i><?php echo strtoupper('Promoción Semanal (Opcional)'); ?>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label
                  class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5"><?php echo strtoupper('Precio de Promoción (Bs.)'); ?></label>
                <div class="relative">
                  <span class="absolute left-3.5 top-3.5 text-green-400 font-bold text-sm">Bs.</span>
                  <input name="precio_promo" type="number" step="0.01"
                    value="<?php echo htmlspecialchars($producto['precio_promo'] ?? ''); ?>" class="form-input pl-14"
                    placeholder="EJ: 35.00" />
                </div>
              </div>
              <div>
                <span
                  class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5"><?php echo strtoupper('Días de Aplicación'); ?></span>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                  <?php
                  $diasArr = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES'];
                  $diasActuales = !empty($producto['dias_promo']) ? array_map('trim', explode(',', $producto['dias_promo'])) : [];
                  foreach ($diasArr as $dia):
                    $checked = in_array($dia, $diasActuales) ? 'checked' : '';
                    ?>
                    <label class="inline-flex items-center gap-1.5 text-xs text-gray-300 cursor-pointer select-none">
                      <input type="checkbox" name="dias_promo[]" value="<?php echo $dia; ?>" <?php echo $checked; ?>
                        class="rounded bg-black/60 border-white/10 text-[#FFE66D] focus:ring-0 w-3.5 h-3.5" />
                      <span><?php echo $dia; ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 5. SECCIÓN DE VARIANTES (Para cualquier producto) -->
        <div id="variantes-section" class="space-y-5 pt-4 border-t border-white/5">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#FFE66D]">
              <i class="fa-solid fa-layer-group mr-2"></i><?php echo strtoupper('Variantes del Producto'); ?>
            </h3>
            <button type="button" onclick="addVariante()" class="btn-outline text-[10px] !py-1 !px-2">
              <i class="fa-solid fa-plus mr-1"></i><?php echo strtoupper('AGREGAR VARIANTE'); ?>
            </button>
          </div>
          <p class="text-[10px] text-gray-400">
            <?php echo strtoupper('Agrega variantes como: ENTERO, MEDIO, CUARTO (comidas) o MEDIO, 1 LITRO, 2 LITROS (bebidas). Si no agregas variantes, se usa el precio único.'); ?>
          </p>

          <div id="variantes-container" class="space-y-3">
            <?php
            if (!empty($variantes)):
              foreach ($variantes as $index => $v):
                $valId = $v['varianteID'];
                $valNombre = htmlspecialchars($v['nombre_variante']);
                $valPrecio = htmlspecialchars($v['precio']);
                $valPrecioPromo = htmlspecialchars($v['precio_promo'] ?? '');
                $valOrden = htmlspecialchars($v['orden_mostrado'] ?? $index + 1);
                $valActivo = (int) $v['activo'];
                $valImagen = htmlspecialchars($v['imagen'] ?? '');
                $diasVar = !empty($v['dias_promo']) ? array_map('trim', explode(',', $v['dias_promo'])) : [];
            ?>
              <div class="variante-item glass-card !bg-black/25 p-4 border border-white/5 rounded-xl space-y-3 relative text-left">
                <div class="flex justify-between items-start gap-3">
                  <div class="flex-1 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                      <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">NOMBRE DE LA VARIANTE</label>
                        <input type="text" name="variantes[<?php echo $index; ?>][nombre]" value="<?php echo $valNombre; ?>"
                          class="form-input !py-1 text-sm" placeholder="EJ: ENTERO, MEDIO, 1 LITRO" required />
                      </div>
                      <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">ORDEN</label>
                        <input type="number" name="variantes[<?php echo $index; ?>][orden]" value="<?php echo $valOrden; ?>"
                          class="form-input !py-1 text-sm" min="1" />
                      </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                      <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">PRECIO (Bs.)</label>
                        <div class="relative">
                          <span class="absolute left-2 top-1 text-xs text-[#FFE66D] font-bold">Bs.</span>
                          <input type="number" step="0.01" name="variantes[<?php echo $index; ?>][precio]" value="<?php echo $valPrecio; ?>"
                            class="form-input pl-8 !py-1 text-sm" required />
                        </div>
                      </div>
                      <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">PRECIO PROMO (Bs.)</label>
                        <div class="relative">
                          <span class="absolute left-2 top-1 text-xs text-green-400 font-bold">Bs.</span>
                          <input type="number" step="0.01" name="variantes[<?php echo $index; ?>][precio_promo]" value="<?php echo $valPrecioPromo; ?>"
                            class="form-input pl-8 !py-1 text-sm" />
                        </div>
                      </div>
                    </div>
                    <div>
                      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">DÍAS DE PROMO</label>
                      <div class="grid grid-cols-5 gap-1">
                        <?php foreach ($diasArr as $dia): ?>
                          <label class="inline-flex items-center gap-0.5 text-[9px] text-gray-300 cursor-pointer">
                            <input type="checkbox" name="variantes[<?php echo $index; ?>][dias_promo][]" value="<?php echo $dia; ?>"
                              <?php echo in_array($dia, $diasVar) ? 'checked' : ''; ?>
                              class="rounded bg-black/60 border-white/10 text-[#FFE66D] focus:ring-0 w-3 h-3" />
                            <span><?php echo substr($dia, 0, 3); ?></span>
                          </label>
                        <?php endforeach; ?>
                      </div>
                    </div>
                    <div>
                      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">IMAGEN DE LA VARIANTE</label>
                      <input type="file" name="variantes_imagenes[<?php echo $index; ?>]" accept="image/png,image/jpeg,image/webp,image/gif"
                        class="form-input !py-1 text-xs" />
                      <?php if (!empty($valImagen) && file_exists(__DIR__ . '/../assets/productos/' . $valImagen)): ?>
                        <div class="mt-2 flex items-center gap-2">
                          <img src="../assets/productos/<?php echo $valImagen; ?>"
                            alt="IMAGEN ACTUAL" class="w-12 h-12 object-cover rounded-lg border border-white/10" />
                          <span class="text-[9px] uppercase text-gray-400 font-semibold">Imagen actual</span>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="flex flex-col items-center gap-2">
                    <label class="inline-flex items-center cursor-pointer select-none">
                      <input type="checkbox" name="variantes[<?php echo $index; ?>][activo]" value="1" class="sr-only peer"
                        <?php echo $valActivo ? 'checked' : ''; ?>>
                      <div
                        class="w-8 h-4 bg-slate-200 dark:bg-gray-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-green-500 peer-checked:after:bg-white relative font-normal">
                      </div>
                    </label>
                    <span class="text-[9px] text-gray-400 uppercase">Activa</span>
                    <button type="button" onclick="removeVariante(this)" class="text-red-400 hover:text-red-300 text-[9px] uppercase font-bold mt-2">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </div>
                </div>
                <input type="hidden" name="variantes[<?php echo $index; ?>][varianteID]" value="<?php echo $valId; ?>">
              </div>
            <?php endforeach;
            endif;
            ?>
          </div>
        </div>

        <!-- Action buttons -->
        <div class="flex items-center justify-between pt-6 border-t border-white/5">
          <a href="/products" class="btn-outline text-xs">
            <i class="fa-solid fa-xmark mr-1"></i><?php echo strtoupper('CANCELAR'); ?>
          </a>
          <button type="submit" class="btn-primary text-xs">
            <i
              class="fa-solid fa-floppy-disk mr-1"></i><?php echo strtoupper($id ? 'GUARDAR CAMBIOS' : 'CREAR PRODUCTO'); ?>
          </button>
        </div>
      </form>
    </div>
  </div>
  <?php require_once __DIR__ . '/../../theme_toggle.php'; ?>

  <script>
    let varianteIndex = <?php echo !empty($variantes) ? count($variantes) : 0; ?>;

    function toggleVariantesSection() {
        const hasVariants = document.getElementById('tiene_variantes').checked;
        const generalSection = document.getElementById('general-price-section');
        const variantesSection = document.getElementById('variantes-section');
        const inputPrecioGeneral = document.getElementById('main-precio');

        if (hasVariants) {
            generalSection.classList.add('hidden');
            variantesSection.classList.remove('hidden');
            if(inputPrecioGeneral) inputPrecioGeneral.removeAttribute('required');
        } else {
            generalSection.classList.remove('hidden');
            variantesSection.classList.add('hidden');
            if(inputPrecioGeneral) inputPrecioGeneral.setAttribute('required', 'required');
        }
    }

    document.addEventListener('DOMContentLoaded', toggleVariantesSection);

    function addVariante() {
      const container = document.getElementById('variantes-container');
      const index = varianteIndex++;
      
      const html = `
        <div class="variante-item glass-card !bg-black/25 p-4 border border-white/5 rounded-xl space-y-3 relative text-left">
          <div class="flex justify-between items-start gap-3">
            <div class="flex-1 space-y-3">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">NOMBRE DE LA VARIANTE</label>
                  <input type="text" name="variantes[${index}][nombre]"
                    class="form-input !py-1 text-sm" placeholder="EJ: ENTERO, MEDIO, 1 LITRO" required />
                </div>
                <div>
                  <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">ORDEN</label>
                  <input type="number" name="variantes[${index}][orden]" value="${index + 1}"
                    class="form-input !py-1 text-sm" min="1" />
                </div>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">PRECIO (Bs.)</label>
                  <div class="relative">
                    <span class="absolute left-2 top-1 text-xs text-[#FFE66D] font-bold">Bs.</span>
                    <input type="number" step="0.01" name="variantes[${index}][precio]"
                      class="form-input pl-8 !py-1 text-sm" required />
                  </div>
                </div>
                <div>
                  <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">PRECIO PROMO (Bs.)</label>
                  <div class="relative">
                    <span class="absolute left-2 top-1 text-xs text-green-400 font-bold">Bs.</span>
                    <input type="number" step="0.01" name="variantes[${index}][precio_promo]"
                      class="form-input pl-8 !py-1 text-sm" />
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">DÍAS DE PROMO</label>
                <div class="grid grid-cols-5 gap-1">
                  <label class="inline-flex items-center gap-0.5 text-[9px] text-gray-300 cursor-pointer">
                    <input type="checkbox" name="variantes[${index}][dias_promo][]" value="LUNES"
                      class="rounded bg-black/60 border-white/10 text-[#FFE66D] focus:ring-0 w-3 h-3" />
                    <span>LUN</span>
                  </label>
                  <label class="inline-flex items-center gap-0.5 text-[9px] text-gray-300 cursor-pointer">
                    <input type="checkbox" name="variantes[${index}][dias_promo][]" value="MARTES"
                      class="rounded bg-black/60 border-white/10 text-[#FFE66D] focus:ring-0 w-3 h-3" />
                    <span>MAR</span>
                  </label>
                  <label class="inline-flex items-center gap-0.5 text-[9px] text-gray-300 cursor-pointer">
                    <input type="checkbox" name="variantes[${index}][dias_promo][]" value="MIERCOLES"
                      class="rounded bg-black/60 border-white/10 text-[#FFE66D] focus:ring-0 w-3 h-3" />
                    <span>MIE</span>
                  </label>
                  <label class="inline-flex items-center gap-0.5 text-[9px] text-gray-300 cursor-pointer">
                    <input type="checkbox" name="variantes[${index}][dias_promo][]" value="JUEVES"
                      class="rounded bg-black/60 border-white/10 text-[#FFE66D] focus:ring-0 w-3 h-3" />
                    <span>JUE</span>
                  </label>
                  <label class="inline-flex items-center gap-0.5 text-[9px] text-gray-300 cursor-pointer">
                    <input type="checkbox" name="variantes[${index}][dias_promo][]" value="VIERNES"
                      class="rounded bg-black/60 border-white/10 text-[#FFE66D] focus:ring-0 w-3 h-3" />
                    <span>VIE</span>
                  </label>
                </div>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">IMAGEN DE LA VARIANTE</label>
                <input type="file" name="variantes_imagenes[${index}]" accept="image/png,image/jpeg,image/webp,image/gif"
                  class="form-input !py-1 text-xs" />
              </div>
            </div>
            <div class="flex flex-col items-center gap-2">
              <label class="inline-flex items-center cursor-pointer select-none">
                <input type="checkbox" name="variantes[${index}][activo]" value="1" class="sr-only peer" checked>
                <div
                  class="w-8 h-4 bg-slate-200 dark:bg-gray-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-green-500 peer-checked:after:bg-white relative font-normal">
                </div>
              </label>
              <span class="text-[9px] text-gray-400 uppercase">Activa</span>
              <button type="button" onclick="removeVariante(this)" class="text-red-400 hover:text-red-300 text-[9px] uppercase font-bold mt-2">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </div>
          <input type="hidden" name="variantes[${index}][varianteID]" value="">
        </div>
      `;
      
      container.insertAdjacentHTML('beforeend', html);
    }

    function removeVariante(btn) {
      const item = btn.closest('.variante-item');
      const varianteID = item.querySelector('input[name$="[varianteID]"]').value;
      
      if (varianteID) {
        // Si tiene ID, marcar para eliminación (no eliminar del DOM para que no se pierda el índice)
        item.style.display = 'none';
        item.querySelector('input[name$="[nombre]"]').removeAttribute('required');
      } else {
        // Si es nueva, eliminar del DOM
        item.remove();
      }
    }
  </script>
</body>

</html>