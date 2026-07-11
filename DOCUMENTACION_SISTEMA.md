# DOCUMENTACIÓN DEL SISTEMA RICO POLLO

## ESTRUCTURA DE BASE DE DATOS

### Tabla: productos
```sql
- productoID (INT, PRIMARY KEY, AUTO_INCREMENT)
- categoriaID (INT, FOREIGN KEY → categorias.categoriaID)
- nombre (VARCHAR)
- slug (VARCHAR, UNIQUE) - Constraint UNIQUE agregado
- descripcion (TEXT)
- precio (DECIMAL) - Se usa si NO tiene variantes
- precio_promo (DECIMAL, NULL)
- dias_promo (VARCHAR, NULL) - Ej: "LUNES,MARTES,MIERCOLES"
- disponible (TINYINT) - 1=disponible, 0=no disponible
- imagen (VARCHAR, NULL) - Nombre del archivo de imagen (fallback para variantes)
- orden_mostrado (INT)
- fecha_modificacion (DATETIME)
```

### Tabla: categorias
```sql
- categoriaID (INT, PRIMARY KEY, AUTO_INCREMENT)
- nombre (VARCHAR) - Ej: "COMIDA", "BEBIDA"
```

### Tabla: producto_variantes
```sql
- varianteID (INT, PRIMARY KEY, AUTO_INCREMENT)
- productoID (INT, FOREIGN KEY → productos.productoID)
- nombre_variante (VARCHAR) - Ej: "ENTERO", "MEDIO", "CUARTO", "1 LITRO", "2 LITROS"
- precio (DECIMAL)
- precio_promo (DECIMAL, NULL)
- dias_promo (VARCHAR, NULL)
- activo (TINYINT) - 1=activo, 0=inactivo
- orden_mostrado (INT) - Campo agregado para controlar orden visual
- imagen (VARCHAR, NULL) - Nombre del archivo de imagen específico de la variante
- fecha_modificacion (DATETIME)
```

## RELACIONES ENTRE TABLAS

```
categorias (1) ←→ (N) productos
productos (1) ←→ (N) producto_variantes
```

- Una categoría tiene muchos productos
- Un producto pertenece a una categoría
- Un producto puede tener muchas variantes (cualquier producto)
- Una variante pertenece a un producto

## LÓGICA DEL SISTEMA

### 1. PRODUCTOS SIN VARIANTES
- Tienen precio único en `productos.precio`
- Tienen una imagen en `productos.imagen`
- Se muestran si `productos.disponible = 1`
- NO tienen filas en `producto_variantes`
- Ejemplo: Comidas simples sin tamaños

### 2. PRODUCTOS CON VARIANTES
- `productos.precio` se ignora (sirve como fallback)
- Pueden tener imagen en `productos.imagen` (opcional, como fallback para variantes)
- Se muestran si tienen variantes activas en `producto_variantes` (independiente de `productos.disponible`)
- Cada variante tiene su propio precio, promo, imagen y orden
- Ejemplos:
  - Comidas: ENTERO, MEDIO, CUARTO, ECONÓMICO
  - Bebidas: MEDIO, 1 LITRO, 2 LITROS

### 3. VARIANTES
- Pueden usarse para cualquier producto (no solo bebidas)
- Nombre libre: ENTERO, MEDIO, CUARTO, 1 LITRO, etc.
- Cada variante tiene:
  - Precio propio
  - Precio promo opcional
  - Días de promo opcionales
  - Campo `activo` (1=se muestra, 0=no se muestra)
  - Campo `orden_mostrado` (controla orden visual)
  - Imagen opcional específica

## LÓGICA DE IMÁGENES

### En menu.php (página pública)
```php
// Para variantes:
'imagen' => $v['imagen'] ?? $p['imagen']

// Lógica:
// 1. Si la variante tiene imagen → usa imagen de la variante
// 2. Si la variante NO tiene imagen → usa imagen del producto base
// 3. Si ninguna tiene imagen → muestra placeholder "SIN IMG"
```

### En product_form.php (formulario de edición)
```php
// Carga de imagen para producto normal:
- Campo: name="imagen"
- Se guarda en: productos.imagen
- Ruta: ../assets/productos/{nombre_archivo}

// Carga de imagen para variantes (dinámico):
- Campo: name="variantes_imagenes[0]", name="variantes_imagenes[1]", etc.
- Se guarda en: producto_variantes.imagen
- Ruta: ../assets/productos/{nombre_archivo}
```

### En products.php (lista de administración)
```php
// Muestra imagen del producto en tabla
// Muestra imagen de cada variante en sub-fila (para cualquier producto)
```

