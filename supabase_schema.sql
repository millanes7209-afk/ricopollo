-- ==================================================================
-- ESQUEMA DE BASE DE DATOS PARA SUPABASE (POSTGRESQL) - RICO POLLO
-- ==================================================================

DROP TABLE IF EXISTS registros_pedidos CASCADE;
DROP TABLE IF EXISTS pedido_items CASCADE;
DROP TABLE IF EXISTS producto_variantes CASCADE;
DROP TABLE IF EXISTS pedidos CASCADE;
DROP TABLE IF EXISTS productos CASCADE;
DROP TABLE IF EXISTS categorias CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;
DROP TABLE IF EXISTS roles CASCADE;

CREATE TABLE roles (
    rolid BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    fecha_creacion TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP WITH TIME ZONE NULL
);

CREATE TABLE usuarios (
    usuarioid BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    rolid BIGINT NOT NULL REFERENCES roles(rolid) ON DELETE RESTRICT ON UPDATE CASCADE,
    nombre VARCHAR(100) NOT NULL,
    correo_electronico VARCHAR(150) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    activo SMALLINT NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP WITH TIME ZONE NULL
);

CREATE TABLE categorias (
    categoriaid BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    activo SMALLINT NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP WITH TIME ZONE NULL
);

CREATE TABLE productos (
    productoid BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    categoriaid BIGINT NULL REFERENCES categorias(categoriaid) ON DELETE SET NULL ON UPDATE CASCADE,
    nombre VARCHAR(150) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    precio_promo DECIMAL(10,2) NULL DEFAULT NULL,
    dias_promo VARCHAR(100) NULL DEFAULT NULL,
    disponible SMALLINT NOT NULL DEFAULT 1,
    imagen VARCHAR(255) NULL,
    orden_mostrado INTEGER NOT NULL DEFAULT 0,
    fecha_creacion TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP WITH TIME ZONE NULL
);

CREATE TABLE producto_variantes (
    varianteid BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    productoid BIGINT NOT NULL REFERENCES productos(productoid) ON DELETE CASCADE ON UPDATE CASCADE,
    nombre_variante VARCHAR(50) NOT NULL,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    precio_promo DECIMAL(10,2) NULL DEFAULT NULL,
    dias_promo VARCHAR(100) NULL DEFAULT NULL,
    activo SMALLINT NOT NULL DEFAULT 1,
    orden_mostrado INTEGER NOT NULL DEFAULT 0,
    imagen VARCHAR(255) NULL,
    fecha_creacion TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP WITH TIME ZONE NULL
);

CREATE TABLE pedidos (
    pedidoid BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    numero_pedido VARCHAR(50) NOT NULL UNIQUE,
    cliente_nombre VARCHAR(120) NOT NULL,
    cliente_telefono VARCHAR(50) NOT NULL,
    tipo_pedido VARCHAR(20) NOT NULL DEFAULT 'recoger' CHECK (tipo_pedido IN ('recoger','domicilio','mesa')),
    numero_mesa VARCHAR(20) NULL,
    direccion_entrega TEXT NULL,
    nota TEXT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente' CHECK (estado IN ('pendiente','aceptado','preparando','listo','entregado','cancelado','completado')),
    estado_pago VARCHAR(20) NOT NULL DEFAULT 'pendiente' CHECK (estado_pago IN ('pendiente','pagado','externo','no_requerido')),
    metodo_pago VARCHAR(20) NOT NULL DEFAULT 'ninguno' CHECK (metodo_pago IN ('efectivo','qr','repartidor','ninguno')),
    monto_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    aceptado_en TIMESTAMP WITH TIME ZONE NULL DEFAULT NULL,
    impreso_en TIMESTAMP WITH TIME ZONE NULL DEFAULT NULL,
    latitud VARCHAR(50) NULL DEFAULT NULL,
    longitud VARCHAR(50) NULL DEFAULT NULL,
    fecha_creacion TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP WITH TIME ZONE NULL
);

CREATE TABLE pedido_items (
    pedidoitemid BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    pedidoid BIGINT NOT NULL REFERENCES pedidos(pedidoid) ON DELETE CASCADE ON UPDATE CASCADE,
    productoid BIGINT NULL REFERENCES productos(productoid) ON DELETE SET NULL ON UPDATE CASCADE,
    nombre_variante VARCHAR(100) NULL,
    cantidad INTEGER NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    precio_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    nota VARCHAR(255) NULL,
    fecha_creacion TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP WITH TIME ZONE NULL
);

