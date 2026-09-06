-- Paintarely - schema inicial (MySQL / MariaDB, InnoDB, utf8mb4)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Usuarios
CREATE TABLE IF NOT EXISTS usuarios (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  monedas INT NOT NULL DEFAULT 0,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Plantillas de dibujo (los "trazos guía")
CREATE TABLE IF NOT EXISTS plantillas (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  nivel_dificultad ENUM('facil','medio','dificil') NOT NULL DEFAULT 'facil',
  svg_path VARCHAR(255) NOT NULL,
  zonas_color JSON NULL,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Grupos (máx 10 participantes)
CREATE TABLE IF NOT EXISTS grupos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  plantilla_id INT NOT NULL,
  estado ENUM('abierto','cerrado','finalizado') NOT NULL DEFAULT 'abierto',
  max_participantes INT NOT NULL DEFAULT 10,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (plantilla_id) REFERENCES plantillas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS grupo_miembros (
  id INT PRIMARY KEY AUTO_INCREMENT,
  grupo_id INT NOT NULL,
  usuario_id INT NOT NULL,
  fecha_union DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (grupo_id) REFERENCES grupos(id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  UNIQUE KEY unico_miembro (grupo_id, usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dibujos enviados (resultado final del usuario)
-- grupo_id es NULL mientras el dibujo es "libre" (guardado antes de unirse a un grupo);
-- se completa cuando el dibujo pasa a formar parte de un reto grupal (Fase 3).
CREATE TABLE IF NOT EXISTS dibujos_enviados (
  id INT PRIMARY KEY AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  grupo_id INT NULL,
  plantilla_id INT NOT NULL,
  imagen_path VARCHAR(255) NOT NULL,
  fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (grupo_id) REFERENCES grupos(id),
  FOREIGN KEY (plantilla_id) REFERENCES plantillas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Votos (1 a 5 estrellas)
CREATE TABLE IF NOT EXISTS votos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  dibujo_id INT NOT NULL,
  votante_id INT NOT NULL,
  estrellas TINYINT NOT NULL CHECK (estrellas BETWEEN 1 AND 5),
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (dibujo_id) REFERENCES dibujos_enviados(id),
  FOREIGN KEY (votante_id) REFERENCES usuarios(id),
  UNIQUE KEY un_voto_por_dibujo (dibujo_id, votante_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ranking final por grupo
CREATE TABLE IF NOT EXISTS ranking_grupo (
  id INT PRIMARY KEY AUTO_INCREMENT,
  grupo_id INT NOT NULL,
  dibujo_id INT NOT NULL,
  puesto TINYINT NOT NULL,
  promedio_estrellas DECIMAL(3,2) NOT NULL,
  monedas_otorgadas INT NOT NULL DEFAULT 0,
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (grupo_id) REFERENCES grupos(id),
  FOREIGN KEY (dibujo_id) REFERENCES dibujos_enviados(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Historial de monedas
CREATE TABLE IF NOT EXISTS monedas_transacciones (
  id INT PRIMARY KEY AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  cantidad INT NOT NULL,
  motivo VARCHAR(150) NOT NULL,
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
