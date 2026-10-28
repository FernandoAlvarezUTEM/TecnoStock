-- TecnoStock: estructura inicial de la base de datos

CREATE DATABASE IF NOT EXISTS tecnostock
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE tecnostock;

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    clave_hash VARCHAR(255) NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_usuario),
    UNIQUE (correo)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4;