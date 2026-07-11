<?php
// Conexión PDO reutilizable. Ajusta credenciales si tu XAMPP usa otra configuración.
class DB
{
    public static function getPdo($withDb = true)
    {
        // Obtener la URL de conexión desde las variables de entorno de Vercel/sistema
        $dbUrl = getenv('DATABASE_URL') ?: (constant('DATABASE_URL') ?? null);

        $host = '127.0.0.1';
        $port = 5432;
        $user = 'postgres';
        $pass = 'SCARYMOVIEscarymovie'; // Contraseña fallback del usuario
        $db = 'postgres';
        $sslmode = 'require';

        if ($dbUrl) {
            $parsed = parse_url($dbUrl);
            $host = $parsed['host'] ?? $host;
            $port = $parsed['port'] ?? $port;
            $user = $parsed['user'] ?? $user;
            $pass = isset($parsed['pass']) ? urldecode($parsed['pass']) : $pass;
            $db = isset($parsed['path']) ? ltrim($parsed['path'], '/') : $db;
        } else {
            // Alternativamente, verificar variables de entorno individuales
            $host = getenv('DB_HOST') ?: $host;
            $port = getenv('DB_PORT') ?: $port;
            $user = getenv('DB_USER') ?: $user;
            $pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : $pass;
            $db = getenv('DB_NAME') ?: $db;
            $sslmode = getenv('DB_SSLMODE') ?: $sslmode;
        }

        // Si se pide conexión sin base de datos (p. ej. para crearla, aunque en postgres usualmente se conecta a 'postgres' o 'template1')
        $dsn = ($withDb && !empty($db))
            ? "pgsql:host=$host;port=$port;dbname=$db;sslmode=$sslmode"
            : "pgsql:host=$host;port=$port;sslmode=$sslmode";

        $opts = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STATEMENT_CLASS => ['CompatiblePDOStatement', []]
        ];
        return new PDO($dsn, $user, $pass, $opts);
    }

    public static function obtenerPrecioActivo($producto)
    {
        if (empty($producto['precio_promo']) || empty($producto['dias_promo'])) {
            return $producto['precio'];
        }

        $diasSemana = [
            0 => 'DOMINGO',
            1 => 'LUNES',
            2 => 'MARTES',
            3 => 'MIERCOLES',
            4 => 'JUEVES',
            5 => 'VIERNES',
            6 => 'SABADO'
        ];
        $diaActual = $diasSemana[(int) date('w')];

        $diasConfigurados = array_map(function ($d) {
            $d = trim(strtoupper($d));
            $search = ['Á', 'É', 'Í', 'Ó', 'Ú'];
            $replace = ['A', 'E', 'I', 'O', 'U'];
            return str_replace($search, $replace, $d);
        }, explode(',', $producto['dias_promo']));

        if (in_array($diaActual, $diasConfigurados)) {
            return $producto['precio_promo'];
        }

        return $producto['precio'];
    }
}

// Clase para traducir automáticamente los nombres de columna en minúsculas de PostgreSQL
// de vuelta a las claves con camelCase que espera el código PHP.
class CompatiblePDOStatement extends PDOStatement
{
    private static $keyMap = [
        'rolid' => 'rolID',
        'usuarioid' => 'usuarioID',
        'categoriaid' => 'categoriaID',
        'productoid' => 'productoID',
        'pedidoid' => 'pedidoID',
        'pedidoitemid' => 'pedidoItemID',
        'varianteid' => 'varianteID',
        'registropedidoid' => 'registroPedidoID'
    ];

    private function mapRow($row)
    {
        if (!is_array($row)) {
            return $row;
        }
        $mapped = [];
        foreach ($row as $key => $value) {
            $lowerKey = strtolower($key);
            if (isset(self::$keyMap[$lowerKey])) {
                $mapped[self::$keyMap[$lowerKey]] = $value;
            } else {
                $mapped[$key] = $value;
            }
        }
        return $mapped;
    }

    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursor_orientation = PDO::FETCH_ORI_NEXT, int $cursor_offset = 0): mixed
    {
        $row = parent::fetch($mode, $cursor_orientation, $cursor_offset);
        return $this->mapRow($row);
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, ...$args): array
    {
        $rows = parent::fetchAll($mode, ...$args);
        if (is_array($rows)) {
            foreach ($rows as &$row) {
                $row = $this->mapRow($row);
            }
        }
        return $rows;
    }
}
