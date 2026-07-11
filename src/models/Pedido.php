<?php

class Pedido
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtener todos los pedidos
     */
    public function getAll()
    {
        $stmt = $this->pdo->query('
            SELECT p.*, 
                   (SELECT COUNT(*) FROM pedido_items WHERE pedidoID = p.pedidoID) as items_count
            FROM pedidos p
            ORDER BY p.fecha_creacion DESC
        ');
        return $stmt->fetchAll();
    }

    /**
     * Obtener pedidos pendientes
     */
    public function getPending()
    {
        $stmt = $this->pdo->query("
            SELECT p.*, 
                   (SELECT COUNT(*) FROM pedido_items WHERE pedidoID = p.pedidoID) as items_count
            FROM pedidos p
            WHERE p.estado = 'PENDIENTE'
            ORDER BY p.fecha_creacion ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtener un pedido por ID
     */
    public function getById($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM pedidos WHERE pedidoID = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Obtener un pedido por número de pedido
     */
    public function getByNumero($numero)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM pedidos WHERE numero_pedido = ?');
        $stmt->execute([$numero]);
        return $stmt->fetch();
    }

    /**
     * Crear un nuevo pedido
     */
    public function create($data)
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO pedidos (numero_pedido, cliente_nombre, cliente_telefono, tipo_pedido, numero_mesa, 
                                 direccion_entrega, nota, estado, estado_pago, metodo_pago, monto_total, 
                                 aceptado_en, impreso_en)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        return $stmt->execute([
            $data['numero_pedido'],
            $data['cliente_nombre'] ?? null,
            $data['cliente_telefono'] ?? null,
            $data['tipo_pedido'],
            $data['numero_mesa'] ?? null,
            $data['direccion_entrega'] ?? null,
            $data['nota'] ?? null,
            $data['estado'] ?? 'PENDIENTE',
            $data['estado_pago'] ?? 'PENDIENTE',
            $data['metodo_pago'] ?? null,
            $data['monto_total'],
            $data['aceptado_en'] ?? null,
            $data['impreso_en'] ?? null
        ]);
    }

    /**
     * Actualizar un pedido
     */
    public function update($id, $data)
    {
        $stmt = $this->pdo->prepare('
            UPDATE pedidos 
            SET cliente_nombre=?, cliente_telefono=?, tipo_pedido=?, numero_mesa=?, 
                direccion_entrega=?, nota=?, estado=?, estado_pago=?, metodo_pago=?, monto_total=?, 
                aceptado_en=?, impreso_en=?, fecha_modificacion=NOW()
            WHERE pedidoID=?
        ');
        return $stmt->execute([
            $data['cliente_nombre'] ?? null,
            $data['cliente_telefono'] ?? null,
            $data['tipo_pedido'],
            $data['numero_mesa'] ?? null,
            $data['direccion_entrega'] ?? null,
            $data['nota'] ?? null,
            $data['estado'],
            $data['estado_pago'] ?? null,
            $data['metodo_pago'] ?? null,
            $data['monto_total'],
            $data['aceptado_en'] ?? null,
            $data['impreso_en'] ?? null,
            $id
        ]);
    }

    /**
     * Actualizar estado de un pedido
     */
    public function updateEstado($id, $estado)
    {
        $stmt = $this->pdo->prepare('UPDATE pedidos SET estado = ?, fecha_modificacion = NOW() WHERE pedidoID = ?');
        return $stmt->execute([$estado, $id]);
    }

    /**
     * Actualizar estado de pago de un pedido
     */
    public function updateEstadoPago($id, $estadoPago)
    {
        $stmt = $this->pdo->prepare('UPDATE pedidos SET estado_pago = ?, fecha_modificacion = NOW() WHERE pedidoID = ?');
        return $stmt->execute([$estadoPago, $id]);
    }

    /**
     * Marcar pedido como aceptado
     */
    public function marcarAceptado($id)
    {
        $stmt = $this->pdo->prepare('UPDATE pedidos SET aceptado_en = NOW(), fecha_modificacion = NOW() WHERE pedidoID = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Marcar pedido como impreso
     */
    public function marcarImpreso($id)
    {
        $stmt = $this->pdo->prepare('UPDATE pedidos SET impreso_en = NOW(), fecha_modificacion = NOW() WHERE pedidoID = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Eliminar un pedido
     */
    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM pedidos WHERE pedidoID = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Obtener items de un pedido
     */
    public function getItems($pedidoId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM pedido_items WHERE pedidoID = ? ORDER BY pedidoItemID ASC');
        $stmt->execute([$pedidoId]);
        return $stmt->fetchAll();
    }

    /**
     * Agregar item a un pedido
     */
    public function addItem($data)
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO pedido_items (pedidoID, productoID, nombre_variante, cantidad, precio_unitario, 
                                       precio_total, nota)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        return $stmt->execute([
            $data['pedidoID'],
            $data['productoID'],
            $data['nombre_variante'] ?? null,
            $data['cantidad'],
            $data['precio_unitario'],
            $data['precio_total'],
            $data['nota'] ?? null
        ]);
    }

    /**
     * Actualizar item de pedido
     */
    public function updateItem($id, $data)
    {
        $stmt = $this->pdo->prepare('
            UPDATE pedido_items 
            SET productoID=?, nombre_variante=?, cantidad=?, precio_unitario=?, precio_total=?, 
                nota=?, fecha_modificacion=NOW()
            WHERE pedidoItemID=?
        ');
        return $stmt->execute([
            $data['productoID'],
            $data['nombre_variante'] ?? null,
            $data['cantidad'],
            $data['precio_unitario'],
            $data['precio_total'],
            $data['nota'] ?? null,
            $id
        ]);
    }

    /**
     * Eliminar item de pedido
     */
    public function deleteItem($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM pedido_items WHERE pedidoItemID = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Eliminar todos los items de un pedido
     */
    public function deleteItemsByPedidoId($pedidoId)
    {
        $stmt = $this->pdo->prepare('DELETE FROM pedido_items WHERE pedidoID = ?');
        return $stmt->execute([$pedidoId]);
    }

    /**
     * Generar número de pedido único
     */
    public function generateNumeroPedido()
    {
        $stmt = $this->pdo->query('SELECT MAX(numero_pedido) as max_num FROM pedidos');
        $result = $stmt->fetch();
        $maxNum = $result['max_num'] ?? 0;
        return $maxNum + 1;
    }
}
