CREATE DATABASE IF NOT EXISTS encuesta;

USE encuesta;

CREATE TABLE IF NOT EXISTS votos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    si INT DEFAULT 0,
    no INT DEFAULT 0
);

-- Insertar fila inicial con contadores en 0
INSERT INTO votos (si, no) VALUES (0, 0);