## CONSULTA PRINCIPAL DEL MENÚ

```sql
SELECT p.productoID, p.nombre, p.descripcion, p.precio, p.precio_promo, p.dias_promo, p.imagen, p.disponible,
       COALESCE(c.nombre, "NUESTROS CLÁSICOS") AS categoria_nombre
FROM productos p
LEFT JOIN categorias c ON p.categoriaID = c.categoriaID
WHERE p.disponible = 1 OR p.productoID IN (SELECT DISTINCT productoID FROM producto_variantes WHERE activo = 1)
ORDER BY c.nombre DESC, p.orden_mostrado ASC
```

**Explicación:**
- Trae productos disponibles (disponible=1) O productos con variantes activas
- Los productos con variantes se muestran aunque tengan disponible=0
- Los productos sin variantes solo se muestran si tienen disponible=1

## PROCESAMIENTO EN MENU.PHP

```php
// 1. Cargar productos
$productos = $pdo->query($consulta)->fetchAll();

// 2. Cargar variantes activas
$variantesMap = [];
foreach ($pdo->query('SELECT * FROM producto_variantes WHERE activo = 1') as $v) {
    $variantesMap[$v['productoID']][] = $v;
}

// 3. Agrupar por categoría
$menuGrouped = [];
foreach ($productos as $p) {
    $esBebida = strtoupper(trim($p['categoria_nombre'])) === 'BEBIDA';
    
    if ($esBebida) {
        // Solo mostrar si tiene variantes activas
        if (empty($variantesMap[$p['productoID']])) {
            continue; // Saltar bebida sin variantes
        }
        // Crear un item por cada variante activa
        foreach ($variantesMap[$p['productoID']] as $v) {
            $menuGrouped['BEBIDA'][] = [
                'productoID' => $p['productoID'],
                'nombre' => $p['nombre'],
                'precio' => $v['precio'],
                'imagen' => $v['imagen'] ?? $p['imagen'], // FALLBACK
                'esVariante' => true,
                'varianteID' => $v['varianteID'],
                'nombre_variante' => $v['nombre_variante'],
            ];
        }
    } else {
        // Producto normal: solo mostrar si disponible=1
        if ($p['disponible'] != 1) {
            continue;
        }
        $menuGrouped['COMIDA'][] = [
            'productoID' => $p['productoID'],
            'nombre' => $p['nombre'],
            'precio' => $p['precio'],
            'imagen' => $p['imagen'],
            'esVariante' => false,
        ];
    }
}
```

## LÓGICA DE PRECIOS ACTIVOS (PROMOS)

```php
// Función DB::obtenerPrecioActivo($producto)
// - Si tiene precio_promo y dias_promo configurados
// - Verifica si hoy está en los días de promo
// - Si sí → retorna precio_promo
// - Si no → retorna precio normal
```

## ARCHIVOS DEL SISTEMA

- **public/menu.php** - Página pública del menú
- **public/products.php** - Lista de productos (admin)
- **public/product_form.php** - Crear/editar productos (admin)
- **src/db.php** - Conexión a BD y funciones auxiliares
- **assets/productos/** - Carpeta de imágenes de productos

## DATOS DE EJEMPLO ACTUALES

```
PRODUCTOS:
- ID 2: ARROZ CHAUFA (COMIDA, disponible=1, tiene imagen)
- ID 4: MILANESA (COMIDA, disponible=1, tiene imagen)
- ID 3: COCA-COLA (BEBIDA, disponible=0, sin imagen)

VARIANTES:
- VarianteID 1: MEDIO (ProductoID 3, activo=1, sin imagen)
- VarianteID 2: 1 LITRO (ProductoID 3, activo=0, sin imagen)
- VarianteID 3: 2 LITROS (ProductoID 3, activo=1, sin imagen)
```

## PROBLEMAS IDENTIFICADOS Y CORREGIDOS

1. **menu.php línea 11**: Comillas dobles en SQL → cambiadas a comillas simples
2. **product_form.php**: Al editar variante sin subir nueva imagen, se perdía la imagen existente → agregada lógica para mantener imagen anterior
3. **products.php**: No mostraba imágenes de variantes → agregada visualización en tabla

## REQUERIMIENTO ACTUAL

El usuario quiere que cada variante de bebida tenga su propia imagen específica, y que el menú muestre:
- Si la variante tiene imagen → mostrar imagen de la variante
- Si la variante no tiene imagen → mostrar imagen del producto base (fallback)
- Si ninguna tiene imagen → mostrar placeholder
