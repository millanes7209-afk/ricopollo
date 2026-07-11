<?php

class Variante
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtener todas las variantes
     */
    public function getAll()
    {
        $stmt = $this->pdo->query('SELECT * FROM producto_variantes ORDER BY orden_mostrado ASC, varianteID ASC');
        return $stmt->fetchAll();
    }

    /**
     * Obtener variantes activas
     */
    public function getActive()
    {
        $stmt = $this->pdo->query('SELECT * FROM producto_variantes WHERE activo = 1 ORDER BY orden_mostrado ASC, varianteID ASC');
        return $stmt->fetchAll();
    }

    /**
     * Obtener variantes por producto ID
     */
    public function getByProductoId($productoId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM producto_variantes WHERE productoID = ? ORDER BY orden_mostrado ASC, varianteID ASC');
        $stmt->execute([$productoId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener variantes activas por producto ID
     */
    public function getActiveByProductoId($productoId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM producto_variantes WHERE productoID = ? AND activo = 1 ORDER BY orden_mostrado ASC, varianteID ASC');
        $stmt->execute([$productoId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener una variante por ID
     */
    public function getById($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM producto_variantes WHERE varianteID = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Crear una nueva variante
     */
    public function create($data)
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO producto_variantes (productoID, nombre_variante, precio, precio_promo, dias_promo, 
                                            activo, orden_mostrado, imagen)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        return $stmt->execute([
            $data['productoID'],
            strtoupper(trim($data['nombre_variante'])),
            $data['precio'],
            $data['precio_promo'] ?? null,
            $data['dias_promo'] ?? null,
            $data['activo'] ?? 1,
            $data['orden_mostrado'] ?? 0,
            $data['imagen'] ?? null
        ]);
    }

    /**
     * Actualizar una variante
     */
    public function update($id, $data)
    {
        $stmt = $this->pdo->prepare('
            UPDATE producto_variantes 
            SET nombre_variante=?, precio=?, precio_promo=?, dias_promo=?, activo=?, orden_mostrado=?, imagen=?, 
                fecha_modificacion=NOW()
            WHERE varianteID=?
        ');
        return $stmt->execute([
            strtoupper(trim($data['nombre_variante'])),
            $data['precio'],
            $data['precio_promo'] ?? null,
            $data['dias_promo'] ?? null,
            $data['activo'] ?? 1,
            $data['orden_mostrado'] ?? 0,
            $data['imagen'] ?? null,
            $id
        ]);
    }

    /**
     * Eliminar una variante
     */
    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM producto_variantes WHERE varianteID = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Eliminar todas las variantes de un producto
     */
    public function deleteByProductoId($productoId)
    {
        $stmt = $this->pdo->prepare('DELETE FROM producto_variantes WHERE productoID = ?');
        return $stmt->execute([$productoId]);
    }

    /**
     * Eliminar variantes de un producto excepto las especificadas
     */
    public function deleteExcept($productoId, $keepIds)
    {
        if (empty($keepIds)) {
            return $this->deleteByProductoId($productoId);
        }
        
        $placeholders = implode(',', array_fill(0, count($keepIds), '?'));
        $stmt = $this->pdo->prepare("DELETE FROM producto_variantes WHERE productoID = ? AND varianteID NOT IN ($placeholders)");
        return $stmt->execute(array_merge([$productoId], $keepIds));
    }

    /**
     * Cambiar estado activo de una variante
     */
    public function toggleActivo($id)
    {
        $stmt = $this->pdo->prepare('UPDATE producto_variantes SET activo = NOT activo WHERE varianteID = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Obtener variantes agrupadas por producto (para menú)
     */
    public function getGroupedByProducto()
    {
        $variantes = $this->getActive();
        $grouped = [];
        foreach ($variantes as $v) {
            $grouped[$v['productoID']][] = $v;
        }
        return $grouped;
    }
}
