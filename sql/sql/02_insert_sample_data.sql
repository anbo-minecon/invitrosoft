-- Instrucciones SQL para insertar datos de ejemplo en las tablas del sistema

-- Insertar datos en la tabla tipo_parametro
INSERT INTO tipo_parametro (nombre) VALUES 
('Tipo A'),
('Tipo B'),
('Tipo C');

-- Insertar datos en la tabla categorias
INSERT INTO categorias (nombre, descripcion) VALUES 
('Categoria 1', 'Descripción de la categoría 1'),
('Categoria 2', 'Descripción de la categoría 2'),
('Categoria 3', 'Descripción de la categoría 3');

-- Insertar datos en la tabla usuarios
INSERT INTO usuarios (identidad, nombre, genero, telefono, email, password, tipo, tiempo_uso, ficha_formacion) VALUES 
('12345678', 'Juan Pérez', 'Masculino', '123456789', 'juan@example.com', '$2y$10$e0MY5Q1m8g5H1g1g1g1g1Oe1g1g1g1g1g1g1g1g1g1g1g1g1g1', 'admin', NULL, NULL),
('87654321', 'María López', 'Femenino', '987654321', 'maria@example.com', '$2y$10$e0MY5Q1m8g5H1g1g1g1g1Oe1g1g1g1g1g1g1g1g1g1g1g1g1g1', 'user', NULL, NULL);

-- Insertar datos en la tabla parametros
INSERT INTO parametros (id_tipo, nombre, descripcion) VALUES 
(1, 'Parametro 1', 'Descripción del parámetro 1'),
(2, 'Parametro 2', 'Descripción del parámetro 2');

-- Insertar datos en la tabla reactivos
INSERT INTO reactivos (nombre_comun, formula_quimica, categoria_id, unidad_medida, cantidad_total, fecha_vencimiento) VALUES 
('Reactivo A', 'H2O', 1, 'Litros', 100, '2025-12-31'),
('Reactivo B', 'NaCl', 2, 'Kilogramos', 50, '2024-06-30');