CREATE TABLE registros_pedidos (
    registropedidoid BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    pedidoid BIGINT NOT NULL REFERENCES pedidos(pedidoid) ON DELETE CASCADE ON UPDATE CASCADE,
    evento VARCHAR(120) NOT NULL,
    detalles TEXT NULL,
    fecha_creacion TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ==================================================================
-- COPIA DE DATOS ACTUALES (SEMNILLAS)
-- ==================================================================

-- Roles
INSERT INTO roles (rolid, nombre, descripcion, fecha_creacion) OVERRIDING SYSTEM VALUE VALUES (1, 'SCARY', 'ROL CON ACCESO TOTAL ABSOLUTO', '2026-07-02 14:51:43');

-- Usuarios
INSERT INTO usuarios (usuarioid, rolid, nombre, correo_electronico, contrasena, activo, fecha_creacion) OVERRIDING SYSTEM VALUE VALUES (1, 1, 'SCARY', 'SCARY@LOCALHOST', '$2y$10$owCRrElgFJ/A0ZGwGUNsi.Up.UW0qllSt6EhlopXg7x9.tVnQC21O', 1, '2026-07-02 14:51:43');

-- Categorias
INSERT INTO categorias (categoriaid, nombre, slug, descripcion, activo, fecha_creacion) OVERRIDING SYSTEM VALUE VALUES (1, 'BEBIDA', 'bebida', 'RESFRESCANTE', 1, '2026-07-03 00:22:24');
INSERT INTO categorias (categoriaid, nombre, slug, descripcion, activo, fecha_creacion) OVERRIDING SYSTEM VALUE VALUES (2, 'COMIDA', 'comida', 'A', 1, '2026-07-03 07:50:27');

-- Productos
INSERT INTO productos (productoid, categoriaid, nombre, slug, descripcion, precio, precio_promo, dias_promo, disponible, imagen, orden_mostrado, fecha_creacion) OVERRIDING SYSTEM VALUE VALUES (3, 1, 'COCA - COLA', 'coca---cola', '', 0.00, NULL, NULL, 0, 'producto_1783106621_c0487414ff.jpg', 0, '2026-07-03 13:15:46');
INSERT INTO productos (productoid, categoriaid, nombre, slug, descripcion, precio, precio_promo, dias_promo, disponible, imagen, orden_mostrado, fecha_creacion) OVERRIDING SYSTEM VALUE VALUES (4, 2, 'MILANESA', 'milanesa', '', 28.00, '25.00', 'MIERCOLES', 1, 'producto_1783100634_52160873bf.jpg', 0, '2026-07-03 13:24:05');

-- Producto Variantes
INSERT INTO producto_variantes (varianteid, productoid, nombre_variante, precio, precio_promo, dias_promo, activo, orden_mostrado, imagen) OVERRIDING SYSTEM VALUE VALUES (1, 3, '1/2 Lt', 8.00, NULL, NULL, 1, 1, NULL);
INSERT INTO producto_variantes (varianteid, productoid, nombre_variante, precio, precio_promo, dias_promo, activo, orden_mostrado, imagen) OVERRIDING SYSTEM VALUE VALUES (2, 3, '1 LITRO', 10.00, NULL, NULL, 0, 2, NULL);
INSERT INTO producto_variantes (varianteid, productoid, nombre_variante, precio, precio_promo, dias_promo, activo, orden_mostrado, imagen) OVERRIDING SYSTEM VALUE VALUES (3, 3, '2 LITROS', 15.00, NULL, NULL, 1, 3, NULL);

-- Pedidos
INSERT INTO pedidos (pedidoid, numero_pedido, cliente_nombre, cliente_telefono, tipo_pedido, numero_mesa, direccion_entrega, nota, estado, estado_pago, metodo_pago, monto_total, aceptado_en, impreso_en, latitud, longitud, fecha_creacion) OVERRIDING SYSTEM VALUE VALUES (1, 'RPO-1783423103', 'JUAN PEREZ', '77875479', 'domicilio', '', 'PORTON NARANJA', '', 'aceptado', 'pendiente', 'ninguno', 99.00, NULL, NULL, '-21.51837873', '-64.74178153', '2026-07-07 07:18:23');

-- Pedido Items
INSERT INTO pedido_items (pedidoitemid, pedidoid, productoid, nombre_variante, cantidad, precio_unitario, precio_total, nota, fecha_creacion) OVERRIDING SYSTEM VALUE VALUES (1, 1, 3, 'COLA - 2 LITROS', 1, 15.00, 15.00, NULL, '2026-07-07 07:18:23');
INSERT INTO pedido_items (pedidoitemid, pedidoid, productoid, nombre_variante, cantidad, precio_unitario, precio_total, nota, fecha_creacion) OVERRIDING SYSTEM VALUE VALUES (2, 1, 4, NULL, 3, 28.00, 84.00, NULL, '2026-07-07 07:18:23');

-- Registros Pedidos
INSERT INTO registros_pedidos (registropedidoid, pedidoid, evento, detalles, fecha_creacion) OVERRIDING SYSTEM VALUE VALUES (1, 1, 'CREACION_PEDIDO', 'PEDIDO CREADO POR EL CLIENTE', '2026-07-07 07:18:23');
INSERT INTO registros_pedidos (registropedidoid, pedidoid, evento, detalles, fecha_creacion) OVERRIDING SYSTEM VALUE VALUES (2, 1, 'CAMBIO_ESTADO', 'ESTADO ACTUALIZADO A: ACEPTADO', '2026-07-07 07:21:00');

-- Reset sequences
SELECT setval(pg_get_serial_sequence('roles', 'rolid'), COALESCE(MAX(rolid), 1)) FROM roles;
SELECT setval(pg_get_serial_sequence('usuarios', 'usuarioid'), COALESCE(MAX(usuarioid), 1)) FROM usuarios;
SELECT setval(pg_get_serial_sequence('categorias', 'categoriaid'), COALESCE(MAX(categoriaid), 1)) FROM categorias;
SELECT setval(pg_get_serial_sequence('productos', 'productoid'), COALESCE(MAX(productoid), 1)) FROM productos;
SELECT setval(pg_get_serial_sequence('producto_variantes', 'varianteid'), COALESCE(MAX(varianteid), 1)) FROM producto_variantes;
SELECT setval(pg_get_serial_sequence('pedidos', 'pedidoid'), COALESCE(MAX(pedidoid), 1)) FROM pedidos;
SELECT setval(pg_get_serial_sequence('pedido_items', 'pedidoitemid'), COALESCE(MAX(pedidoitemid), 1)) FROM pedido_items;
SELECT setval(pg_get_serial_sequence('registros_pedidos', 'registropedidoid'), COALESCE(MAX(registropedidoid), 1)) FROM registros_pedidos;
