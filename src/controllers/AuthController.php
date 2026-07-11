<?php

class AuthController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Procesar login
     */
    public function login($correo, $contrasena)
    {
        $correo = strtoupper(trim($correo));
        
        try {
            $stmt = $this->pdo->prepare('SELECT usuarioID, nombre, correo_electronico, contrasena, rolID FROM usuarios WHERE correo_electronico = ? LIMIT 1');
            $stmt->execute([$correo]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($contrasena, $user['contrasena'])) {
                // Autenticado
                session_start();
                $_SESSION['usuarioID'] = $user['usuarioID'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['rolID'] = $user['rolID'];
                $_SESSION['admin_logged_in'] = true;
                
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'CREDENCIALES INVÁLIDAS'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'ERROR DE CONEXIÓN'];
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        session_start();
        session_destroy();
        return ['success' => true];
    }

    /**
     * Verificar si está autenticado
     */
    public function checkAuth()
    {
        session_start();
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
}
