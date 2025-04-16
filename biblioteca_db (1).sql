-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-04-2025 a las 22:30:08
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `biblioteca_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `autores`
--

CREATE TABLE `autores` (
  `id_autor` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `biografia` text DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `nacionalidad` varchar(50) DEFAULT NULL,
  `imagen_autor` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `autores`
--

INSERT INTO `autores` (`id_autor`, `nombre`, `apellido`, `biografia`, `fecha_nacimiento`, `nacionalidad`, `imagen_autor`) VALUES
(1, 'Gabriel', 'García Márquez', 'Escritor, periodista y novelista colombiano, conocido por popularizar el realismo mágico. Su obra más famosa, Cien años de soledad, es considerada una de las más importantes de la literatura hispanoamericana. Ganó el Premio Nobel de Literatura en 1982.', '1927-03-06', 'Colombiana', NULL),
(2, 'Jorge Luis', 'Borges', 'Escritor, poeta y ensayista argentino, reconocido por su influencia en la literatura del siglo XX. Su obra explora temas como los laberintos, los espejos y el infinito. Ficciones y El Aleph son algunas de sus colecciones más destacadas.', '1899-08-24', 'Argentina', NULL),
(3, 'Isabel', 'Allende', 'Escritora chilena, famosa por sus novelas que mezclan elementos históricos y de realismo mágico. Su obra más conocida es La casa de los espíritus, que narra la historia de una familia a lo largo de generaciones en Chile. Ha recibido numerosos premios literarios.', '1942-08-02', 'Chilena', NULL),
(4, 'Mario', 'Vargas Llosa', 'Novelista, ensayista y político peruano, conocido por obras como La ciudad y los perros y La fiesta del chivo. Su narrativa suele abordar temas políticos y sociales de América Latina. Ganó el Premio Nobel de Literatura en 2010.', '1936-03-28', 'Peruana', NULL),
(13, 'George R.R.', 'Martin', 'Autor de Canción de Hielo y Fuego', '1948-09-20', 'Estadounidense', NULL),
(14, 'Agatha', 'Christie', 'La reina del crimen, autora de misterio', '1890-09-15', 'Británica', NULL),
(15, 'Nicholas', 'Sparks', 'Autor bestseller de novelas románticas', '1965-12-31', 'Estadounidense', NULL),
(16, 'Laura', 'Gallego', 'Escritora española de literatura fantástica', '1977-10-11', 'Española', NULL),
(17, 'Mario', 'Benedetti', 'Escritor uruguayo destacado en poesía y narrativa', '1920-09-14', 'Uruguaya', NULL),
(23, 'Haruki', 'Murakami', 'Escritor y traductor japonés, autor de novelas, relatos y ensayos. Sus obras más conocidas incluyen \"Tokio blues\", \"Kafka en la orilla\" y \"1Q84\". Su estilo mezcla realismo con elementos surrealistas.', '1949-01-12', 'Japonesa', NULL),
(24, 'Jane', 'Austen', 'Novelista británica reconocida por sus obras que retratan la vida de la burguesía rural inglesa de principios del siglo XIX. Sus obras más famosas incluyen \"Orgullo y prejuicio\" y \"Sentido y sensibilidad\".', '1775-12-16', 'Británica', NULL),
(25, 'Octavia', 'Butler', 'Escritora estadounidense de ciencia ficción. Fue la primera escritora de este género en recibir la Beca MacArthur. Sus obras exploran temas como la raza, el género y la jerarquía social.', '1947-06-22', 'Estadounidense', NULL),
(26, 'Albert', 'Camus', 'Escritor, filósofo y periodista francés nacido en Argelia. Figura clave del existencialismo y ganador del Premio Nobel de Literatura en 1957. Sus obras más conocidas incluyen \"El extranjero\" y \"La peste\".', '1913-11-07', 'Francesa', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito_prestamos`
--

