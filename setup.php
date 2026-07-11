<?php
require_once __DIR__ . '/src/db.php';

echo "INICIANDO SETUP...\n";

try {
    // Conexión sin DB para crearla
    $pdo = DB::getPdo(false);

    // Leer esquema
    $schemaFile = __DIR__ . '/database_schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception('No se encontró database_schema.sql en el directorio del proyecto.');
    }
    $sql = file_get_contents($schemaFile);

    // Separar por ; y ejecutar cada bloque
    $stmts = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($stmts as $stmt) {
        if ($stmt === '') continue;
        // Evitar ejecutar comentarios sueltos
        if (strpos($stmt, '--') === 0) continue;
        $pdo->exec($stmt);
    }

    echo "ESQUEMA APLICADO. CREANDO USUARIO 'SCARY' SI NO EXISTE...\n";

    // Conectarse ya a la BD creada
    $pdo = DB::getPdo(true);

    // Crear rol 'SCARY' si no existe
    $rol = strtoupper('scary');
    $stmt = $pdo->prepare('SELECT rolID FROM roles WHERE nombre = ?');
    $stmt->execute([$rol]);
    $row = $stmt->fetch();
    if (!$row) {
        $ins = $pdo->prepare('INSERT INTO roles (nombre, descripcion) VALUES (?, ?)');
        $ins->execute([$rol, 'ROL CON ACCESO TOTAL ABSOLUTO']);
        $rolID = $pdo->lastInsertId();
        echo "ROL 'SCARY' CREADO (rolID={$rolID}).\n";
    } else {
        $rolID = $row['rolID'];
        echo "ROL 'SCARY' YA EXISTE (rolID={$rolID}).\n";
    }

    // Crear usuario SCARY si no existe
    $email = strtoupper('scary@localhost');
    $stmt = $pdo->prepare('SELECT usuarioID FROM usuarios WHERE correo_electronico = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if (!$row) {
        $passwordPlain = 'SCARY1234';
        $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);
        $nombre = strtoupper('SCARY');
        $ins = $pdo->prepare('INSERT INTO usuarios (rolID, nombre, correo_electronico, contrasena, activo) VALUES (?, ?, ?, ?, 1)');
        $ins->execute([$rolID, $nombre, $email, $hash]);
        $userID = $pdo->lastInsertId();
        echo "USUARIO 'SCARY' CREADO (usuarioID={$userID}) - CREDENCIALES: EMAIL={$email} PASS={$passwordPlain}\n";
        echo "CAMBIA ESTA CONTRASEÑA AL INICIAR SESIÓN.\n";
    } else {
        echo "USUARIO 'SCARY' YA EXISTE.\n";
    }

    echo "SETUP COMPLETADO. Ejecuta el servidor PHP apuntando a la carpeta public/ o copia los archivos a tu htdocs.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
