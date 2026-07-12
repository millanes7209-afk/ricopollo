<?php
/**
 * Router principal - Punto de entrada de la aplicación
 */

require_once __DIR__ . '/../src/db.php';

// Verificar autenticación para rutas de admin
function requireAuth()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Compatible con admin_logged_in (router) y usuarioID (archivos legacy de /public)
    if (
        (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true)
        && !isset($_SESSION['usuarioID'])
    ) {
        header('Location: /login');
        exit;
    }
}

// Obtener ruta solicitada (ignorando subcarpetas dinámicas como /ricopollo/public)
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filename = basename($request_uri);

if (empty($filename) || strtolower($filename) === 'public' || strtolower($filename) === 'index.php' || strtolower($filename) === 'ricopollo') {
    $path = '/';
} else {
    // Strip .php extension for clean URL routing
    $path = '/' . preg_replace('/\.php$/i', '', $filename);
}

// Router simple
switch ($path) {
    case '/':
    case '/menu':
    case '/menu.php':
        // Menú público
        require_once __DIR__ . '/../src/controllers/MenuController.php';
        $pdo = DB::getPdo(true);
        $menuController = new MenuController($pdo);
        $data = $menuController->index();

        // Pasar variables a la vista
        $menuGrouped = $data['menuGrouped'];
        $catalogoJson = $data['catalogoJson'];

        require_once __DIR__ . '/../src/views/menu/index.php';
        break;

    case '/admin':
        // Panel de administración - incluye su propio auth check
        require_once __DIR__ . '/../public/admin.php';
        break;

    case '/products':
    case '/products.php':
        // Lista de productos (admin)
        requireAuth();
        require_once __DIR__ . '/../src/controllers/ProductoController.php';
        $pdo = DB::getPdo(true);
        $productoController = new ProductoController($pdo);

        // Manejar acciones
        if (isset($_GET['action']) && isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            if ($_GET['action'] === 'delete') {
                $productoController->delete($id);
                header('Location: /products');
                exit;
            }
            if ($_GET['action'] === 'toggle') {
                $productoController->toggleDisponible($id);
                header('Location: /products');
                exit;
            }
            if ($_GET['action'] === 'toggle_variante' && isset($_GET['variante_id'])) {
                $varianteId = (int) $_GET['variante_id'];
                $productoController->toggleVarianteActivo($varianteId);
                header('Location: /products');
                exit;
            }
        }

        $data = $productoController->index();

        // Pasar variables a la vista
        $productos = $data['productos'];
        $variantesMap = $data['variantesMap'];

        require_once __DIR__ . '/../src/views/productos/index.php';
        break;

    case '/product_form':
    case '/product_form.php':
        // Formulario de producto (admin)
        requireAuth();
        require_once __DIR__ . '/../src/controllers/ProductoController.php';
        $pdo = DB::getPdo(true);
        $productoController = new ProductoController($pdo);

        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $productoController->save($_POST, $_FILES);
            if ($result['success']) {
                header('Location: /products');
                exit;
            }
            $error = $result['error'] ?? 'Error al guardar';
        }

        $data = $productoController->form($id);

        // Pasar variables a la vista
        $producto = $data['producto'];
        $variantes = $data['variantes'];
        $categorias = $data['categorias'];

        require_once __DIR__ . '/../src/views/productos/form.php';
        break;

    case '/login':
    case '/login.php':
        // Login
        require_once __DIR__ . '/../src/controllers/AuthController.php';
        $pdo = DB::getPdo(true);
        $authController = new AuthController($pdo);

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $authController->login($_POST['correo'] ?? '', $_POST['contrasena'] ?? '');
            if ($result['success']) {
                header('Location: /admin');
                exit;
            }
            $error = $result['error'];
        }

        require_once __DIR__ . '/../src/views/auth/login.php';
        break;

    case '/categories':
    case '/categories.php':
        requireAuth();
        require_once __DIR__ . '/../public/categories.php';
        break;

    case '/category_form':
    case '/category_form.php':
        requireAuth();
        require_once __DIR__ . '/../public/category_form.php';
        break;

    case '/logout':
    case '/logout.php':
        require_once __DIR__ . '/../public/logout.php';
        break;

    case '/ticket':
    case '/ticket.php':
        requireAuth();
        require_once __DIR__ . '/../public/ticket.php';
        break;

    case '/order':
    case '/order.php':
        require_once __DIR__ . '/../public/order.php';
        break;

    case '/order_confirm':
    case '/order_confirm.php':
        require_once __DIR__ . '/../public/order_confirm.php';
        break;

    default:
        // Ruta no encontrada - intentar cargar archivo directo (compatibilidad)
        $file = __DIR__ . $path;
        if (file_exists($file) && is_file($file)) {
            require_once $file;
        } else {
            http_response_code(404);
            echo 'Página no encontrada';
        }
        break;
}