CREATE TABLE `carrito_prestamos` (
  `id_carrito` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_libro` int(11) NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `id_genero` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `id_genero`, `nombre`, `descripcion`, `fecha_creacion`) VALUES
(1, 1, 'Novela histórica', 'Ficción ambientada en períodos históricos específicos', '2025-02-03 21:14:45'),
(2, 1, 'Realismo contemporáneo', 'Ficción ambientada en la actualidad', '2025-02-03 21:14:45'),
(3, 1, 'Aventura', 'Historias de acción y aventuras emocionantes', '2025-02-03 21:14:45'),
(4, 2, 'Fantasía épica', 'Historias ambientadas en mundos fantásticos completos', '2025-02-03 21:14:45'),
(5, 2, 'Ciencia ficción distópica', 'Futuros distópicos y sociedades alternativas', '2025-02-03 21:14:45'),
(6, 2, 'Fantasía urbana', 'Elementos fantásticos en entornos urbanos modernos', '2025-02-03 21:14:45'),
(7, 3, 'Novela policial', 'Investigaciones criminales y detectives', '2025-02-03 21:14:45'),
(8, 3, 'Thriller psicológico', 'Suspenso centrado en aspectos psicológicos', '2025-02-03 21:14:45'),
(9, 3, 'Misterio sobrenatural', 'Misterios con elementos paranormales', '2025-02-03 21:14:45'),
(10, 4, 'Romance contemporáneo', 'Historias de amor en la actualidad', '2025-02-03 21:14:45'),
(11, 4, 'Romance histórico', 'Historias de amor en contextos históricos', '2025-02-03 21:14:45'),
(12, 4, 'Comedia romántica', 'Romance con elementos humorísticos', '2025-02-03 21:14:45'),
(13, 5, 'Cuentos para niños', 'Literatura para lectores más jóvenes', '2025-02-03 21:14:45'),
(14, 5, 'Literatura juvenil', 'Literatura para adolescentes', '2025-02-03 21:14:45'),
(15, 5, 'Fábulas y cuentos clásicos', 'Historias tradicionales y moralejas', '2025-02-03 21:14:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `imagen_cliente` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `password` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellido`, `email`, `telefono`, `direccion`, `imagen_cliente`, `fecha_registro`, `estado`, `password`) VALUES
(1, 'Ana', 'Martínez', 'ana.martinez@email.com', '555-0101', 'Calle Principal 123', NULL, '2025-01-31 18:02:11', 'Activo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(2, 'Carlos', 'Rodríguez', 'carlos.rodriguez@email.com', '555-0102', 'Avenida Central 456', NULL, '2025-01-31 18:02:11', 'Activo', ''),
(3, 'María', 'López', 'maria.lopez@email.com', '555-0103', 'Plaza Mayor 789', NULL, '2025-01-31 18:02:11', 'Activo', ''),
(4, 'Pedro', 'Sánchez', 'pedro.sanchez@email.com', '555-0104', 'Calle Secundaria 321', NULL, '2025-01-31 18:02:11', 'Activo', ''),
(5, 'Joel', 'cano', 'sss@g.com', '4412345693', 'ahuacatlan de guadalupe', 'img/usuarios/679ea0f1356ea.jpg', '2025-02-01 22:23:45', 'Activo', '$2y$10$/UnCWWwXHtC6TZU8vdFr3OzFq4U5LGuSV7Ufe789dK.d02b/WdBAq'),
(6, 'Juann', 'Pérez', 'juan@ejemplo.com', '1234567890', 'Calle Principal #123', NULL, '2025-02-04 02:46:46', 'Activo', '$2y$10$l7Nr.myHwhFtk0xV5GPmuuNoxzBzW2RhGVQpPxPeaq6W9b/ooC/8.'),
(7, 'Mario', 'Alcaraz', '1sss@g.com', '0000012346', 'Colonia ermita CD México', 'img/usuarios/67a1aeb45217b.jpg', '2025-02-04 06:07:48', 'Activo', '$2y$10$Dapd8TFpRfovpfB9g5s.R.XfX4EFPz5FD5pNB5.JWulBNi8ULxeoi'),
(9, 'marcos', 'camacho', 'camachomarcos590@gmail.com', '4411231647', 'jalpan de serra', 'img/usuarios/67edbfc67eea4.jpg', '2025-03-05 17:13:21', 'Activo', '$2y$10$AeEeKeDBrrIN.l/bQePSw..oLLvTE60PJKD9Q.DdKEUnemB8JWO7G'),
(10, '1', '1', '1@gmail.com', '4411231647', '', 'img/usuarios/67eccfa2202b9.jpg', '2025-04-02 04:42:27', 'Activo', '$2y$10$17NvmuK9YqBZDTX5IzL3s.ZRNwXmguFEkRPvKyiHSg4nGXO0OE2R2');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `nombre_biblioteca` varchar(100) DEFAULT 'BiblioTech',
  `email_contacto` varchar(100) DEFAULT NULL,
  `telefono_contacto` varchar(20) DEFAULT NULL,
  `dias_prestamo` int(11) DEFAULT 7,
  `max_prestamos_usuario` int(11) DEFAULT 3,
  `multa_dia_retraso` decimal(10,2) DEFAULT 1.00,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nombre_biblioteca`, `email_contacto`, `telefono_contacto`, `dias_prestamo`, `max_prestamos_usuario`, `multa_dia_retraso`, `fecha_actualizacion`) VALUES
(1, 'BiblioSis', 'bibliosissoporte@gmail.com', '555-0100', 7, 3, 10.00, '2025-03-07 04:11:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `editoriales`
--

CREATE TABLE `editoriales` (
  `id_editorial` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `pais` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `editoriales`
--

INSERT INTO `editoriales` (`id_editorial`, `nombre`, `pais`) VALUES
(1, 'Planeta', 'España'),
(2, 'Penguin Random House', 'Estados Unidos'),
(3, 'Anagrama', 'España'),
(4, 'Alfaguara', 'España');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `generos`
--

CREATE TABLE `generos` (
  `id_genero` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `genero_padre_id` int(11) DEFAULT NULL,
  `es_categoria` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `generos`
--

INSERT INTO `generos` (`id_genero`, `nombre`, `descripcion`, `genero_padre_id`, `es_categoria`) VALUES
(1, 'Ficción', 'Obras literarias basadas en la imaginación y creatividad del autor', NULL, 0),
(2, 'Fantasía y Ciencia Ficción', 'Obras que exploran mundos imaginarios y futuros posibles', NULL, 0),
(3, 'Misterio y Suspenso', 'Obras que generan intriga y tensión en el lector', NULL, 0),
(4, 'Romance', 'Obras centradas en relaciones románticas y sentimentales', NULL, 0),
(5, 'Infantil y Juvenil', 'Literatura dirigida específicamente a niños y jóvenes lectores', NULL, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--

CREATE TABLE `libros` (
  `id_libro` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `id_autor` int(11) DEFAULT NULL,
  `id_editorial` int(11) DEFAULT NULL,
  `id_genero` int(11) DEFAULT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `isbn` varchar(13) DEFAULT NULL,
  `año_publicacion` year(4) DEFAULT NULL,
  `cantidad_total` int(11) DEFAULT 1,
  `cantidad_disponible` int(11) DEFAULT 1,
  `imagen_portada` varchar(255) DEFAULT NULL,
  `sinopsis` text DEFAULT NULL,
  `estado` enum('Disponible','Prestado','No disponible') DEFAULT 'Disponible',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `libros`
--

INSERT INTO `libros` (`id_libro`, `titulo`, `id_autor`, `id_editorial`, `id_genero`, `id_categoria`, `isbn`, `año_publicacion`, `cantidad_total`, `cantidad_disponible`, `imagen_portada`, `sinopsis`, `estado`, `fecha_registro`) VALUES
(1, 'Cien años de soledad', 1, 1, 1, 2, '9780307474728', '1967', 5, 2, 'img/libros/679e96a44b4b4.jpg', 'Cien años de soledad&amp;quot; es una novela de Gabriel García Márquez que narra la historia de la familia Buendía y el pueblo de Macondo, un lugar ficticio que refleja muchas de las costumbres y anécdotas vividas por el autor durante su infancia en Aracataca, Colombia. La novela se desarrolla entre mediados del siglo XIX y mediados del siglo XX, época marcada por guerras civiles y el surgimiento de partidos políticos en Colombia.', 'Disponible', '2025-01-31 18:02:11'),
(2, 'El Aleph', 2, 3, 1, 2, '9788437604848', '1949', 2, 1, 'img/libros/679e97d6b1778.jpg', 'Dieciocho relatos filosóficos y sobrenaturales entre los que se encuentra uno de los relatos más admirados en el campo de la literatura: El Aleph. La mayoría de los cuentos reunidos en este libro pertenecen al género fantástico. Algunos surgieron a partir de crónicas policiales, de pinturas o simplemente de la visión de algún conventillo; otro explora el efecto que la inmortalidad causaría en los hombres; hay una glosa al Martín Fierro, sueños sobre la identidad personal y fantasías del tiempo.', 'Disponible', '2025-01-31 18:02:11'),
(3, 'La casa de los espíritus', 3, 2, 1, 2, '9780525433477', '1982', 4, 3, 'img/libros/67a15904dcf01.jpg', '&lt;br /&gt;\r\n&lt;b&gt;Deprecated&lt;/b&gt;:  htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in &lt;b&gt;C:xampphtdocsbibliotecaadminlibroseditar.php&lt;/b&gt; on line &lt;b&gt;285&lt;/b&gt;&lt;br /&gt;', 'Disponible', '2025-01-31 18:02:11'),
(4, 'La ciudad y los perros', 4, 4, 1, 2, '9788420471839', '1963', 2, 1, 'img/libros/67a15969506cd.jpg', '&lt;br /&gt;\r\n&lt;b&gt;Deprecated&lt;/b&gt;:  htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in &lt;b&gt;C:xampphtdocsbibliotecaadminlibroseditar.php&lt;/b&gt; on line &lt;b&gt;285&lt;/b&gt;&lt;br /&gt;', 'Prestado', '2025-01-31 18:02:11'),
(5, 'Crónica de una muerte anunciada', 1, 1, 1, 2, '9788437601774', '1981', 3, 3, 'img/libros/679ebae010b90.jpg', 'Anda, niña: dinos quién fue. Ella se demoró apenas el tiempo necesario para decir el nombre. Lo buscó en las tinieblas, lo encontró a primera vista entre los tantos y tantos nombres confundibles de este mundo y del otro, y lo dejó clavado en la pared con su dardo certero, como a una mariposa cuya sentencia estaba escrita desde siempre. Santiago Nasar dijo.', 'Disponible', '2025-01-31 18:15:16'),
(6, 'El amor en los tiempos del cólera', 1, 1, 1, 2, '9788497592447', '1985', 4, 3, 'img/libros/679ebb741b7f9.jpg', 'Era inevitable: el olor de las almendras amargas le recordaba siempre el destino de los amores contrariados. Así empieza una de las historias de amor más maravillosas de la literatura universal.', 'Disponible', '2025-01-31 18:15:16'),
(10, 'La Tregua', 17, 1, 1, 3, '9788420428956', '2021', 4, 3, 'img/libros/67a3f275daa09.jpg', 'Historia de un hombre que encuentra el amor maduro', 'Disponible', '2025-02-05 22:02:52'),
(12, 'Memorias del Águila y el Jaguar', 3, 3, 1, 3, '9788401337857', '2019', 5, 5, 'img/libros/67a3f27f5584d.jpg', 'Aventuras místicas en América', 'Disponible', '2025-02-05 22:02:52'),
(22, 'Juego de Tronos', 13, 1, 2, 4, '9788496204964', '2021', 6, 3, 'img/libros/67a3f1c9526d7.jpg', 'Primer libro de Canción de Hielo y Fuego', 'Disponible', '2025-02-05 22:05:34'),
(23, 'Memorias de Idhún', 16, 2, 2, 6, '9783467539639', '2020', 4, 3, 'img/libros/67a3f1dc4b569.jpg', 'Aventuras en un mundo mágico paralelo', 'Disponible', '2025-02-05 22:05:34'),
(24, 'Donde los árboles cantan', 16, 3, 2, 4, '9788437552799', '2019', 3, 3, 'img/libros/67a3f4910a01d.png', 'Historia de magia y aventura medieval', 'Disponible', '2025-02-05 22:05:34'),
(25, 'Asesinato en el Orient Express', 14, 1, 3, 7, '9788427198095', '2020', 5, 5, 'img/libros/67a3f2348d5bb.jpg', 'Un misterioso asesinato en un tren', 'Disponible', '2025-02-05 22:05:34'),
(26, 'Muerte en el Nilo', 14, 2, 3, 7, '9788427298105', '2019', 4, 4, 'img/libros/67a3f242f3184.jpg', 'Hercule Poirot investiga un crimen en Egipto', 'Disponible', '2025-02-05 22:05:34'),
(27, 'Diez Negritos', 14, 3, 3, 8, '9788427298119', '2018', 3, 3, 'img/libros/67a3f251e4eb3.jpg', 'Diez personas aisladas en una isla misteriosa', 'Disponible', '2025-02-05 22:05:34'),
(28, 'El Cuaderno de Noah', 15, 1, 4, 10, '9788415140287', '2020', 5, 5, 'img/libros/67a3e9a7e2f59.jpg', 'Una historia de amor a través del tiempo', 'Disponible', '2025-02-05 22:05:34'),
(29, 'Mensaje en una Botella', 15, 2, 4, 10, '9788455140290', '2019', 4, 4, 'img/libros/67a3f26045732.jpg', 'Romance y misterio en la costa', 'Disponible', '2025-02-05 22:05:34'),
(30, 'Noches de Tormenta', 15, 3, 4, 11, '9788415240306', '2018', 3, 3, 'img/libros/67a3f26b6ce30.jpg', 'Encuentro fortuito durante una tormenta', 'Disponible', '2025-02-05 22:05:34'),
(31, 'Kafka en la orilla', 23, 1, 2, 6, '9788483835180', '2002', 3, 3, 'img/libros/67f48116e96f0.jpg', 'Un joven de quince años, Kafka Tamura, se escapa de casa para escapar de la terrible profecía de su padre y encontrar a su madre y hermana. Después de varias peripecias, Kafka llega a una pintoresca biblioteca privada donde conoce a la misteriosa señorita Saeki y a Oshima, su mano derecha. Mientras, Nakata, viejo y casi analfabeto, pero con un don especial, se ve inmerso en una aventura que le llevará hasta Kafka.', 'Disponible', '2025-04-08 01:49:51'),
(32, 'Orgullo y prejuicio', 24, 2, 1, 2, '9788497940252', '2023', 4, 4, 'img/libros/67f48159f403f.jpg', 'La historia sigue a Elizabeth Bennet, la segunda de cinco hermanas de una familia rural inglesa. A pesar de la insistencia de su madre para que se case con un hombre rico, Elizabeth desea casarse por amor. Cuando conoce al apuesto y rico Señor Darcy, las chispas vuelan, pero el orgullo de él y los prejuicios de ella amenazan con mantenerlos separados.', 'Disponible', '2025-04-08 01:49:51'),
(33, 'Kindred: Lazos de sangre', 25, 3, 2, 5, '9788433960177', '1979', 2, 2, 'img/libros/67f4818da917a.jpg', 'Dana, una mujer afroamericana moderna, es inexplicablemente transportada desde 1976 a una plantación de la Maryland anterior a la Guerra Civil. Allí se encuentra con sus antepasados: un esclavo negro y su amo blanco. Cada vez que la vida de su antepasado está en peligro, Dana es llevada a través del tiempo para salvarlo.', 'Disponible', '2025-04-08 01:49:51'),
(34, 'La peste', 26, 4, 1, 2, '9788420674292', '1947', 3, 1, 'img/libros/67f481ba57800.jpg', 'Ambientada en los años 40 en Orán, una ciudad cerrada a causa de una epidemia de peste que asola a la población. La novela narra la lucha contra la enfermedad por parte del doctor Rieux y otros personajes, convirtiéndose en una metáfora sobre el mal y la condición humana.', 'Disponible', '2025-04-08 01:49:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_cuenta` enum('cliente','usuario') NOT NULL,
  `token` varchar(64) NOT NULL,
  `expira` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `password_resets`
--

INSERT INTO `password_resets` (`id`, `id_usuario`, `tipo_cuenta`, `token`, `expira`, `usado`, `fecha_creacion`) VALUES
(1, 15, 'cliente', 'dd6b913752eb5391966d81147dbd73c388d121afeae6a70f5aa4b7b47ffd94d5', '2025-02-04 18:57:14', 0, '2025-02-04 16:57:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiracion` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `email`, `token`, `expiracion`, `created_at`) VALUES
(1, 'camachomarcos590@gmail.com', '8bcccfdcbe03a6f213c264574b9541d53c3d049ff2dc7c9747d05aeabf6b646c', '2025-03-06 19:31:18', '2025-03-07 00:16:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

CREATE TABLE `prestamos` (
  `id_prestamo` int(11) NOT NULL,
  `id_libro` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_prestamo` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_devolucion_esperada` date NOT NULL,
  `fecha_devolucion_real` date DEFAULT NULL,
  `estado` enum('Pendiente','Aprobado','Rechazado','Prestado','Devuelto','Atrasado') DEFAULT 'Pendiente',
  `observaciones` text DEFAULT NULL,
  `observaciones_devolucion` text DEFAULT NULL,
  `comentario_revision` text DEFAULT NULL,
  `id_usuario_aprobacion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prestamos`
--

INSERT INTO `prestamos` (`id_prestamo`, `id_libro`, `id_cliente`, `id_usuario`, `fecha_prestamo`, `fecha_devolucion_esperada`, `fecha_devolucion_real`, `estado`, `observaciones`, `observaciones_devolucion`, `comentario_revision`, `id_usuario_aprobacion`) VALUES
(4, 4, 4, 1, '2025-01-31 18:02:11', '2025-01-17', '2025-01-24', 'Devuelto', NULL, NULL, NULL, NULL),
(5, 1, 1, 1, '2025-02-01 22:36:54', '2025-02-08', '2025-02-01', 'Devuelto', '\nDevuelto el 2025-02-01', NULL, NULL, NULL),
(6, 2, 5, 1, '2025-02-03 04:53:14', '2025-02-09', '2025-02-03', 'Devuelto', '', NULL, NULL, NULL),
(7, 5, 1, 1, '2025-02-03 19:20:23', '2025-02-10', '2025-02-03', 'Devuelto', '', NULL, NULL, NULL),
(8, 6, 1, NULL, '2025-02-04 02:15:52', '2025-02-10', '2025-02-03', 'Devuelto', NULL, NULL, NULL, NULL),
(9, 5, 6, NULL, '2025-02-04 05:27:33', '2025-02-10', '2025-02-03', 'Devuelto', NULL, '', NULL, NULL),
(10, 5, 6, NULL, '2025-02-04 05:34:15', '2025-02-10', '2025-02-03', 'Devuelto', NULL, '', NULL, NULL),
(11, 5, 1, NULL, '2025-02-04 20:17:38', '2025-02-11', '2025-02-05', 'Devuelto', NULL, NULL, NULL, NULL),
(12, 22, 7, NULL, '2025-02-06 23:28:23', '2025-02-13', '2025-02-20', 'Devuelto', NULL, NULL, NULL, NULL),
(13, 23, 7, NULL, '2025-02-21 00:54:00', '2025-03-06', '2025-02-20', 'Devuelto', 'Devuelto el 2025-02-20: ', NULL, NULL, NULL),
(14, 22, 1, NULL, '2025-03-07 04:04:42', '2025-03-13', '2025-03-06', 'Devuelto', 'Devuelto el 2025-03-06: ', NULL, NULL, NULL),
(15, 22, 9, NULL, '2025-03-12 22:35:23', '2025-03-19', '2025-04-01', 'Devuelto', NULL, NULL, NULL, NULL),
(16, 5, 9, NULL, '2025-03-12 22:36:50', '2025-03-19', '2025-04-01', 'Devuelto', NULL, NULL, NULL, NULL),
(17, 5, 9, NULL, '2025-04-02 04:40:07', '2025-04-08', '2025-04-01', 'Devuelto', 'Devuelto el 2025-04-01: ', NULL, NULL, NULL),
(18, 5, 9, NULL, '2025-04-02 04:41:26', '2025-04-08', '2025-04-01', 'Devuelto', NULL, NULL, NULL, NULL),
(19, 28, 10, NULL, '2025-04-02 05:24:55', '2025-04-08', '2025-04-01', 'Devuelto', 'Devuelto el 2025-04-01: ', NULL, NULL, NULL),
(20, 5, 10, NULL, '2025-04-02 05:26:08', '2025-04-08', '2025-04-01', 'Devuelto', 'Devuelto el 2025-04-01: ', NULL, NULL, NULL),
(21, 5, 10, NULL, '2025-04-02 05:27:22', '2025-04-08', '2025-04-01', 'Devuelto', 'Devuelto el 2025-04-01: ', NULL, NULL, NULL),
(22, 10, 10, NULL, '2025-04-02 05:27:46', '2025-04-08', '2025-04-01', 'Devuelto', 'Devuelto el 2025-04-01: ', NULL, NULL, NULL),
(23, 5, 10, NULL, '2025-04-02 05:33:03', '2025-04-08', '2025-04-01', 'Devuelto', 'Devuelto el 2025-04-01: ', NULL, NULL, NULL),
(24, 28, 10, NULL, '2025-04-02 05:33:46', '2025-04-08', '2025-04-01', 'Devuelto', 'Devuelto el 2025-04-01: ', NULL, NULL, NULL),
(25, 22, 9, NULL, '2025-04-02 16:52:58', '2025-04-09', '2025-04-02', 'Devuelto', 'Préstamo realizado desde carrito\nDevuelto el 2025-04-02: ', NULL, NULL, NULL),
(26, 23, 9, NULL, '2025-04-02 16:52:58', '2025-04-09', '2025-04-02', 'Devuelto', 'Préstamo realizado desde carrito\nDevuelto el 2025-04-02: ', NULL, NULL, NULL),
(27, 22, 9, NULL, '2025-04-02 22:38:18', '2025-04-09', '2025-04-02', 'Devuelto', 'Préstamo realizado desde carrito', NULL, NULL, NULL),
(28, 23, 9, NULL, '2025-04-02 22:38:18', '2025-04-09', '2025-04-02', 'Devuelto', 'Préstamo realizado desde carrito\nDevuelto el 2025-04-02: muy buen libro lo recomiendo', NULL, NULL, NULL),
(29, 25, 9, NULL, '2025-04-02 22:49:11', '2025-04-09', '2025-04-03', 'Devuelto', 'Devuelto el 2025-04-03: ', NULL, NULL, NULL),
(30, 22, 9, NULL, '2025-04-03 04:31:21', '2025-04-09', '2025-04-03', 'Devuelto', 'Solicitud realizada desde carrito, pendiente de aprobación\nDevuelto el 2025-04-03: ', NULL, '', 1),
(31, 22, 9, NULL, '2025-04-03 06:06:04', '2025-04-10', '2025-04-08', 'Devuelto', 'Solicitud realizada desde carrito, pendiente de aprobación', NULL, '1', 14),
(32, 23, 9, NULL, '2025-04-03 06:06:04', '2025-04-10', '2025-04-08', 'Devuelto', 'Solicitud realizada desde carrito, pendiente de aprobación', NULL, '1', 14),
(33, 1, 9, 1, '2025-04-03 21:31:25', '2025-04-10', '2025-04-08', 'Devuelto', '', NULL, '', 1),
(34, 22, 1, NULL, '2025-04-04 00:29:37', '2025-04-10', NULL, 'Rechazado', 'Solicitud realizada desde carrito, pendiente de aprobación', NULL, '', 1),
(35, 34, 9, NULL, '2025-04-08 18:48:12', '2025-04-22', NULL, 'Prestado', 'Solicitud pendiente de aprobación por personal de biblioteca', NULL, '', 1),
(36, 5, 9, NULL, '2025-04-08 19:12:17', '2025-04-22', NULL, 'Pendiente', 'Solicitud realizada desde carrito, pendiente de aprobación', NULL, NULL, NULL),
(37, 32, 9, NULL, '2025-04-08 19:12:17', '2025-04-22', NULL, 'Pendiente', 'Solicitud realizada desde carrito, pendiente de aprobación', NULL, NULL, NULL);

--
-- Disparadores `prestamos`
--
DELIMITER $$
CREATE TRIGGER `after_prestamo_insert` AFTER INSERT ON `prestamos` FOR EACH ROW BEGIN
    -- Solo actualiza el libro si el préstamo es Aprobado o Prestado (no en Pendiente)
    IF NEW.estado IN ('Aprobado', 'Prestado') THEN
        UPDATE libros 
        SET cantidad_disponible = cantidad_disponible - 1,
            estado = CASE 
                WHEN cantidad_disponible - 1 = 0 THEN 'Prestado'
                ELSE estado 
            END
        WHERE id_libro = NEW.id_libro;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_prestamo_update` AFTER UPDATE ON `prestamos` FOR EACH ROW BEGIN
    -- Si el estado cambia de Pendiente a Aprobado/Prestado, actualizar la disponibilidad
    IF (OLD.estado = 'Pendiente' AND NEW.estado IN ('Aprobado', 'Prestado')) THEN
        UPDATE libros 
        SET cantidad_disponible = cantidad_disponible - 1,
            estado = CASE 
                WHEN cantidad_disponible - 1 = 0 THEN 'Prestado'
                ELSE estado 
            END
        WHERE id_libro = NEW.id_libro;
    -- Si el estado cambia a Devuelto, aumentar disponibilidad
    ELSEIF (OLD.estado IN ('Aprobado', 'Prestado') AND NEW.estado = 'Devuelto') THEN
        UPDATE libros 
        SET cantidad_disponible = cantidad_disponible + 1,
            estado = CASE 
                WHEN cantidad_disponible + 1 > 0 THEN 'Disponible'
                ELSE estado 
            END
        WHERE id_libro = NEW.id_libro;
    -- Si el préstamo es rechazado, no se modifica la disponibilidad
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'Administrador'),
(2, 'Bibliotecario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `email`, `password`, `id_rol`, `fecha_registro`, `estado`) VALUES
(1, 'Adminn', 'Sistema', 'admin@biblioteca.com', '$2y$10$/UWmkwBpV8.3AGNh51h6OOTeAokGd/PZ1z0.uUbE5d2NKoM4irlw.', 1, '2025-01-31 05:08:07', 'Activo'),
(14, 'bibliotecario', 'Sistema', 'biblioteca@biblioteca.com', '$2y$10$sN7g9LrT.RMTEllC02Cp7enFxez/FkW6Wk3q/E/owi0wq48SOuhAO', 2, '2025-02-01 20:09:48', 'Activo'),
(15, 'ddd', 'Pérez', '1sss@g.com', '$2y$10$S3TG1IydZlrn1Bv8hvN4GORVriF0.ZfXiwxgoKMy6QwKrGROsgb6u', 2, '2025-02-02 00:00:19', 'Activo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `autores`
--
ALTER TABLE `autores`
  ADD PRIMARY KEY (`id_autor`);

--
-- Indices de la tabla `carrito_prestamos`
--
ALTER TABLE `carrito_prestamos`
  ADD PRIMARY KEY (`id_carrito`),
  ADD UNIQUE KEY `idx_cliente_libro` (`id_cliente`,`id_libro`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_libro` (`id_libro`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD KEY `categorias_ibfk_1` (`id_genero`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `editoriales`
--
ALTER TABLE `editoriales`
  ADD PRIMARY KEY (`id_editorial`);

--
-- Indices de la tabla `generos`
--
ALTER TABLE `generos`
  ADD PRIMARY KEY (`id_genero`),
  ADD KEY `genero_padre_id` (`genero_padre_id`);

--
-- Indices de la tabla `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`id_libro`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `id_autor` (`id_autor`),
  ADD KEY `id_editorial` (`id_editorial`),
  ADD KEY `libros_ibfk_3` (`id_genero`),
  ADD KEY `libros_ibfk_5` (`id_categoria`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD UNIQUE KEY `unique_token` (`token`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id_prestamo`),
  ADD KEY `id_libro` (`id_libro`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `fk_usuario_aprobacion` (`id_usuario_aprobacion`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `autores`
--
ALTER TABLE `autores`
  MODIFY `id_autor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `carrito_prestamos`
--
ALTER TABLE `carrito_prestamos`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `editoriales`
--
ALTER TABLE `editoriales`
  MODIFY `id_editorial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `generos`
--
ALTER TABLE `generos`
  MODIFY `id_genero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `libros`
--
ALTER TABLE `libros`
  MODIFY `id_libro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id_prestamo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito_prestamos`
--
ALTER TABLE `carrito_prestamos`
  ADD CONSTRAINT `carrito_prestamos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_prestamos_ibfk_2` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id_libro`) ON DELETE CASCADE;

--
-- Filtros para la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`id_genero`) REFERENCES `generos` (`id_genero`);

--
-- Filtros para la tabla `generos`
--
ALTER TABLE `generos`
  ADD CONSTRAINT `generos_ibfk_1` FOREIGN KEY (`genero_padre_id`) REFERENCES `generos` (`id_genero`);

--
-- Filtros para la tabla `libros`
--
ALTER TABLE `libros`
  ADD CONSTRAINT `libros_ibfk_1` FOREIGN KEY (`id_autor`) REFERENCES `autores` (`id_autor`),
  ADD CONSTRAINT `libros_ibfk_2` FOREIGN KEY (`id_editorial`) REFERENCES `editoriales` (`id_editorial`),
  ADD CONSTRAINT `libros_ibfk_3` FOREIGN KEY (`id_genero`) REFERENCES `generos` (`id_genero`),
  ADD CONSTRAINT `libros_ibfk_5` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);

--
-- Filtros para la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `fk_usuario_aprobacion` FOREIGN KEY (`id_usuario_aprobacion`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `prestamos_ibfk_1` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id_libro`),
  ADD CONSTRAINT `prestamos_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `prestamos_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
