<?php

class Producto
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtener todos los productos
     */
    public function getAll()
    {
        $stmt = $this->pdo->query('
            SELECT p.productoID, p.nombre, p.slug, p.descripcion, p.precio, p.precio_promo, p.dias_promo, 
                   p.disponible, p.imagen, p.orden_mostrado, p.fecha_modificacion,
                   c.nombre AS categoria_nombre, c.categoriaID
            FROM productos p
            LEFT JOIN categorias c ON p.categoriaID = c.categoriaID
            ORDER BY c.nombre DESC, p.orden_mostrado ASC
        ');
        return $stmt->fetchAll();
    }

    /**
     * Obtener productos disponibles para el menú público
     */
    public function getForMenu()
    {
        $stmt = $this->pdo->query("
            SELECT p.productoID, p.nombre, p.slug, p.descripcion, p.precio, p.precio_promo, p.dias_promo, 
                   p.imagen, p.disponible,
                   COALESCE(c.nombre, 'NUESTROS CLÁSICOS') AS categoria_nombre
            FROM productos p
            LEFT JOIN categorias c ON p.categoriaID = c.categoriaID
            WHERE p.disponible = 1 OR p.productoID IN (SELECT DISTINCT productoID FROM producto_variantes WHERE activo = 1)
            ORDER BY c.nombre DESC, p.orden_mostrado ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtener un producto por ID
     */
    public function getById($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM productos WHERE productoID = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Obtener un producto por slug
     */
    public function getBySlug($slug)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM productos WHERE slug = ?');
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    /**
     * Crear un nuevo producto
     */
    public function create($data)
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO productos (categoriaID, nombre, slug, descripcion, precio, precio_promo, dias_promo, 
                                   disponible, imagen, orden_mostrado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        return $stmt->execute([
            $data['categoriaID'],
            $data['nombre'],
            $data['slug'],
            $data['descripcion'],
            $data['precio'],
            $data['precio_promo'] ?? null,
            $data['dias_promo'] ?? null,
            $data['disponible'],
            $data['imagen'] ?? null,
            $data['orden_mostrado'] ?? 0
        ]);
    }

    /**
     * Actualizar un producto
     */
    public function update($id, $data)
    {
        $stmt = $this->pdo->prepare('
            UPDATE productos 
            SET categoriaID=?, nombre=?, slug=?, descripcion=?, precio=?, precio_promo=?, dias_promo=?, 
                disponible=?, imagen=?, orden_mostrado=?, fecha_modificacion=NOW()
            WHERE productoID=?
        ');
        return $stmt->execute([
            $data['categoriaID'],
            $data['nombre'],
            $data['slug'],
            $data['descripcion'],
            $data['precio'],
            $data['precio_promo'] ?? null,
            $data['dias_promo'] ?? null,
            $data['disponible'],
            $data['imagen'] ?? null,
            $data['orden_mostrado'] ?? 0,
            $id
        ]);
    }

    /**
     * Eliminar un producto
     */
    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM productos WHERE productoID = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Cambiar disponibilidad de un producto
     */
    public function toggleDisponible($id)
    {
        $stmt = $this->pdo->prepare('UPDATE productos SET disponible = NOT disponible WHERE productoID = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Verificar si un slug existe (excluyendo un ID específico)
     */
    public function slugExists($slug, $excludeId = null)
    {
        if ($excludeId) {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM productos WHERE slug = ? AND productoID != ?');
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM productos WHERE slug = ?');
            $stmt->execute([$slug]);
        }
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Generar slug único desde un nombre
     */
    public function generateSlug($nombre, $excludeId = null)
    {
        $slug = strtolower(trim($nombre));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
