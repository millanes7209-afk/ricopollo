<?php

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Variante.php';

class ProductoController
{
    private $productoModel;
    private $varianteModel;
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->productoModel = new Producto($pdo);
        $this->varianteModel = new Variante($pdo);
    }

    /**
     * Listar todos los productos (admin)
     */
    public function index()
    {
        $productos = $this->productoModel->getAll();
        $variantes = $this->varianteModel->getAll();

        // Agrupar variantes por producto
        $variantesMap = [];
        foreach ($variantes as $v) {
            $variantesMap[$v['productoID']][] = $v;
        }

        return [
            'productos' => $productos,
            'variantesMap' => $variantesMap
        ];
    }

    /**
     * Mostrar formulario de creación/edición
     */
    public function form($id = null)
    {
        $producto = null;
        $variantes = [];
        $categorias = $this->getCategorias();

        if ($id) {
            $producto = $this->productoModel->getById($id);
            if ($producto) {
                $variantes = $this->varianteModel->getByProductoId($id);
            }
        }

        return [
            'producto' => $producto,
            'variantes' => $variantes,
            'categorias' => $categorias
        ];
    }

    /**
     * Guardar producto (crear o actualizar)
     */
    public function save($data, $files = null)
    {
        $id = $data['id'] ?? null;
        $tieneVariantes = !empty($data['tiene_variantes']);

        // Procesar imagen principal
        $imagenNombre = $this->procesarImagenPrincipal($files, $id);

        // Generar slug
        $slug = $this->productoModel->generateSlug($data['nombre'], $id);

        // Convertir dias_promo de array (checkboxes) a string CSV
        $diasPromo = null;
        if (!empty($data['dias_promo'])) {
            $diasPromo = is_array($data['dias_promo'])
                ? implode(',', array_map('strtoupper', $data['dias_promo']))
                : strtoupper($data['dias_promo']);
        }

        // Preparar datos del producto
        $productoData = [
            'categoriaID' => $data['categoriaID'],
            'nombre' => $data['nombre'],
            'slug' => $slug,
            'descripcion' => $data['descripcion'] ?? '',
            'precio' => $tieneVariantes ? 0 : ($data['precio'] ?? 0),
            'precio_promo' => !empty($data['precio_promo']) ? floatval($data['precio_promo']) : null,
            'dias_promo' => $diasPromo,
            'disponible' => isset($data['disponible']) ? 1 : 0,
            'imagen' => $imagenNombre,
            'orden_mostrado' => (int) ($data['orden_mostrado'] ?? 0)
        ];

        $this->pdo->beginTransaction();
        try {
            if ($id) {
                // Actualizar producto existente
                $this->productoModel->update($id, $productoData);
                $productoId = $id;
            } else {
                // Crear nuevo producto
                $this->productoModel->create($productoData);
                $productoId = $this->pdo->lastInsertId();
            }

            // Procesar variantes
            if ($tieneVariantes) {
                $this->guardarVariantes($productoId, $data['variantes'] ?? [], $files);
            } else {
                // Eliminar todas las variantes si no tiene
                $this->varianteModel->deleteByProductoId($productoId);
            }

            $this->pdo->commit();
            return ['success' => true, 'productoId' => $productoId];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Eliminar producto
     */
    public function delete($id)
    {
        $this->pdo->beginTransaction();
        try {
            // Eliminar variantes primero
            $this->varianteModel->deleteByProductoId($id);
            // Eliminar producto
            $this->productoModel->delete($id);
            $this->pdo->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Toggle disponibilidad
     */
    public function toggleDisponible($id)
    {
        $this->productoModel->toggleDisponible($id);
        return ['success' => true];
    }

    /**
     * Toggle activo de variante
     */
    public function toggleVarianteActivo($varianteId)
    {
        $this->varianteModel->toggleActivo($varianteId);
        return ['success' => true];
    }

    /**
     * Obtener categorías
     */
    private function getCategorias()
    {
        $stmt = $this->pdo->query('SELECT * FROM categorias ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }

    /**
     * Procesar imagen principal del producto
     * En Vercel el filesystem es read-only, así que el upload se ignora silenciosamente.
     */
    private function procesarImagenPrincipal($files, $id)
    {
        if (!$files || empty($files['imagen']['name'])) {
            // Sin nueva imagen: mantener la existente
            if ($id) {
                $producto = $this->productoModel->getById($id);
                return $producto['imagen'] ?? null;
            }
            return null;
        }

        $file = $files['imagen'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            // Sin nueva imagen válida: mantener la existente
            if ($id) {
                $producto = $this->productoModel->getById($id);
                return $producto['imagen'] ?? null;
            }
            return null;
        }

        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mimeType = mime_content_type($file['tmp_name']) ?: '';
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($fileExt, $allowedExt) || !in_array($mimeType, $allowedMime)) {
            return null;
        }

        // Detectar directorio de uploads (Vercel usa /tmp, local usa assets/productos)
        $localDir = __DIR__ . '/../../assets/productos';
        if (is_writable($localDir) || (!is_dir($localDir) && @mkdir($localDir, 0755, true))) {
            $uploadDir = $localDir;
        } elseif (is_writable('/tmp')) {
            $uploadDir = '/tmp';
        } else {
            // Filesystem completamente read-only: conservar imagen existente
            if ($id) {
                $producto = $this->productoModel->getById($id);
                return $producto['imagen'] ?? null;
            }
            return null;
        }

        // Eliminar imagen anterior si subimos a assets locales
        if ($uploadDir === $localDir && $id) {
            $producto = $this->productoModel->getById($id);
            if ($producto && $producto['imagen']) {
                $oldFile = $uploadDir . '/' . $producto['imagen'];
                if (is_file($oldFile)) {
                    @unlink($oldFile);
                }
            }
        }

        $nombreArchivo = 'producto_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $fileExt;
        $targetPath = $uploadDir . '/' . $nombreArchivo;

        if (@move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Solo guardamos el nombre si el archivo está en el directorio permanente
            return ($uploadDir === $localDir) ? $nombreArchivo : null;
        }

        // Si falló el move, conservar imagen existente
        if ($id) {
            $producto = $this->productoModel->getById($id);
            return $producto['imagen'] ?? null;
        }
        return null;
    }

    /**
     * Guardar variantes de un producto
     */
    private function guardarVariantes($productoId, $variantesData, $files)
    {
        $uploadDir = __DIR__ . '/../../assets/productos';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Obtener IDs de variantes enviadas para eliminar las que no estén
        $varianteIDsEnviados = [];
        foreach ($variantesData as $v) {
            if (!empty($v['varianteID'])) {
                $varianteIDsEnviados[] = (int) $v['varianteID'];
            }
        }

        // Eliminar variantes que no fueron enviadas
        $this->varianteModel->deleteExcept($productoId, $varianteIDsEnviados);

        // Procesar cada variante
        foreach ($variantesData as $index => $v) {
            $nombreVariante = strtoupper(trim($v['nombre'] ?? ''));
            if (empty($nombreVariante))
                continue;

            // Procesar imagen de variante
            $imagenNombre = null;
            if ($files && isset($files['variantes_imagenes'][$index]) && !empty($files['variantes_imagenes'][$index]['name'])) {
                $imagenNombre = $this->procesarImagenVariante($files['variantes_imagenes'][$index], $uploadDir, $v['varianteID'] ?? null);
            } elseif (!empty($v['varianteID'])) {
                // Mantener imagen existente
                $varianteExistente = $this->varianteModel->getById($v['varianteID']);
                if ($varianteExistente) {
                    $imagenNombre = $varianteExistente['imagen'];
                }
            }

            $varianteData = [
                'productoID' => $productoId,
                'nombre_variante' => $nombreVariante,
                'precio' => floatval($v['precio'] ?? 0),
                'precio_promo' => !empty($v['precio_promo']) ? floatval($v['precio_promo']) : null,
                'dias_promo' => isset($v['dias_promo']) && is_array($v['dias_promo']) ? implode(',', array_map('strtoupper', $v['dias_promo'])) : null,
                'activo' => isset($v['activo']) ? 1 : 0,
                'orden_mostrado' => !empty($v['orden']) ? (int) $v['orden'] : $index + 1,
                'imagen' => $imagenNombre
            ];

            if (!empty($v['varianteID'])) {
                $this->varianteModel->update((int) $v['varianteID'], $varianteData);
            } else {
                $this->varianteModel->create($varianteData);
            }
        }
    }

    /**
     * Procesar imagen de variante
     */
    private function procesarImagenVariante($file, $uploadDir, $varianteId)
    {
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mimeType = mime_content_type($file['tmp_name']) ?: '';
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if ($file['error'] !== UPLOAD_ERR_OK || !in_array($fileExt, $allowedExt) || !in_array($mimeType, $allowedMime)) {
            return null;
        }

        // Eliminar imagen anterior si existe
        if ($varianteId) {
            $variante = $this->varianteModel->getById($varianteId);
            if ($variante && $variante['imagen']) {
                $oldFile = $uploadDir . '/' . $variante['imagen'];
                if (is_file($oldFile)) {
                    unlink($oldFile);
                }
            }
        }

        $nombreArchivo = 'variante_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $fileExt;
        $targetPath = $uploadDir . '/' . $nombreArchivo;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $nombreArchivo;
        }

        return null;
    }
}
