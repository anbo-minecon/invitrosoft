--- SQL script to create the database and tables for Invitrosoft
--  root: localhost
--  password:

CREATE DATABASE invitrosoft;

USE  invitrosoft;

CREATE TABLE tipo_parametro (
    id_tipo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT
);

CREATE TABLE parametros (
    id_parametro INT AUTO_INCREMENT PRIMARY KEY,
    id_tipo INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    usuarios INT,
    FOREIGN KEY (id_tipo) REFERENCES tipo_parametro(id_tipo)
);

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identidad VARCHAR(50) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    genero INT NOT NULL, -- FK a parametros.id_parametro
    telefono VARCHAR(15),
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'aprendiz', 'pasante') DEFAULT 'admin',
    tiempo_uso INT,
    ficha_formacion VARCHAR(255),
    FOREIGN KEY (genero) REFERENCES parametros(id_parametro)
);

CREATE TABLE reactivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_comun VARCHAR(255) NOT NULL,
    formula_quimica VARCHAR(255),
    categoria_id INT,
    unidad_medida VARCHAR(50),
    cantidad_total INT DEFAULT 0,
    fecha_vencimiento DATE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE TABLE formulaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    tipo ENUM('soluciones-madre','medios-cultivo','soluciones-desinfectantes') NULL
);

CREATE TABLE formulacion_reactivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    formulacion_id INT NOT NULL,
    reactivo_id INT NOT NULL,
    cantidad VARCHAR(50),
    FOREIGN KEY (formulacion_id) REFERENCES formulaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (reactivo_id) REFERENCES reactivos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS grupos_formulacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    formulacion_id INT NOT NULL,
    nombre_grupo VARCHAR(255) NOT NULL,
    FOREIGN KEY (formulacion_id) REFERENCES formulaciones(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS grupo_reactivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grupo_id INT NOT NULL,
    reactivo_id INT NOT NULL,
    cantidad VARCHAR(100),
    s_madre VARCHAR(100),
    FOREIGN KEY (grupo_id) REFERENCES grupos_formulacion(id) ON DELETE CASCADE,
    FOREIGN KEY (reactivo_id) REFERENCES reactivos(id) ON DELETE CASCADE
);