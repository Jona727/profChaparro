-- ------------------------------------------------------------------------------
-- ESQUEMA DE BASE DE DATOS COMPLETO - PORTFOLIO Y CMS PROF. CHAPARRO
-- ------------------------------------------------------------------------------

CREATE DATABASE IF NOT EXISTS portfolio_chaparro;
USE portfolio_chaparro;

-- 1. Escuelas
CREATE TABLE IF NOT EXISTS escuelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Años (relacionados a escuela)
CREATE TABLE IF NOT EXISTS anios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_anio VARCHAR(100) NOT NULL,
    escuela_id INT NOT NULL,
    FOREIGN KEY (escuela_id) REFERENCES escuelas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Materias (relacionadas a año)
CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    anio_id INT NOT NULL,
    FOREIGN KEY (anio_id) REFERENCES anios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Temas (relacionados a materia)
CREATE TABLE IF NOT EXISTS temas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    materia_id INT NOT NULL,
    FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Archivos (relacionados a tema)
CREATE TABLE IF NOT EXISTS archivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    ruta_pdf VARCHAR(500) NOT NULL,
    tipo_archivo ENUM('pdf', 'word', 'other') DEFAULT 'pdf',
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tema_id INT NOT NULL,
    FOREIGN KEY (tema_id) REFERENCES temas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Usuarios (para el panel admin)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Alumnos (para debate)
CREATE TABLE IF NOT EXISTS alumnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Debates
CREATE TABLE IF NOT EXISTS debates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Opiniones
CREATE TABLE IF NOT EXISTS opiniones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    debate_id INT NOT NULL,
    alumno_id INT NOT NULL,
    opinion TEXT NOT NULL,
    puntuacion INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (debate_id) REFERENCES debates(id) ON DELETE CASCADE,
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------------------
-- DATOS SEMILLA (Usuarios Iniciales)
-- ------------------------------------------------------------------------------

-- Insertar usuario admin por defecto (password: admin123)
-- NOTA: Se recomienda cambiar esta contraseña al subir el sitio a producción.
INSERT IGNORE INTO usuarios (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
