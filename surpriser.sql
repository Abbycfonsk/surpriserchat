-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 01-05-2026 a las 19:51:45
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `surpriser`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `model` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `model`, `model_id`, `old_values`, `new_values`, `ip`, `user_agent`, `created_at`, `updated_at`) VALUES
(3, 1, 'surprise_created', 'Surprise', 88, NULL, '{\"title\": \"Cumpleaños\", \"price\": 50}', '192.168.1.22', 'Mozilla/5.0', '2026-04-16 10:01:28', '2026-04-16 10:01:28'),
(4, 9, 'offer_created', 'Offer', 4, NULL, '{\"surprise_id\":20,\"genius_id\":9,\"price\":80,\"message\":\"Puedo tenerlo listo en 3 d\\u00edas \\ud83d\\ude0a\",\"eta_hours\":72,\"status\":\"pending\",\"creator_bid_count\":0,\"genius_bid_count\":0,\"updated_at\":\"2026-04-18T13:59:40.000000Z\",\"created_at\":\"2026-04-18T13:59:40.000000Z\",\"id\":4}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 11:59:40', '2026-04-18 11:59:40'),
(5, 9, 'offer_countered', 'Offer', 4, '{\"id\":4,\"surprise_id\":20,\"genius_id\":9,\"price\":\"80.00\",\"message\":\"Puedo tenerlo listo en 3 d\\u00edas \\ud83d\\ude0a\",\"eta_hours\":72,\"status\":\"pending\",\"creator_bid_count\":0,\"genius_bid_count\":0,\"created_at\":\"2026-04-18T13:59:40.000000Z\",\"updated_at\":\"2026-04-18T13:59:40.000000Z\"}', '{\"price\":70,\"message\":\"Si lo dejas en 70 cerramos\",\"status\":\"negotiating\",\"genius_bid_count\":1,\"updated_at\":\"2026-04-18 14:02:38\"}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 12:02:38', '2026-04-18 12:02:38'),
(6, 8, 'offer_accepted', 'Offer', 4, NULL, '{\"id\":4,\"surprise_id\":20,\"genius_id\":9,\"price\":\"70.00\",\"message\":\"Si lo dejas en 70 cerramos\",\"eta_hours\":72,\"status\":\"accepted\",\"creator_bid_count\":0,\"genius_bid_count\":1,\"created_at\":\"2026-04-18T13:59:40.000000Z\",\"updated_at\":\"2026-04-18T14:08:35.000000Z\",\"surprise\":{\"id\":20,\"creator_id\":8,\"genius_id\":9,\"skill_id\":3,\"title\":\"Logo minimalista actualizadosssss\",\"description\":\"Quiero un logo simple, limpio, en blanco y negro, con tipograf\\u00eda sans serif.\",\"size\":\"MEDIUM\",\"is_urgent\":1,\"rating_for_genius\":null,\"status\":\"in_progress\",\"price\":\"45.00\",\"deadline\":\"2026-04-25 18:00:00\",\"target_name\":\"Mar\\u00eda\",\"target_city\":\"Barcelona\",\"target_country\":\"Espa\\u00f1a\",\"target_lat\":\"41.3874000\",\"target_lng\":\"2.1686000\",\"price_creator\":null,\"price_genius\":\"70.00\",\"final_price\":\"70.00\",\"created_at\":\"2026-04-12T16:30:08.000000Z\",\"updated_at\":\"2026-04-18T14:08:35.000000Z\",\"deadline_warning_sent\":0,\"completed_at\":null}}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 12:08:35', '2026-04-18 12:08:35'),
(7, 8, 'surprise_created', 'Surprise', 25, NULL, '{\"creator_id\":3,\"title\":\"Necesito una web negra\",\"description\":\"Algo limpio, moderno y elegante\",\"status\":\"open\",\"price\":null,\"deadline\":\"2026-04-20 18:00:00\",\"skill_id\":1,\"size\":\"MEDIUM\",\"is_urgent\":false,\"updated_at\":\"2026-04-18T14:10:59.000000Z\",\"created_at\":\"2026-04-18T14:10:59.000000Z\",\"id\":25}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 12:10:59', '2026-04-18 12:10:59'),
(8, 9, 'offer_created', 'Offer', 5, NULL, '{\"surprise_id\":25,\"genius_id\":9,\"price\":120,\"message\":\"Puedo hacerlo en 48 horas\",\"eta_hours\":48,\"status\":\"pending\",\"creator_bid_count\":0,\"genius_bid_count\":0,\"updated_at\":\"2026-04-18T14:13:04.000000Z\",\"created_at\":\"2026-04-18T14:13:04.000000Z\",\"id\":5}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 12:13:04', '2026-04-18 12:13:04'),
(9, 8, 'offer_countered', 'Offer', 5, '{\"id\":5,\"surprise_id\":25,\"genius_id\":9,\"price\":\"120.00\",\"message\":\"Puedo hacerlo en 48 horas\",\"eta_hours\":48,\"status\":\"pending\",\"creator_bid_count\":0,\"genius_bid_count\":0,\"created_at\":\"2026-04-18T14:13:04.000000Z\",\"updated_at\":\"2026-04-18T14:13:04.000000Z\"}', '{\"price\":100,\"message\":\"Si lo dejas en 100 lo cerramos\",\"status\":\"negotiating\",\"creator_bid_count\":1,\"updated_at\":\"2026-04-18 14:15:05\"}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 12:15:05', '2026-04-18 12:15:05'),
(10, 9, 'offer_countered', 'Offer', 5, '{\"id\":5,\"surprise_id\":25,\"genius_id\":9,\"price\":\"100.00\",\"message\":\"Si lo dejas en 100 lo cerramos\",\"eta_hours\":48,\"status\":\"negotiating\",\"creator_bid_count\":1,\"genius_bid_count\":0,\"created_at\":\"2026-04-18T14:13:04.000000Z\",\"updated_at\":\"2026-04-18T14:15:05.000000Z\"}', '{\"price\":110,\"message\":\"Puedo bajarlo a 110\",\"genius_bid_count\":1,\"updated_at\":\"2026-04-18 14:16:51\"}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 12:16:51', '2026-04-18 12:16:51'),
(11, 8, 'offer_accepted', 'Offer', 5, NULL, '{\"id\":5,\"surprise_id\":25,\"genius_id\":9,\"price\":\"110.00\",\"message\":\"Puedo bajarlo a 110\",\"eta_hours\":48,\"status\":\"accepted\",\"creator_bid_count\":1,\"genius_bid_count\":1,\"created_at\":\"2026-04-18T14:13:04.000000Z\",\"updated_at\":\"2026-04-18T14:19:48.000000Z\",\"surprise\":{\"id\":25,\"creator_id\":8,\"genius_id\":9,\"skill_id\":1,\"title\":\"Necesito una web negra\",\"description\":\"Algo limpio, moderno y elegante\",\"size\":\"MEDIUM\",\"is_urgent\":0,\"rating_for_genius\":null,\"status\":\"in_progress\",\"price\":null,\"deadline\":\"2026-04-20 18:00:00\",\"target_name\":null,\"target_city\":null,\"target_country\":null,\"target_lat\":null,\"target_lng\":null,\"price_creator\":null,\"price_genius\":\"110.00\",\"final_price\":\"110.00\",\"created_at\":\"2026-04-18T14:10:59.000000Z\",\"updated_at\":\"2026-04-18T14:19:48.000000Z\",\"deadline_warning_sent\":0,\"completed_at\":null}}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 12:19:48', '2026-04-18 12:19:48'),
(12, 8, 'message_sent', 'Message', 1, NULL, '{\"conversation_id\":1,\"sender_id\":8,\"content\":\"Hola genius! Empezamos con la sorpresa?\",\"image\":null,\"updated_at\":\"2026-04-18T15:09:09.000000Z\",\"created_at\":\"2026-04-18T15:09:09.000000Z\",\"id\":1}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 13:09:09', '2026-04-18 13:09:09'),
(13, 9, 'message_sent', 'Message', 2, NULL, '{\"conversation_id\":1,\"sender_id\":9,\"content\":\"Perfecto! Ya estoy trabajando en ello.\",\"image\":null,\"updated_at\":\"2026-04-18T15:11:08.000000Z\",\"created_at\":\"2026-04-18T15:11:08.000000Z\",\"id\":2}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 13:11:08', '2026-04-18 13:11:08'),
(14, 8, 'message_sent', 'Message', 3, NULL, '{\"conversation_id\":1,\"sender_id\":8,\"content\":\"aqui tienes el archivo\",\"image\":\"messages\\/VFjZEXXLxzZ8tooV7XYmg3JsAzatoeOMaD22g26p.pdf\",\"updated_at\":\"2026-04-18T15:29:56.000000Z\",\"created_at\":\"2026-04-18T15:29:56.000000Z\",\"id\":3}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 13:29:56', '2026-04-18 13:29:56'),
(15, 8, 'message_sent', 'Message', 4, NULL, '{\"conversation_id\":1,\"sender_id\":8,\"content\":\"aqui tienes el archivo\",\"image\":\"messages\\/qQPk8dAg9QQOR1D5tnHDw8AnuWzFKH0QAY5c5sGt.jpg\",\"updated_at\":\"2026-04-18T15:30:22.000000Z\",\"created_at\":\"2026-04-18T15:30:22.000000Z\",\"id\":4}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 13:30:22', '2026-04-18 13:30:22'),
(16, 8, 'message_sent', 'Message', 5, NULL, '{\"conversation_id\":1,\"sender_id\":8,\"content\":\"aqui tienes el archivo\",\"image\":\"messages\\/DjO7egVPHmMMJoSgTNrRMBSuTGl98aLRw4yVNhnm.jpg\",\"updated_at\":\"2026-04-18T15:30:48.000000Z\",\"created_at\":\"2026-04-18T15:30:48.000000Z\",\"id\":5}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 13:30:48', '2026-04-18 13:30:48'),
(17, 8, 'message_sent', 'Message', 6, NULL, '{\"conversation_id\":1,\"sender_id\":8,\"content\":\"aqui tienes el archivo\",\"image\":\"messages\\/0EwLvbcHJjNd8eZ2OAnmqMFTohqs9Zolr7NDfJJj.jpg\",\"updated_at\":\"2026-04-18T15:32:20.000000Z\",\"created_at\":\"2026-04-18T15:32:20.000000Z\",\"id\":6}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 13:32:20', '2026-04-18 13:32:20'),
(18, 8, 'message_sent', 'Message', 7, NULL, '{\"conversation_id\":1,\"sender_id\":8,\"content\":\"aqui tienes el archivo\",\"image\":\"messages\\/EHLpPNuBOY7AeTiNPkRKeYjrP56RU2JZ1ugUefTl.png\",\"updated_at\":\"2026-04-18T15:33:32.000000Z\",\"created_at\":\"2026-04-18T15:33:32.000000Z\",\"id\":7}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 13:33:32', '2026-04-18 13:33:32'),
(19, 9, 'message_sent', 'Message', 8, NULL, '{\"conversation_id\":1,\"sender_id\":9,\"content\":\"recibido\",\"image\":\"messages\\/z7dOnoZnZHlDZsVCXoPOA395IpUv9Ps13BRt5CKG.pdf\",\"updated_at\":\"2026-04-18T15:39:02.000000Z\",\"created_at\":\"2026-04-18T15:39:02.000000Z\",\"id\":8}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 13:39:02', '2026-04-18 13:39:02'),
(20, 8, 'message_sent', 'Message', 9, NULL, '{\"conversation_id\":1,\"sender_id\":8,\"content\":\"ya veo el archivo\",\"image\":null,\"updated_at\":\"2026-04-18T15:39:36.000000Z\",\"created_at\":\"2026-04-18T15:39:36.000000Z\",\"id\":9}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 13:39:36', '2026-04-18 13:39:36'),
(21, 9, 'message_sent', 'Message', 10, NULL, '{\"conversation_id\":1,\"sender_id\":9,\"content\":\"vale te lomiras y me dices\",\"image\":null,\"updated_at\":\"2026-04-18T15:39:54.000000Z\",\"created_at\":\"2026-04-18T15:39:54.000000Z\",\"id\":10}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 13:39:54', '2026-04-18 13:39:54'),
(22, 9, 'message_sent', 'Message', 11, NULL, '{\"conversation_id\":1,\"sender_id\":9,\"content\":\"vale te lomiras y me dices\",\"image\":null,\"updated_at\":\"2026-04-18T15:40:07.000000Z\",\"created_at\":\"2026-04-18T15:40:07.000000Z\",\"id\":11}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 13:40:07', '2026-04-18 13:40:07'),
(23, 8, 'message_sent', 'Message', 12, NULL, '{\"conversation_id\":1,\"sender_id\":8,\"content\":\"kahfiuagf f ugafgasif gha fgafia f afa fa f ahf auhfiuahf aufhiufhaiufha faf a fuahfiuahsfi uahfu faf.fiu fhiuahf afaif s gsa g g. g a ga g aeg. ge h. rth r.  wr h wrhwh wwr hw rhw wy w hw rh wr hw h w w. wr hwr. wr hj wr hw r hw rh w r\",\"image\":null,\"updated_at\":\"2026-04-18T15:40:57.000000Z\",\"created_at\":\"2026-04-18T15:40:57.000000Z\",\"id\":12}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-18 13:40:57', '2026-04-18 13:40:57'),
(24, 8, 'dispute_resolved', 'Dispute', 1, '{\"status\":\"open\"}', '{\"status\":\"resolved\",\"winner\":\"creator\"}', '127.0.0.1', 'PostmanRuntime/7.53.0', '2026-04-26 07:36:50', '2026-04-26 07:36:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('surpriser-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:1;', 1777196208),
('surpriser-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1777196208;', 1777196208),
('surpriser-cache-creator1@example.com|127.0.0.1', 'i:1;', 1777196208),
('surpriser-cache-creator1@example.com|127.0.0.1:timer', 'i:1777196208;', 1777196208);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cancellations`
--

CREATE TABLE `cancellations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `surprise_id` bigint(20) UNSIGNED NOT NULL,
  `genius_id` bigint(20) UNSIGNED DEFAULT NULL,
  `creator_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cancelled_by` enum('creator','genius','admin') NOT NULL,
  `reason_key` enum('illness','personal_issue','force_majeure','technical_issue','no_time','uncomfortable','cant_now','no_reason') NOT NULL,
  `reason_text` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conversations`
--

CREATE TABLE `conversations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `surprise_id` bigint(20) UNSIGNED NOT NULL,
  `creator_id` bigint(20) UNSIGNED NOT NULL,
  `genius_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `conversations`
--

INSERT INTO `conversations` (`id`, `surprise_id`, `creator_id`, `genius_id`, `created_at`, `updated_at`) VALUES
(1, 20, 8, 9, '2026-04-18 13:08:13', '2026-04-18 13:08:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `disputes`
--

CREATE TABLE `disputes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `surprise_id` bigint(20) UNSIGNED NOT NULL,
  `creator_id` bigint(20) UNSIGNED NOT NULL,
  `genius_id` bigint(20) UNSIGNED NOT NULL,
  `opened_by` bigint(20) UNSIGNED NOT NULL,
  `reason` text NOT NULL,
  `status` enum('open','resolved') NOT NULL DEFAULT 'open',
  `resolution` text DEFAULT NULL,
  `winner` enum('creator','genius','none') DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `disputes`
--

INSERT INTO `disputes` (`id`, `surprise_id`, `creator_id`, `genius_id`, `opened_by`, `reason`, `status`, `resolution`, `winner`, `resolved_at`, `created_at`, `updated_at`) VALUES
(1, 19, 8, 9, 9, 'El genio no entregó lo que pedí.', 'resolved', 'El genio no cumplió los requisitos', 'creator', '2026-04-26 07:36:50', '2026-04-20 09:55:44', '2026-04-26 07:36:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispute_penalties`
--

CREATE TABLE `dispute_penalties` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dispute_id` bigint(20) UNSIGNED NOT NULL,
  `genius_id` bigint(20) UNSIGNED NOT NULL,
  `penalty_level` enum('warning','suspension_7d','suspension_30d','ban_permanent') NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dispute_penalties`
--

INSERT INTO `dispute_penalties` (`id`, `dispute_id`, `genius_id`, `penalty_level`, `starts_at`, `ends_at`, `created_at`, `updated_at`) VALUES
(1, 1, 9, 'warning', '2026-04-26 09:36:50', NULL, '2026-04-26 07:36:50', '2026-04-26 07:36:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `genius_point_events`
--

CREATE TABLE `genius_point_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `genius_id` bigint(20) UNSIGNED NOT NULL,
  `surprise_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(100) NOT NULL,
  `points_delta` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `genius_point_events`
--

INSERT INTO `genius_point_events` (`id`, `genius_id`, `surprise_id`, `type`, `points_delta`, `created_at`, `updated_at`) VALUES
(1, 9, 11, 'COMPLETE', 5, '2026-04-12 01:37:17', '2026-04-12 01:37:17'),
(2, 9, 11, 'EARLY_DELIVERY', 2, '2026-04-12 01:37:17', '2026-04-12 01:37:17'),
(3, 9, 12, 'COMPLETE', 5, '2026-04-12 01:58:10', '2026-04-12 01:58:10'),
(4, 9, 12, 'EARLY_DELIVERY', 2, '2026-04-12 01:58:10', '2026-04-12 01:58:10'),
(5, 9, 13, 'COMPLETE', 5, '2026-04-12 01:58:32', '2026-04-12 01:58:32'),
(6, 9, 14, 'COMPLETE', 5, '2026-04-12 02:08:16', '2026-04-12 02:08:16'),
(7, 9, 14, 'EARLY_DELIVERY', 2, '2026-04-12 02:08:16', '2026-04-12 02:08:16'),
(8, 9, 15, 'COMPLETE', 5, '2026-04-12 02:08:21', '2026-04-12 02:08:21'),
(9, 9, 15, 'EARLY_DELIVERY', 2, '2026-04-12 02:08:21', '2026-04-12 02:08:21'),
(10, 9, 16, 'COMPLETE', 5, '2026-04-12 02:08:25', '2026-04-12 02:08:25'),
(11, 9, 16, 'EARLY_DELIVERY', 2, '2026-04-12 02:08:25', '2026-04-12 02:08:25'),
(12, 9, 17, 'COMPLETE', 5, '2026-04-12 02:08:28', '2026-04-12 02:08:28'),
(13, 9, 17, 'EARLY_DELIVERY', 2, '2026-04-12 02:08:28', '2026-04-12 02:08:28'),
(14, 9, 18, 'COMPLETE', 5, '2026-04-12 02:08:32', '2026-04-12 02:08:32'),
(15, 9, 18, 'EARLY_DELIVERY', 2, '2026-04-12 02:08:32', '2026-04-12 02:08:32'),
(16, 9, 19, 'COMPLETE', 5, '2026-04-12 02:08:37', '2026-04-12 02:08:37'),
(17, 9, 19, 'EARLY_DELIVERY', 2, '2026-04-12 02:08:37', '2026-04-12 02:08:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `content`, `image`, `created_at`, `updated_at`) VALUES
(1, 1, 8, 'Hola genius! Empezamos con la sorpresa?', NULL, '2026-04-18 13:09:09', '2026-04-18 13:09:09'),
(2, 1, 9, 'Perfecto! Ya estoy trabajando en ello.', NULL, '2026-04-18 13:11:08', '2026-04-18 13:11:08'),
(3, 1, 8, 'aqui tienes el archivo', 'messages/VFjZEXXLxzZ8tooV7XYmg3JsAzatoeOMaD22g26p.pdf', '2026-04-18 13:29:56', '2026-04-18 13:29:56'),
(4, 1, 8, 'aqui tienes el archivo', 'messages/qQPk8dAg9QQOR1D5tnHDw8AnuWzFKH0QAY5c5sGt.jpg', '2026-04-18 13:30:22', '2026-04-18 13:30:22'),
(5, 1, 8, 'aqui tienes el archivo', 'messages/DjO7egVPHmMMJoSgTNrRMBSuTGl98aLRw4yVNhnm.jpg', '2026-04-18 13:30:48', '2026-04-18 13:30:48'),
(6, 1, 8, 'aqui tienes el archivo', 'messages/0EwLvbcHJjNd8eZ2OAnmqMFTohqs9Zolr7NDfJJj.jpg', '2026-04-18 13:32:20', '2026-04-18 13:32:20'),
(7, 1, 8, 'aqui tienes el archivo', 'messages/EHLpPNuBOY7AeTiNPkRKeYjrP56RU2JZ1ugUefTl.png', '2026-04-18 13:33:32', '2026-04-18 13:33:32'),
(8, 1, 9, 'recibido', 'messages/z7dOnoZnZHlDZsVCXoPOA395IpUv9Ps13BRt5CKG.pdf', '2026-04-18 13:39:02', '2026-04-18 13:39:02'),
(9, 1, 8, 'ya veo el archivo', NULL, '2026-04-18 13:39:36', '2026-04-18 13:39:36'),
(10, 1, 9, 'vale te lomiras y me dices', NULL, '2026-04-18 13:39:54', '2026-04-18 13:39:54'),
(11, 1, 9, 'vale te lomiras y me dices', NULL, '2026-04-18 13:40:07', '2026-04-18 13:40:07'),
(12, 1, 8, 'kahfiuagf f ugafgasif gha fgafia f afa fa f ahf auhfiuahf aufhiufhaiufha faf a fuahfiuahsfi uahfu faf.fiu fhiuahf afaif s gsa g g. g a ga g aeg. ge h. rth r.  wr h wrhwh wwr hw rhw wy w hw rh wr hw h w w. wr hwr. wr hj wr hw r hw rh w r', NULL, '2026-04-18 13:40:57', '2026-04-18 13:40:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `type` varchar(20) DEFAULT 'info',
  `read_flag` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `metadata`, `type`, `read_flag`, `created_at`, `updated_at`) VALUES
(1, 1, 'Prueba', 'Esta es una notificación de prueba', NULL, 'info', 1, '2026-04-09 14:39:47', NULL),
(2, 1, 'Sorpresa creada', 'Tu sorpresa ha sido creada correctamente.', NULL, 'success', 1, '2026-04-10 13:06:33', NULL),
(3, 1, 'Nueva oferta recibida', 'Un genius ha enviado una oferta para tu sorpresa.', NULL, 'info', 1, '2026-04-10 13:09:17', NULL),
(4, 1, 'Nueva oferta recibida', 'Un genius ha enviado una oferta para tu sorpresa.', NULL, 'info', 1, '2026-04-10 13:10:05', NULL),
(5, 2, 'Oferta aceptada', 'Tu oferta ha sido aceptada. ¡Empieza la sorpresa!', NULL, 'success', 0, '2026-04-10 13:12:42', NULL),
(6, 5, 'Oferta rechazada', 'La sorpresa ha sido asignada a otro genius.', NULL, 'info', 1, '2026-04-10 13:12:42', NULL),
(7, 1, 'Sorpresa urgente', 'Faltan menos de 24 horas para la entrega.', NULL, 'warning', 0, '2026-04-10 13:47:18', NULL),
(8, 2, 'Sorpresa urgente', 'Faltan menos de 24 horas para entregar la sorpresa.', NULL, 'warning', 0, '2026-04-10 13:47:18', NULL),
(9, 9, 'Sorpresa completada', 'El creador ha marcado la sorpresa como completada.', NULL, 'success', 0, '2026-04-12 03:37:17', NULL),
(10, 8, 'Sorpresa entregada', 'El genius ha entregado tu sorpresa.', NULL, 'success', 0, '2026-04-12 03:57:52', NULL),
(11, 9, 'Sorpresa completada', 'El creador ha marcado la sorpresa como completada.', NULL, 'success', 0, '2026-04-12 03:58:10', NULL),
(12, 8, 'Sorpresa entregada', 'El genius ha entregado tu sorpresa.', NULL, 'success', 0, '2026-04-12 03:58:24', NULL),
(13, 9, 'Sorpresa completada', 'El creador ha marcado la sorpresa como completada.', NULL, 'success', 0, '2026-04-12 03:58:32', NULL),
(14, 8, 'Sorpresa creada', 'Tu sorpresa ha sido creada correctamente.', NULL, 'success', 0, '2026-04-12 04:03:40', NULL),
(15, 8, 'Sorpresa creada', 'Tu sorpresa ha sido creada correctamente.', NULL, 'success', 0, '2026-04-12 04:03:54', NULL),
(16, 8, 'Sorpresa creada', 'Tu sorpresa ha sido creada correctamente.', NULL, 'success', 0, '2026-04-12 04:03:58', NULL),
(17, 8, 'Sorpresa creada', 'Tu sorpresa ha sido creada correctamente.', NULL, 'success', 0, '2026-04-12 04:04:02', NULL),
(18, 8, 'Sorpresa creada', 'Tu sorpresa ha sido creada correctamente.', NULL, 'success', 0, '2026-04-12 04:04:06', NULL),
(19, 8, 'Sorpresa creada', 'Tu sorpresa ha sido creada correctamente.', NULL, 'success', 0, '2026-04-12 04:04:11', NULL),
(20, 8, 'Sorpresa entregada', 'El genius ha entregado tu sorpresa.', NULL, 'success', 0, '2026-04-12 04:07:44', NULL),
(21, 8, 'Sorpresa entregada', 'El genius ha entregado tu sorpresa.', NULL, 'success', 0, '2026-04-12 04:07:49', NULL),
(22, 8, 'Sorpresa entregada', 'El genius ha entregado tu sorpresa.', NULL, 'success', 0, '2026-04-12 04:07:54', NULL),
(23, 8, 'Sorpresa entregada', 'El genius ha entregado tu sorpresa.', NULL, 'success', 0, '2026-04-12 04:07:58', NULL),
(24, 8, 'Sorpresa entregada', 'El genius ha entregado tu sorpresa.', NULL, 'success', 0, '2026-04-12 04:08:04', NULL),
(25, 8, 'Sorpresa entregada', 'El genius ha entregado tu sorpresa.', NULL, 'success', 0, '2026-04-12 04:08:09', NULL),
(26, 9, 'Sorpresa completada', 'El creador ha marcado la sorpresa como completada.', NULL, 'success', 0, '2026-04-12 04:08:16', NULL),
(27, 9, 'Sorpresa completada', 'El creador ha marcado la sorpresa como completada.', NULL, 'success', 0, '2026-04-12 04:08:21', NULL),
(28, 9, 'Sorpresa completada', 'El creador ha marcado la sorpresa como completada.', NULL, 'success', 0, '2026-04-12 04:08:25', NULL),
(29, 9, 'Sorpresa completada', 'El creador ha marcado la sorpresa como completada.', NULL, 'success', 0, '2026-04-12 04:08:28', NULL),
(30, 9, 'Sorpresa completada', 'El creador ha marcado la sorpresa como completada.', NULL, 'success', 0, '2026-04-12 04:08:32', NULL),
(31, 9, 'Sorpresa completada', 'El creador ha marcado la sorpresa como completada.', NULL, 'success', 0, '2026-04-12 04:08:37', NULL),
(32, 8, 'Sorpresa creada', 'Tu sorpresa ha sido creada correctamente.', NULL, 'success', 0, '2026-04-12 16:30:08', NULL),
(33, 8, 'Sorpresa creada', 'Tu sorpresa ha sido creada correctamente.', NULL, 'success', 0, '2026-04-12 16:31:46', NULL),
(34, 9, 'Sorpresa creada', 'Tu sorpresa ha sido creada correctamente.', NULL, 'success', 0, '2026-04-12 16:32:23', NULL),
(35, 1, 'Sorpresa creada', 'Tu sorpresa ha sido creada correctamente.', '{\"surprise_id\":23}', 'success', 0, '2026-04-15 12:56:12', '2026-04-15 12:56:12'),
(36, 8, 'Sorpresa creada', 'Tu sorpresa ha sido creada correctamente.', '{\"surprise_id\":24}', 'success', 0, '2026-04-15 12:56:56', '2026-04-15 12:56:56'),
(37, 8, 'Nueva oferta recibida', 'Un genius ha enviado una oferta para tu sorpresa.', '{\"surprise_id\":24,\"offer_id\":3}', 'info', 0, '2026-04-15 12:59:42', '2026-04-15 12:59:42'),
(38, 9, 'Oferta aceptada', 'Tu oferta ha sido aceptada. ¡Empieza la sorpresa!', '{\"surprise_id\":24,\"offer_id\":3}', 'success', 0, '2026-04-15 13:02:56', '2026-04-15 13:02:56'),
(39, 9, 'Deadline actualizado', 'El creador ha actualizado la fecha límite de la sorpresa.', '{\"surprise_id\":24}', 'info', 0, '2026-04-15 13:03:46', '2026-04-15 13:03:46'),
(40, 8, 'Nuevo archivo disponible', 'El genius ha subido un archivo a tu sorpresa.', NULL, 'info', 0, '2026-04-15 13:05:57', '2026-04-15 13:05:57'),
(41, 9, 'Nuevo archivo del creador', 'El creador ha subido un archivo a la sorpresa.', NULL, 'info', 0, '2026-04-15 13:05:57', '2026-04-15 13:05:57'),
(42, 8, 'Sorpresa entregada', 'El genius ha entregado tu sorpresa.', '{\"surprise_id\":24}', 'success', 0, '2026-04-15 13:08:56', '2026-04-15 13:08:56'),
(43, 8, 'Sorpresa completada', 'Has marcado la sorpresa como completada.', '{\"surprise_id\":24}', 'success', 0, '2026-04-15 13:09:27', '2026-04-15 13:09:27'),
(44, 9, 'Sorpresa completada', 'El creador ha completado la sorpresa.', '{\"surprise_id\":24}', 'success', 0, '2026-04-15 13:09:27', '2026-04-15 13:09:27'),
(45, 9, 'Nueva reseña recibida', 'Has recibido una nueva reseña.', '{\"surprise_id\":\"24\",\"review_id\":15,\"rating_genius\":5,\"rating_surprise\":5}', 'success', 0, '2026-04-15 13:12:34', '2026-04-15 13:12:34'),
(46, 8, 'Nuevo archivo disponible', 'El genius ha subido un archivo a tu sorpresa.', NULL, 'info', 0, '2026-04-15 13:24:15', '2026-04-15 13:24:15'),
(47, 9, 'Nuevo archivo del creador', 'El creador ha subido un archivo a la sorpresa.', NULL, 'info', 0, '2026-04-15 13:24:15', '2026-04-15 13:24:15'),
(48, 8, 'Nuevo archivo disponible', 'El genius ha subido un archivo a tu sorpresa.', NULL, 'info', 0, '2026-04-15 13:28:59', '2026-04-15 13:28:59'),
(49, 9, 'Nuevo archivo del creador', 'El creador ha subido un archivo a la sorpresa.', NULL, 'info', 0, '2026-04-15 13:28:59', '2026-04-15 13:28:59'),
(50, 8, 'Nuevo archivo disponible', 'El genius ha subido un archivo a tu sorpresa.', NULL, 'info', 0, '2026-04-15 13:36:30', '2026-04-15 13:36:30'),
(51, 9, 'Nuevo archivo del creador', 'El creador ha subido un archivo a la sorpresa.', NULL, 'info', 0, '2026-04-15 13:36:30', '2026-04-15 13:36:30'),
(52, 8, 'Nuevo archivo disponible', 'El genius ha subido un archivo a tu sorpresa.', '{\"surprise_id\":24}', 'info', 0, '2026-04-15 13:37:54', '2026-04-15 13:37:54'),
(53, 8, 'Nueva oferta recibida', 'Un genius ha enviado una oferta para tu sorpresa.', '{\"surprise_id\":20,\"offer_id\":4}', 'info', 0, '2026-04-18 11:59:40', '2026-04-18 11:59:40'),
(54, 8, 'Nueva contraoferta del genius', 'El genius ha enviado una contraoferta.', '{\"surprise_id\":20,\"offer_id\":4,\"bid_id\":1}', 'info', 0, '2026-04-18 12:02:38', '2026-04-18 12:02:38'),
(55, 9, 'Oferta aceptada', 'Tu oferta ha sido aceptada. ¡Empieza la sorpresa!', '{\"surprise_id\":20,\"offer_id\":4}', 'success', 0, '2026-04-18 12:08:35', '2026-04-18 12:08:35'),
(56, 3, 'Sorpresa creada', 'Tu sorpresa ha sido creada correctamente.', '{\"surprise_id\":25}', 'success', 0, '2026-04-18 12:10:59', '2026-04-18 12:10:59'),
(57, 3, 'Nueva oferta recibida', 'Un genius ha enviado una oferta para tu sorpresa.', '{\"surprise_id\":25,\"offer_id\":5}', 'info', 0, '2026-04-18 12:13:04', '2026-04-18 12:13:04'),
(58, 9, 'Nueva contraoferta del creador', 'El creador ha enviado una contraoferta.', '{\"surprise_id\":25,\"offer_id\":5,\"bid_id\":2}', 'info', 0, '2026-04-18 12:15:05', '2026-04-18 12:15:05'),
(59, 8, 'Nueva contraoferta del genius', 'El genius ha enviado una contraoferta.', '{\"surprise_id\":25,\"offer_id\":5,\"bid_id\":3}', 'info', 0, '2026-04-18 12:16:51', '2026-04-18 12:16:51'),
(60, 9, 'Oferta aceptada', 'Tu oferta ha sido aceptada. ¡Empieza la sorpresa!', '{\"surprise_id\":25,\"offer_id\":5}', 'success', 0, '2026-04-18 12:19:48', '2026-04-18 12:19:48'),
(61, 9, 'Nuevo mensaje del creador', 'Has recibido un nuevo mensaje en la sorpresa.', '{\"surprise_id\":20,\"conversation_id\":1,\"message_id\":1}', 'info', 0, '2026-04-18 13:09:09', '2026-04-18 13:09:09'),
(62, 8, 'Nuevo mensaje del genius', 'Has recibido un nuevo mensaje en la sorpresa.', '{\"surprise_id\":20,\"conversation_id\":1,\"message_id\":2}', 'info', 0, '2026-04-18 13:11:08', '2026-04-18 13:11:08'),
(63, 9, 'Nuevo mensaje del creador', 'Has recibido un nuevo mensaje en la sorpresa.', '{\"surprise_id\":20,\"conversation_id\":1,\"message_id\":3}', 'info', 0, '2026-04-18 13:29:56', '2026-04-18 13:29:56'),
(64, 9, 'Nuevo mensaje del creador', 'Has recibido un nuevo mensaje en la sorpresa.', '{\"surprise_id\":20,\"conversation_id\":1,\"message_id\":4}', 'info', 0, '2026-04-18 13:30:22', '2026-04-18 13:30:22'),
(65, 9, 'Nuevo mensaje del creador', 'Has recibido un nuevo mensaje en la sorpresa.', '{\"surprise_id\":20,\"conversation_id\":1,\"message_id\":5}', 'info', 0, '2026-04-18 13:30:48', '2026-04-18 13:30:48'),
(66, 9, 'Nuevo mensaje del creador', 'Has recibido un nuevo mensaje en la sorpresa.', '{\"surprise_id\":20,\"conversation_id\":1,\"message_id\":6}', 'info', 0, '2026-04-18 13:32:20', '2026-04-18 13:32:20'),
(67, 9, 'Nuevo mensaje del creador', 'Has recibido un nuevo mensaje en la sorpresa.', '{\"surprise_id\":20,\"conversation_id\":1,\"message_id\":7}', 'info', 0, '2026-04-18 13:33:32', '2026-04-18 13:33:32'),
(68, 8, 'Nuevo mensaje del genius', 'Has recibido un nuevo mensaje en la sorpresa.', '{\"surprise_id\":20,\"conversation_id\":1,\"message_id\":8}', 'info', 0, '2026-04-18 13:39:02', '2026-04-18 13:39:02'),
(69, 9, 'Nuevo mensaje del creador', 'Has recibido un nuevo mensaje en la sorpresa.', '{\"surprise_id\":20,\"conversation_id\":1,\"message_id\":9}', 'info', 0, '2026-04-18 13:39:36', '2026-04-18 13:39:36'),
(70, 8, 'Nuevo mensaje del genius', 'Has recibido un nuevo mensaje en la sorpresa.', '{\"surprise_id\":20,\"conversation_id\":1,\"message_id\":10}', 'info', 0, '2026-04-18 13:39:54', '2026-04-18 13:39:54'),
(71, 8, 'Nuevo mensaje del genius', 'Has recibido un nuevo mensaje en la sorpresa.', '{\"surprise_id\":20,\"conversation_id\":1,\"message_id\":11}', 'info', 0, '2026-04-18 13:40:07', '2026-04-18 13:40:07'),
(72, 9, 'Nuevo mensaje del creador', 'Has recibido un nuevo mensaje en la sorpresa.', '{\"surprise_id\":20,\"conversation_id\":1,\"message_id\":12}', 'info', 0, '2026-04-18 13:40:57', '2026-04-18 13:40:57'),
(73, 8, 'Disputa resuelta', 'La disputa ha sido resuelta por el equipo de administración.', '{\"surprise_id\":19,\"dispute_id\":1,\"winner\":\"creator\"}', 'info', 0, '2026-04-26 07:36:50', '2026-04-26 07:36:50'),
(74, 9, 'Disputa resuelta', 'La disputa ha sido resuelta por el equipo de administración.', '{\"surprise_id\":19,\"dispute_id\":1,\"winner\":\"creator\"}', 'info', 0, '2026-04-26 07:36:50', '2026-04-26 07:36:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `offers`
--

CREATE TABLE `offers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `surprise_id` bigint(20) UNSIGNED NOT NULL,
  `genius_id` bigint(20) UNSIGNED NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `eta_hours` int(11) DEFAULT NULL,
  `status` enum('pending','negotiating','accepted','rejected') NOT NULL DEFAULT 'pending',
  `creator_bid_count` int(11) NOT NULL DEFAULT 0,
  `genius_bid_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `offers`
--

INSERT INTO `offers` (`id`, `surprise_id`, `genius_id`, `price`, `original_price`, `message`, `eta_hours`, `status`, `creator_bid_count`, `genius_bid_count`, `created_at`, `updated_at`) VALUES
(1, 7, 5, 12.50, NULL, 'Puedo hacerlo muy bonito', 2, 'rejected', 0, 0, '2026-04-10 11:09:17', '2026-04-10 11:12:42'),
(2, 7, 2, 20.00, NULL, 'Soy experto en cartas personalizadas', 1, 'accepted', 0, 0, '2026-04-10 11:10:05', '2026-04-10 11:12:42'),
(3, 24, 9, 20.00, NULL, NULL, NULL, 'accepted', 0, 0, '2026-04-15 12:59:42', '2026-04-15 13:02:56'),
(4, 20, 9, 70.00, NULL, 'Si lo dejas en 70 cerramos', 72, 'accepted', 0, 1, '2026-04-18 11:59:40', '2026-04-18 12:08:35'),
(5, 25, 9, 110.00, NULL, 'Puedo bajarlo a 110', 48, 'accepted', 1, 1, '2026-04-18 12:13:04', '2026-04-18 12:19:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `offer_bids`
--

CREATE TABLE `offer_bids` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `offer_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` enum('creator','genius') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `eta_hours` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `offer_bids`
--

INSERT INTO `offer_bids` (`id`, `offer_id`, `user_id`, `role`, `price`, `eta_hours`, `message`, `created_at`, `updated_at`) VALUES
(1, 4, 9, 'genius', 70.00, 72, 'Si lo dejas en 70 cerramos', '2026-04-18 12:02:38', '2026-04-18 12:02:38'),
(2, 5, 8, 'creator', 100.00, 48, 'Si lo dejas en 100 lo cerramos', '2026-04-18 12:15:05', '2026-04-18 12:15:05'),
(3, 5, 9, 'genius', 110.00, 48, 'Puedo bajarlo a 110', '2026-04-18 12:16:51', '2026-04-18 12:16:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('abicfonseca@gmail.com', '$2y$12$CamBGxex4Lsg8WUiEpa7KOyATAcbCYT.NAkuyWtsFv9f2rDas3OlW', '2026-04-19 14:41:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `penalties`
--

CREATE TABLE `penalties` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('cancellation_doubtful','cancellation_invalid','dispute_loss','fraud','manual') NOT NULL,
  `reason_key` varchar(100) DEFAULT NULL,
  `reason_text` text DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` text NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(30, 'App\\Models\\User', 9, 'auth_token', '7363e267a465f9e01cf6cb310542191a16b755deee0cf9b06a11d1aee1935e7d', '[\"*\"]', NULL, NULL, '2026-04-18 12:58:25', '2026-04-18 12:58:25'),
(31, 'App\\Models\\User', 9, 'auth_token', '74f3c633e5c350a77c15057667af02447fdae5fde30d0f78221bc4edc478df5b', '[\"*\"]', '2026-04-20 09:55:44', NULL, '2026-04-18 12:58:28', '2026-04-20 09:55:44'),
(34, 'App\\Models\\User', 8, 'auth_token', 'a25d4800f69f094e4410b55c013cfa5c36548708c218f5c8d48403c148aa81d2', '[\"*\"]', '2026-04-25 07:28:29', NULL, '2026-04-25 07:23:48', '2026-04-25 07:28:29'),
(35, 'App\\Models\\User', 8, 'auth_token', '83b9e4683c9485dbeca858babe6ed076f8cad65a0edfaa6d3d7c0d886eb1a7f0', '[\"*\"]', '2026-04-26 07:36:50', NULL, '2026-04-26 07:35:49', '2026-04-26 07:36:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `surprise_id` bigint(20) UNSIGNED NOT NULL,
  `reviewer_id` bigint(20) UNSIGNED NOT NULL,
  `reviewed_user_id` bigint(20) UNSIGNED NOT NULL,
  `rating_genius` int(11) DEFAULT NULL CHECK (`rating_genius` between 1 and 5),
  `rating_surprise` int(11) DEFAULT NULL CHECK (`rating_surprise` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reviews`
--

INSERT INTO `reviews` (`id`, `surprise_id`, `reviewer_id`, `reviewed_user_id`, `rating_genius`, `rating_surprise`, `comment`, `created_at`, `updated_at`) VALUES
(4, 12, 8, 9, 5, 5, 'Perfecto, todo genial', '2026-04-12 02:26:52', '2026-04-12 02:26:52'),
(5, 13, 8, 9, 5, 5, 'todo genial', '2026-04-12 02:27:05', '2026-04-12 02:27:05'),
(6, 14, 8, 9, 5, 5, 'todo genial', '2026-04-12 02:27:13', '2026-04-12 02:27:13'),
(7, 15, 8, 9, 5, 5, 'todo genial', '2026-04-12 02:27:18', '2026-04-12 02:27:18'),
(8, 16, 8, 9, 5, 5, '100% recomendable', '2026-04-12 02:27:30', '2026-04-12 02:27:30'),
(9, 17, 8, 9, 5, 5, '100% recomendable', '2026-04-12 02:27:34', '2026-04-12 02:27:34'),
(10, 18, 8, 9, 5, 5, '100% recomendable', '2026-04-12 02:27:38', '2026-04-12 02:27:38'),
(13, 19, 8, 9, 5, 5, 'todo ok', '2026-04-12 03:34:32', '2026-04-12 03:34:32'),
(14, 8, 8, 9, 5, 5, 'todo ok', '2026-04-13 09:47:31', '2026-04-13 09:47:31'),
(15, 24, 8, 9, 5, 5, 'Excelente', '2026-04-15 13:12:34', '2026-04-15 13:12:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('abc', 1, '127.0.0.1', 'Chrome', 'test', 123456),
('isG2epHXS04q8Bu0zMiLnM7DyO8avR96Mxt8ZmXY', NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSHVKZm15b2JjeHltVXdGdmFxanozT0lMUDIycmZGWDRob0NWc0c0ciI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC90ZXN0IjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1762511662);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `skills`
--

CREATE TABLE `skills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `skills`
--

INSERT INTO `skills` (`id`, `name`, `category`, `description`) VALUES
(157, 'Arte y manualidades', 'Creatividad y arte', 'creación artística personalizada'),
(158, 'Fotografía y vídeo', 'Creatividad y arte', 'fotos, grabación, edición'),
(159, 'Música y audio', 'Creatividad y arte', 'canciones, grabaciones, dedicatorias'),
(160, 'Artes escénicas y performance', 'Creatividad y arte', 'actuación, presencia, espectáculo'),
(161, 'Diseño y creación digital', 'Creatividad y arte', 'diseño, edición, contenido digital'),
(162, 'Contenido para redes', 'Creatividad y arte', 'reels, clips, contenido social'),
(163, 'Gastronomía creativa', 'Cocina y gastronomía', 'comida decorada, detalles'),
(164, 'Cocina y preparación', 'Cocina y gastronomía', 'comida casera, preparación'),
(165, 'Belleza y cuidado personal', 'Personas, emociones y bienestar', 'estética, imagen, bienestar'),
(166, 'Acompañamiento personal', 'Personas, emociones y bienestar', 'apoyo, presencia, ayuda'),
(167, 'Bienestar emocional', 'Personas, emociones y bienestar', 'calma, relajación, respiración'),
(168, 'Emociones y mensajes', 'Personas, emociones y bienestar', 'textos, sentimientos, conexión'),
(169, 'Actividad física y deporte', 'Actividades y experiencias', 'movimiento, ejercicio, bienestar'),
(170, 'Experiencias sociales', 'Actividades y experiencias', 'interacción, grupo, participación'),
(171, 'Naturaleza y exterior', 'Actividades y experiencias', 'aire libre, entorno, paseo'),
(172, 'Experiencias en ruta', 'Actividades y experiencias', 'paseos, trayectos, conducción'),
(173, 'Juegos y aventuras', 'Actividades y experiencias', 'dinámicas, pistas, diversión'),
(174, 'Niños y actividades', 'Actividades y experiencias', 'juegos, creatividad, diversión'),
(175, 'Mascotas y entrenamiento', 'Actividades y experiencias', 'cuidados, trucos, aprendizaje'),
(176, 'Manitas y hogar', 'Servicios y soporte', 'arreglos, montajes, tareas'),
(177, 'Logística y recados', 'Servicios y soporte', 'entregas, gestiones, recados'),
(178, 'Planificación y organización', 'Servicios y soporte', 'ideas, coordinación, diseño'),
(179, 'Decoración y celebraciones', 'Servicios y soporte', 'ambientación, fiestas, eventos'),
(180, 'Selección de regalos', 'Servicios y soporte', 'elección experta de detalles');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `surpriser_skills`
--

CREATE TABLE `surpriser_skills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `surpriser_user_skills`
--

CREATE TABLE `surpriser_user_skills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `skill_id` bigint(20) UNSIGNED NOT NULL,
  `level` varchar(20) DEFAULT 'intermediate'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `surprises`
--

CREATE TABLE `surprises` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `creator_id` bigint(20) UNSIGNED NOT NULL,
  `genius_id` bigint(20) UNSIGNED DEFAULT NULL,
  `skill_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `size` enum('SMALL','MEDIUM','LARGE','PREMIUM') NOT NULL DEFAULT 'SMALL',
  `is_urgent` tinyint(1) NOT NULL DEFAULT 0,
  `rating_for_genius` decimal(3,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'open',
  `price` decimal(8,2) DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `target_name` varchar(100) DEFAULT NULL,
  `target_city` varchar(100) DEFAULT NULL,
  `target_country` varchar(100) DEFAULT NULL,
  `target_lat` decimal(10,7) DEFAULT NULL,
  `target_lng` decimal(10,7) DEFAULT NULL,
  `price_creator` decimal(10,2) DEFAULT NULL,
  `price_genius` decimal(10,2) DEFAULT NULL,
  `final_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deadline_warning_sent` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `surprises`
--

INSERT INTO `surprises` (`id`, `creator_id`, `genius_id`, `skill_id`, `title`, `description`, `size`, `is_urgent`, `rating_for_genius`, `status`, `price`, `deadline`, `target_name`, `target_city`, `target_country`, `target_lat`, `target_lng`, `price_creator`, `price_genius`, `final_price`, `created_at`, `updated_at`, `deadline_warning_sent`, `completed_at`) VALUES
(7, 8, 9, NULL, 'Quiero una carta bonita para mi pareja', 'Algo emotivo y personalizado', 'SMALL', 0, NULL, 'in_progress', NULL, '2026-04-12 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-10 11:06:33', '2026-04-10 11:12:42', 0, '2026-04-12 03:37:09'),
(8, 8, 9, NULL, 'Sorpresa 1', 'Entrega perfecta', 'SMALL', 0, NULL, 'completed', 50.00, '2026-04-14 20:13:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11 18:13:20', '2026-04-11 18:13:20', 0, '2026-04-12 03:37:09'),
(9, 8, 9, NULL, 'Sorpresa 2', 'Muy buena', 'SMALL', 0, NULL, 'completed', 60.00, '2026-04-13 20:13:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11 18:13:31', '2026-04-11 18:13:31', 0, '2026-04-12 03:37:09'),
(10, 8, 9, NULL, 'Sorpresa 3', 'Normalita', 'SMALL', 0, NULL, 'completed', 40.00, '2026-04-12 20:13:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11 18:13:49', '2026-04-11 18:13:49', 0, '2026-04-12 03:37:09'),
(11, 8, 9, NULL, 'Sorpresa 4', 'Pendiente de confirmar', 'SMALL', 0, NULL, 'completed', 70.00, '2026-04-12 20:14:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11 18:14:00', '2026-04-12 01:37:17', 0, '2026-04-12 01:37:17'),
(12, 8, 9, NULL, 'Sorpresa 5', 'En curso', 'SMALL', 0, NULL, 'completed', 80.00, '2026-04-13 20:14:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11 18:14:12', '2026-04-12 01:58:10', 0, '2026-04-12 01:58:10'),
(13, 8, 9, NULL, 'Sorpresa Tarde', 'Entrega tardía', 'SMALL', 0, NULL, 'completed', 90.00, '2026-04-10 20:14:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11 18:14:25', '2026-04-12 01:58:32', 0, '2026-04-12 01:58:32'),
(14, 8, 9, NULL, 'Sorpresa SMALL 1', 'Prueba para subir de nivel', 'SMALL', 0, NULL, 'completed', 50.00, '2026-04-20 20:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-12 02:03:40', '2026-04-12 02:08:16', 0, '2026-04-12 02:08:16'),
(15, 8, 9, NULL, 'Sorpresa SMALL 2', 'Prueba para subir de nivel', 'SMALL', 0, NULL, 'completed', 50.00, '2026-04-20 20:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-12 02:03:54', '2026-04-12 02:08:21', 0, '2026-04-12 02:08:21'),
(16, 8, 9, NULL, 'Sorpresa SMALL 3', 'Prueba para subir de nivel', 'SMALL', 0, NULL, 'completed', 50.00, '2026-04-20 20:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-12 02:03:58', '2026-04-12 02:08:25', 0, '2026-04-12 02:08:25'),
(17, 8, 9, NULL, 'Sorpresa SMALL 4', 'Prueba para subir de nivel', 'SMALL', 0, NULL, 'completed', 50.00, '2026-04-20 20:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-12 02:04:02', '2026-04-12 02:08:28', 0, '2026-04-12 02:08:28'),
(18, 8, 9, NULL, 'Sorpresa SMALL 5', 'Prueba para subir de nivel', 'SMALL', 0, NULL, 'completed', 50.00, '2026-04-20 20:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-12 02:04:06', '2026-04-12 02:08:32', 0, '2026-04-12 02:08:32'),
(19, 8, 9, NULL, 'Sorpresa SMALL 6', 'Prueba para subir de nivel', 'SMALL', 0, NULL, 'completed', 50.00, '2026-04-20 20:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-12 02:04:11', '2026-04-12 02:08:37', 0, '2026-04-12 02:08:37'),
(20, 8, 9, NULL, 'Logo minimalista actualizadosssss', 'Quiero un logo simple, limpio, en blanco y negro, con tipografía sans serif.', 'MEDIUM', 1, NULL, 'in_progress', 45.00, '2026-04-25 18:00:00', 'María', 'Barcelona', 'España', 41.3874000, 2.1686000, NULL, 70.00, 70.00, '2026-04-12 14:30:08', '2026-04-18 12:08:35', 0, NULL),
(21, 8, NULL, NULL, 'Logo minimalista para mi marca', 'Quiero un logo sencillo en blanco y negro.', 'SMALL', 0, NULL, 'open', 30.00, '2026-04-20 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-12 14:31:46', '2026-04-12 14:31:46', 0, '2026-04-12 16:31:46'),
(22, 9, NULL, NULL, 'Logo minimalista para mi marca', 'Quiero un logo sencillo en blanco y negro.', 'SMALL', 0, NULL, 'open', 30.00, '2026-04-20 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-12 14:32:23', '2026-04-12 14:32:23', 0, '2026-04-12 16:32:23'),
(23, 1, NULL, NULL, 'Prueba de notificación', 'Test', 'SMALL', 0, NULL, 'open', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15 12:56:12', '2026-04-15 12:56:12', 0, NULL),
(24, 8, 9, NULL, 'Prueba de notificación', 'Test', 'SMALL', 0, NULL, 'completed', NULL, '2026-05-01 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15 12:56:56', '2026-04-15 13:09:27', 0, NULL),
(25, 8, 9, NULL, 'Necesito una web negra', 'Algo limpio, moderno y elegante', 'MEDIUM', 0, NULL, 'in_progress', NULL, '2026-04-20 18:00:00', NULL, NULL, NULL, NULL, NULL, NULL, 110.00, 110.00, '2026-04-18 12:10:59', '2026-04-18 12:19:48', 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `surprise_files`
--

CREATE TABLE `surprise_files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `surprise_id` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `mime` varchar(100) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `file_url` text NOT NULL,
  `file_type` varchar(20) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `surprise_files`
--

INSERT INTO `surprise_files` (`id`, `surprise_id`, `filename`, `path`, `mime`, `size`, `file_url`, `file_type`, `uploaded_at`) VALUES
(9, 24, 'descargasa.jpg', 'surprises/24/ZJDk0PZVan3Qu4KqRlLyv9dj0bYwUZ4yB81Cp5SF.jpg', 'image/jpeg', 8778, 'http://localhost:8000/storage/surprises/24/ZJDk0PZVan3Qu4KqRlLyv9dj0bYwUZ4yB81Cp5SF.jpg', 'image', '2026-04-15 15:05:57'),
(10, 24, 'descargasa.jpg', 'surprises/24/UDdIdC4emTqbzYfSt5NRkBkn6MPzvNCoF55xB2gk.jpg', 'image/jpeg', 8778, 'http://localhost:8000/storage/surprises/24/UDdIdC4emTqbzYfSt5NRkBkn6MPzvNCoF55xB2gk.jpg', 'image', '2026-04-15 15:24:15'),
(11, 24, 'descargasa.jpg', 'surprises/24/mGAfNSU5cKR39nW52lK28COeoYVzl9RD3yWcoCoH.jpg', 'image/jpeg', 8778, 'http://localhost:8000/storage/surprises/24/mGAfNSU5cKR39nW52lK28COeoYVzl9RD3yWcoCoH.jpg', 'image', '2026-04-15 15:28:59'),
(12, 24, 'descargasa.jpg', 'surprises/24/a3vUg0Z8NVazesYqCW49um8vxUPDyZd4CH7kQaYK.jpg', 'image/jpeg', 8778, 'http://localhost:8000/storage/surprises/24/a3vUg0Z8NVazesYqCW49um8vxUPDyZd4CH7kQaYK.jpg', 'image', '2026-04-15 15:35:13'),
(13, 24, 'descargasa.jpg', 'surprises/24/3rcZBjPwPvwmbGpODJyGWioHRxL9SVjYwMKgD6kp.jpg', 'image/jpeg', 8778, 'http://localhost:8000/storage/surprises/24/3rcZBjPwPvwmbGpODJyGWioHRxL9SVjYwMKgD6kp.jpg', 'image', '2026-04-15 15:35:42'),
(14, 24, 'descargasa.jpg', 'surprises/24/MJZTTrkBGy0IKnX5B34eXyAtSZDtzDsWEZPj7UKr.jpg', 'image/jpeg', 8778, 'http://localhost:8000/storage/surprises/24/MJZTTrkBGy0IKnX5B34eXyAtSZDtzDsWEZPj7UKr.jpg', 'image', '2026-04-15 15:36:30'),
(15, 24, 'descargasa.jpg', 'surprises/24/y11e5fvBLAL4JuKWXSgeWw1l6dNzKORTLCxugbIr.jpg', 'image/jpeg', 8778, 'http://localhost:8000/storage/surprises/24/y11e5fvBLAL4JuKWXSgeWw1l6dNzKORTLCxugbIr.jpg', 'image', '2026-04-15 15:37:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_creator` tinyint(1) DEFAULT 1,
  `is_genius` tinyint(1) DEFAULT 0,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `banned` tinyint(1) NOT NULL DEFAULT 0,
  `bio` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `location_city` varchar(100) DEFAULT NULL,
  `location_country` varchar(100) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `genius_level` enum('SPARK','FLAME','GENIE','SULTAN') NOT NULL DEFAULT 'SPARK',
  `genius_points` int(11) NOT NULL DEFAULT 0,
  `genius_total_surprises` int(11) NOT NULL DEFAULT 0,
  `genius_avg_rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `genius_major_disputes` int(11) NOT NULL DEFAULT 0,
  `genius_recent_penalties` int(11) NOT NULL DEFAULT 0,
  `genius_last_20_penalties` int(11) NOT NULL DEFAULT 0,
  `genius_first_activity_at` timestamp NULL DEFAULT NULL,
  `identity_verified` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `username`, `avatar`, `is_creator`, `is_genius`, `is_admin`, `banned`, `bio`, `phone`, `location_city`, `location_country`, `lat`, `lng`, `created_at`, `updated_at`, `genius_level`, `genius_points`, `genius_total_surprises`, `genius_avg_rating`, `genius_major_disputes`, `genius_recent_penalties`, `genius_last_20_penalties`, `genius_first_activity_at`, `identity_verified`) VALUES
(1, 'Juanass', 'juanass@example.com', NULL, '$2y$12$yhxXOVIZ9L5r1dL29CoQ8Ox623UXOJw0bfmyXJYEQ1vl8QE64w6MK', NULL, NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-07 09:23:28', '2025-11-07 09:23:28', 'SPARK', 0, 0, 0.00, 0, 0, 0, NULL, 0),
(2, 'Juan', 'juan@example.com', NULL, '$2y$12$r0dSIkAxRWSt909JSgXPs.nGNBiEgle7D9LLMPexBOmkgaW8oz8Ua', NULL, NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-07 09:23:53', '2025-11-07 09:23:53', 'SPARK', 0, 0, 0.00, 0, 0, 0, NULL, 0),
(3, 'Juani', 'juani@example.com', NULL, '$2y$12$3R8XRCnG/4m9VfW6tOdw2.sGakQGwrwD85BQm5KEQdMLCm6WzsEui', NULL, NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-07 09:37:00', '2025-11-07 09:37:00', 'SPARK', 0, 0, 0.00, 0, 0, 0, NULL, 0),
(4, 'Abby', 'abby@example.com', NULL, '$2y$12$ZNgjNbnbVbBIFNPcmMMz/OqTLOE2.JOScuvZVhOIkP229zcJ.s0Ei', NULL, NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-08 13:05:35', '2025-11-08 13:05:35', 'SPARK', 0, 0, 0.00, 0, 0, 0, NULL, 0),
(5, 'Test', 'test@test.com', NULL, '1234', NULL, NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SPARK', 0, 0, 0.00, 0, 0, 0, NULL, 0),
(6, 'Test Creator', 'creator@example.com', NULL, 'creator1', 'creator1', NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11 18:07:21', '2026-04-11 18:07:21', 'SPARK', 0, 0, 0.00, 0, 0, 0, NULL, 0),
(7, 'Test Genius', 'genius@example.com', NULL, 'genius1', 'genius1', NULL, 0, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11 18:07:21', '2026-04-11 18:07:21', 'SPARK', 0, 0, 0.00, 0, 0, 0, NULL, 1),
(8, 'Creador de test', 'creator1@example.com', '2026-04-25 07:23:26', '$2y$12$Ccn9YlR1cM/kVTcTjQz9Le5h31Ta5dtRO7Jc/EzhoaUJGVR0gPb.i', NULL, NULL, 1, 1, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11 16:25:23', '2026-04-25 07:23:26', 'SPARK', 0, 0, 0.00, 0, 0, 0, NULL, 0),
(9, 'Genius de test', 'genius1@example.com', NULL, '$2y$12$g0Cdgg9zI7jC8N8kRq4AweXumKqyqd2TAURIE8qKS/ho0akoHh6My', NULL, NULL, 1, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11 16:27:22', '2026-04-15 13:12:34', 'FLAME', 61, 13, 5.00, 0, 0, 0, '2026-04-12 01:37:17', 0),
(10, 'Abby Test', 'abbycfonsk@gmail.com', NULL, '$2y$12$0u/aZJEgPSm9Sn5qg4sAxuKkl1WOHWtse1JW99D9NVpfy6ZABK6Sm', NULL, NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-18 14:01:11', '2026-04-18 14:01:11', 'SPARK', 0, 0, 0.00, 0, 0, 0, NULL, 0),
(11, 'Abby Test2', 'abbytest@example.com', '2026-04-18 14:12:16', '$2y$12$LQs0lZKZcsoCI6xILw7JUOLE4.QFAP5.vfDXa0GeqPHaOmUcU2slq', NULL, NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-18 14:09:55', '2026-04-18 14:29:29', 'SPARK', 0, 0, 0.00, 0, 0, 0, NULL, 0),
(12, 'Abby', 'abicfonseca@gmail.com', NULL, '$2y$10$abcdefghijklmnopqrstuv', NULL, NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-19 16:40:56', '2026-04-19 16:40:56', 'SPARK', 0, 0, 0.00, 0, 0, 0, NULL, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_proposed_skills`
--

CREATE TABLE `user_proposed_skills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `skill_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_proposed_skills`
--

INSERT INTO `user_proposed_skills` (`id`, `user_id`, `skill_id`, `created_at`, `updated_at`) VALUES
(1, 8, 1, NULL, NULL),
(2, 8, 20, NULL, NULL),
(3, 8, 56, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_skills`
--

CREATE TABLE `user_skills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `skill_id` int(11) NOT NULL,
  `level` int(11) NOT NULL DEFAULT 1,
  `xp` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_skills`
--

INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `xp`) VALUES
(2, 1, 2, 1, 0),
(3, 2, 1, 1, 0),
(4, 9, 4, 1, 10),
(5, 9, 20, 1, 20),
(6, 9, 1, 1, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_suspensions`
--

CREATE TABLE `user_suspensions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `suspended_until` datetime NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_model` (`model`,`model_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_created_at` (`created_at`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cancellations`
--
ALTER TABLE `cancellations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cancellations_surprise` (`surprise_id`),
  ADD KEY `fk_cancellations_genius` (`genius_id`),
  ADD KEY `fk_cancellations_creator` (`creator_id`);

--
-- Indices de la tabla `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversations_surprise` (`surprise_id`),
  ADD KEY `idx_conversations_creator` (`creator_id`),
  ADD KEY `idx_conversations_genius` (`genius_id`);

--
-- Indices de la tabla `disputes`
--
ALTER TABLE `disputes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `surprise_id` (`surprise_id`),
  ADD KEY `creator_id` (`creator_id`),
  ADD KEY `genius_id` (`genius_id`),
  ADD KEY `fk_disputes_opened_by` (`opened_by`);

--
-- Indices de la tabla `dispute_penalties`
--
ALTER TABLE `dispute_penalties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dp_dispute` (`dispute_id`),
  ADD KEY `fk_dp_genius` (`genius_id`);

--
-- Indices de la tabla `genius_point_events`
--
ALTER TABLE `genius_point_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gpe_genius_fk` (`genius_id`),
  ADD KEY `gpe_surprise_fk` (`surprise_id`);

--
-- Indices de la tabla `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_conversation` (`conversation_id`),
  ADD KEY `idx_messages_sender` (`sender_id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_id` (`user_id`),
  ADD KEY `idx_notifications_read_flag` (`read_flag`);

--
-- Indices de la tabla `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `surprise_id` (`surprise_id`),
  ADD KEY `genius_id` (`genius_id`);

--
-- Indices de la tabla `offer_bids`
--
ALTER TABLE `offer_bids`
  ADD PRIMARY KEY (`id`),
  ADD KEY `offer_id` (`offer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `penalties`
--
ALTER TABLE `penalties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_penalties_user` (`user_id`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indices de la tabla `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reviews_surprise` (`surprise_id`),
  ADD KEY `fk_reviews_reviewer` (`reviewer_id`),
  ADD KEY `fk_reviews_reviewed_user` (`reviewed_user_id`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`);

--
-- Indices de la tabla `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `surpriser_skills`
--
ALTER TABLE `surpriser_skills`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `surpriser_user_skills`
--
ALTER TABLE `surpriser_user_skills`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `surprises`
--
ALTER TABLE `surprises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_surprise_creator` (`creator_id`),
  ADD KEY `fk_surprise_genius` (`genius_id`),
  ADD KEY `fk_surprise_skill` (`skill_id`);

--
-- Indices de la tabla `surprise_files`
--
ALTER TABLE `surprise_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_surprise_files_surprise` (`surprise_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indices de la tabla `user_proposed_skills`
--
ALTER TABLE `user_proposed_skills`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `user_skills`
--
ALTER TABLE `user_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_skills_user` (`user_id`);

--
-- Indices de la tabla `user_suspensions`
--
ALTER TABLE `user_suspensions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_suspensions_user` (`user_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `cancellations`
--
ALTER TABLE `cancellations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `disputes`
--
ALTER TABLE `disputes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `dispute_penalties`
--
ALTER TABLE `dispute_penalties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `genius_point_events`
--
ALTER TABLE `genius_point_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT de la tabla `offers`
--
ALTER TABLE `offers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `offer_bids`
--
ALTER TABLE `offer_bids`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `penalties`
--
ALTER TABLE `penalties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `skills`
--
ALTER TABLE `skills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT de la tabla `surpriser_skills`
--
ALTER TABLE `surpriser_skills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `surpriser_user_skills`
--
ALTER TABLE `surpriser_user_skills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `surprises`
--
ALTER TABLE `surprises`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `surprise_files`
--
ALTER TABLE `surprise_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `user_proposed_skills`
--
ALTER TABLE `user_proposed_skills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `user_skills`
--
ALTER TABLE `user_skills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `user_suspensions`
--
ALTER TABLE `user_suspensions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `cancellations`
--
ALTER TABLE `cancellations`
  ADD CONSTRAINT `fk_cancellations_creator` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cancellations_genius` FOREIGN KEY (`genius_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cancellations_surprise` FOREIGN KEY (`surprise_id`) REFERENCES `surprises` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `fk_conversations_creator` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conversations_genius` FOREIGN KEY (`genius_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conversations_surprise` FOREIGN KEY (`surprise_id`) REFERENCES `surprises` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `disputes`
--
ALTER TABLE `disputes`
  ADD CONSTRAINT `disputes_ibfk_1` FOREIGN KEY (`surprise_id`) REFERENCES `surprises` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disputes_ibfk_2` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disputes_ibfk_3` FOREIGN KEY (`genius_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_disputes_opened_by` FOREIGN KEY (`opened_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `dispute_penalties`
--
ALTER TABLE `dispute_penalties`
  ADD CONSTRAINT `fk_dp_dispute` FOREIGN KEY (`dispute_id`) REFERENCES `disputes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dp_genius` FOREIGN KEY (`genius_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `genius_point_events`
--
ALTER TABLE `genius_point_events`
  ADD CONSTRAINT `gpe_genius_fk` FOREIGN KEY (`genius_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gpe_surprise_fk` FOREIGN KEY (`surprise_id`) REFERENCES `surprises` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `offers`
--
ALTER TABLE `offers`
  ADD CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`surprise_id`) REFERENCES `surprises` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `offers_ibfk_2` FOREIGN KEY (`genius_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `offer_bids`
--
ALTER TABLE `offer_bids`
  ADD CONSTRAINT `fk_offer_bids_offer` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_offer_bids_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `penalties`
--
ALTER TABLE `penalties`
  ADD CONSTRAINT `fk_penalties_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_reviewed_user` FOREIGN KEY (`reviewed_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_reviews_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_reviews_surprise` FOREIGN KEY (`surprise_id`) REFERENCES `surprises` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `surprises`
--
ALTER TABLE `surprises`
  ADD CONSTRAINT `fk_surprise_creator` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_surprise_genius` FOREIGN KEY (`genius_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_surprise_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `surprise_files`
--
ALTER TABLE `surprise_files`
  ADD CONSTRAINT `fk_surprise_files_surprise` FOREIGN KEY (`surprise_id`) REFERENCES `surprises` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_skills`
--
ALTER TABLE `user_skills`
  ADD CONSTRAINT `fk_user_skills_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_suspensions`
--
ALTER TABLE `user_suspensions`
  ADD CONSTRAINT `fk_user_suspensions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
