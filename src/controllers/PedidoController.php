<?php

require_once __DIR__ . '/../models/Pedido.php';

class PedidoController
{
    private $pedidoModel;
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->pedidoModel = new Pedido($pdo);
    }

    /**
     * Listar todos los pedidos
     */
    public function index()
    {
        $pedidos = $this->pedidoModel->getAll();
        
        // Cargar items para cada pedido
        foreach ($pedidos as &$pedido) {
            $pedido['items'] = $this->pedidoModel->getItems($pedido['pedidoID']);
        }

        return ['pedidos' => $pedidos];
    }

    /**
     * Listar pedidos pendientes
     */
    public function pending()
    {
        $pedidos = $this->pedidoModel->getPending();
        
        // Cargar items para cada pedido
        foreach ($pedidos as &$pedido) {
            $pedido['items'] = $this->pedidoModel->getItems($pedido['pedidoID']);
        }

        return ['pedidos' => $pedidos];
    }

    /**
     * Mostrar detalle de un pedido
     */
    public function show($id)
    {
        $pedido = $this->pedidoModel->getById($id);
        if (!$pedido) {
            return ['success' => false, 'error' => 'Pedido no encontrado'];
        }

        $items = $this->pedidoModel->getItems($id);

        return [
            'success' => true,
            'pedido' => $pedido,
            'items' => $items
        ];
    }

    /**
     * Crear nuevo pedido
     */
    public function create($data)
    {
        $numeroPedido = $this->pedidoModel->generateNumeroPedido();
        
        $pedidoData = [
            'numero_pedido' => $numeroPedido,
            'cliente_nombre' => $data['cliente_nombre'] ?? null,
            'cliente_telefono' => $data['cliente_telefono'] ?? null,
            'tipo_pedido' => $data['tipo_pedido'],
            'numero_mesa' => $data['numero_mesa'] ?? null,
            'direccion_entrega' => $data['direccion_entrega'] ?? null,
            'nota' => $data['nota'] ?? null,
            'estado' => $data['estado'] ?? 'PENDIENTE',
            'estado_pago' => $data['estado_pago'] ?? 'PENDIENTE',
            'metodo_pago' => $data['metodo_pago'] ?? null,
            'monto_total' => $data['monto_total'],
            'aceptado_en' => null,
            'impreso_en' => null
        ];

        $this->pdo->beginTransaction();
        try {
            $this->pedidoModel->create($pedidoData);
            $pedidoId = $this->pdo->lastInsertId();

            // Agregar items
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $itemData = [
                        'pedidoID' => $pedidoId,
                        'productoID' => $item['productoID'],
                        'nombre_variante' => $item['nombre_variante'] ?? null,
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio_unitario'],
                        'precio_total' => $item['precio_total'],
                        'nota' => $item['nota'] ?? null
                    ];
                    $this->pedidoModel->addItem($itemData);
                }
            }

            $this->pdo->commit();
            return ['success' => true, 'pedidoId' => $pedidoId, 'numero_pedido' => $numeroPedido];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Actualizar pedido
     */
    public function update($id, $data)
    {
        $pedidoData = [
            'cliente_nombre' => $data['cliente_nombre'] ?? null,
            'cliente_telefono' => $data['cliente_telefono'] ?? null,
            'tipo_pedido' => $data['tipo_pedido'],
            'numero_mesa' => $data['numero_mesa'] ?? null,
            'direccion_entrega' => $data['direccion_entrega'] ?? null,
            'nota' => $data['nota'] ?? null,
            'estado' => $data['estado'],
            'estado_pago' => $data['estado_pago'] ?? null,
            'metodo_pago' => $data['metodo_pago'] ?? null,
            'monto_total' => $data['monto_total'],
            'aceptado_en' => $data['aceptado_en'] ?? null,
            'impreso_en' => $data['impreso_en'] ?? null
        ];

        $this->pdo->beginTransaction();
        try {
            $this->pedidoModel->update($id, $pedidoData);

            // Eliminar items existentes y agregar nuevos
            $this->pedidoModel->deleteItemsByPedidoId($id);
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $itemData = [
                        'pedidoID' => $id,
                        'productoID' => $item['productoID'],
                        'nombre_variante' => $item['nombre_variante'] ?? null,
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio_unitario'],
                        'precio_total' => $item['precio_total'],
                        'nota' => $item['nota'] ?? null
                    ];
                    $this->pedidoModel->addItem($itemData);
                }
            }

            $this->pdo->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Actualizar estado de pedido
     */
    public function updateEstado($id, $estado)
    {
        $this->pedidoModel->updateEstado($id, $estado);
        return ['success' => true];
    }

    /**
     * Actualizar estado de pago
     */
    public function updateEstadoPago($id, $estadoPago)
    {
        $this->pedidoModel->updateEstadoPago($id, $estadoPago);
        return ['success' => true];
    }

    /**
     * Marcar pedido como aceptado
     */
    public function marcarAceptado($id)
    {
        $this->pedidoModel->marcarAceptado($id);
        return ['success' => true];
    }

    /**
     * Marcar pedido como impreso
     */
    public function marcarImpreso($id)
    {
        $this->pedidoModel->marcarImpreso($id);
        return ['success' => true];
    }

    /**
     * Eliminar pedido
     */
    public function delete($id)
    {
        $this->pdo->beginTransaction();
        try {
            // Eliminar items primero
            $this->pedidoModel->deleteItemsByPedidoId($id);
            // Eliminar pedido
            $this->pedidoModel->delete($id);
            $this->pdo->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
