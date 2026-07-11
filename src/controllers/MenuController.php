<?php

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Variante.php';
require_once __DIR__ . '/../db.php';

class MenuController
{
    private $productoModel;
    private $varianteModel;

    public function __construct($pdo)
    {
        $this->productoModel = new Producto($pdo);
        $this->varianteModel = new Variante($pdo);
    }

    /**
     * Obtener datos para el menú público
     */
    public function index()
    {
        // Obtener productos
        $productos = $this->productoModel->getForMenu();

        // Cargar variantes activas
        $variantesMap = $this->varianteModel->getGroupedByProducto();

        // Agrupar por categoría
        $menuGrouped = [];
        foreach ($productos as $p) {
            $tieneVariantes = !empty($variantesMap[$p['productoID']]);

            if ($tieneVariantes) {
                $cat = strtoupper($p['categoria_nombre']);
                $menuGrouped[$cat][] = [
                    'productoID' => $p['productoID'],
                    'nombre' => $p['nombre'],
                    'descripcion' => $p['descripcion'],
                    'imagen' => $p['imagen'],
                    'categoria_nombre' => $p['categoria_nombre'],
                    'tieneVariantes' => true,
                    'variantes' => $variantesMap[$p['productoID']],
                ];
            } else {
                $cat = strtoupper($p['categoria_nombre']);
                $menuGrouped[$cat][] = [
                    'productoID' => $p['productoID'],
                    'nombre' => $p['nombre'],
                    'descripcion' => $p['descripcion'],
                    'precio' => $p['precio'],
                    'precio_promo' => $p['precio_promo'],
                    'dias_promo' => $p['dias_promo'],
                    'imagen' => $p['imagen'],
                    'categoria_nombre' => $p['categoria_nombre'],
                    'tieneVariantes' => false,
                ];
            }
        }

        // Emitir catálogo como JSON para el cartJS
        $catalogoJson = [];
        foreach ($productos as $p) {
            $tieneVariantes = !empty($variantesMap[$p['productoID']]);
            $variants = [];

            if ($tieneVariantes) {
                foreach ($variantesMap[$p['productoID']] as $v) {
                    $vPrecioActivo = DB::obtenerPrecioActivo($v);
                    $variants[] = [
                        'id' => 'v' . $v['varianteID'],
                        'varianteID' => $v['varianteID'],
                        'nombre' => strtoupper($v['nombre_variante']),
                        'precio' => $vPrecioActivo,
                        'enPromo' => $vPrecioActivo < $v['precio'],
                        'precioOrig' => $v['precio'],
                        'imagen' => $v['imagen'] ?? $p['imagen'] ?? null,
                    ];
                }
            }

            $catalogoJson[$p['productoID']] = [
                'id' => $p['productoID'],
                'nombre' => strtoupper($p['nombre']),
                'tieneVariantes' => $tieneVariantes,
                'imagen' => $p['imagen'] ?? null,
                'variants' => $variants,
            ];
        }

        return [
            'menuGrouped' => $menuGrouped,
            'catalogoJson' => $catalogoJson
        ];
    }
}
