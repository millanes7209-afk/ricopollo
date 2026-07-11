-- Crear base de datos y usarla
CREATE DATABASE IF NOT EXISTS rico_pollo DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rico_pollo;

CREATE TABLE roles (
    rolID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuarios (
    usuarioID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rolID INT UNSIGNED NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    correo_electronico VARCHAR(150) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rolID) REFERENCES roles(rolID) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categorias (
    categoriaID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE productos (
    productoID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoriaID INT UNSIGNED NULL,
    nombre VARCHAR(150) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0,
    precio_promo DECIMAL(10,2) NULL DEFAULT NULL,
    dias_promo VARCHAR(100) NULL DEFAULT NULL,
    disponible TINYINT(1) NOT NULL DEFAULT 1,
    imagen VARCHAR(255) NULL,
    orden_mostrado INT UNSIGNED NOT NULL DEFAULT 0,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoriaID) REFERENCES categorias(categoriaID) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pedidos (
    pedidoID BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(50) NOT NULL UNIQUE,
    cliente_nombre VARCHAR(120) NOT NULL,
    cliente_telefono VARCHAR(50) NOT NULL,
    tipo_pedido ENUM('recoger','domicilio','mesa') NOT NULL DEFAULT 'recoger',
    numero_mesa VARCHAR(20) NULL,
    direccion_entrega TEXT NULL,
    nota TEXT NULL,
    estado ENUM('pendiente','aceptado','preparando','listo','entregado','cancelado','completado') NOT NULL DEFAULT 'pendiente',
    estado_pago ENUM('pendiente','pagado','externo','no_requerido') NOT NULL DEFAULT 'pendiente',
    metodo_pago ENUM('efectivo','qr','repartidor','ninguno') NOT NULL DEFAULT 'ninguno',
    monto_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    aceptado_en TIMESTAMP NULL DEFAULT NULL,
    impreso_en TIMESTAMP NULL DEFAULT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pedidos_estado (estado),
    INDEX idx_pedidos_estado_pago (estado_pago),
    INDEX idx_pedidos_fecha_creacion (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pedido_items (
    pedidoItemID BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedidoID BIGINT UNSIGNED NOT NULL,
    productoID INT UNSIGNED NULL,
    nombre_variante VARCHAR(100) NULL,
    cantidad INT UNSIGNED NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0,
    precio_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    nota VARCHAR(255) NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pedidoID) REFERENCES pedidos(pedidoID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (productoID) REFERENCES productos(productoID) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE producto_variantes (
    varianteID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    productoID INT UNSIGNED NOT NULL,
    nombre_variante VARCHAR(50) NOT NULL,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    precio_promo DECIMAL(10,2) NULL DEFAULT NULL,
    dias_promo VARCHAR(100) NULL DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    imagen VARCHAR(255) NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (productoID) REFERENCES productos(productoID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE registros_pedidos (
    registroPedidoID BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedidoID BIGINT UNSIGNED NOT NULL,
    evento VARCHAR(120) NOT NULL,
    detalles TEXT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedidoID) REFERENCES pedidos(pedidoID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Para actualizar una base existente:
-- ALTER TABLE productos ADD COLUMN imagen VARCHAR(255) NULL AFTER disponible;
-- Nota: no se inserta usuario inicial aquí. Crear el usuario 'SCARY' desde la aplicación.
