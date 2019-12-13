<?php
/** 
 * Configuración básica de WordPress.
 *
 * Este archivo contiene las siguientes configuraciones: ajustes de MySQL, prefijo de tablas,
 * claves secretas, idioma de WordPress y ABSPATH. Para obtener más información,
 * visita la página del Codex{@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} . Los ajustes de MySQL te los proporcionará tu proveedor de alojamiento web.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** Ajustes de MySQL. Solicita estos datos a tu proveedor de alojamiento web. ** //
/** El nombre de tu base de datos de WordPress */
define('DB_NAME', 'lanuza');

/** Tu nombre de usuario de MySQL */
define('DB_USER', 'root');

/** Tu contraseña de MySQL */
define('DB_PASSWORD', 'root');

/** Host de MySQL (es muy probable que no necesites cambiarlo) */
define('DB_HOST', 'localhost');

/** Codificación de caracteres para la base de datos. */
define('DB_CHARSET', 'utf8mb4');

/** Cotejamiento de la base de datos. No lo modifiques si tienes dudas. */
define('DB_COLLATE', '');

/**#@+
 * Claves únicas de autentificación.
 *
 * Define cada clave secreta con una frase aleatoria distinta.
 * Puedes generarlas usando el {@link https://api.wordpress.org/secret-key/1.1/salt/ servicio de claves secretas de WordPress}
 * Puedes cambiar las claves en cualquier momento para invalidar todas las cookies existentes. Esto forzará a todos los usuarios a volver a hacer login.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'r{21e)R$EiN~DnN</;8Uy=%ccr9-M.i*/mJY@!4W `B>-$8?d5HQbuB>(P|6Rt5.');
define('SECURE_AUTH_KEY', 'oq7??9r?-RYM|eNg.6I5/lnQCeZt0bg3TQQu)yc$17$LS+|;O}fw$r>Vob|S;h`q');
define('LOGGED_IN_KEY', 'wEskRCuO~uC4m{Gx}dk;&TFTI 5Z`6=Wg#0}K_`R~0V&^ =,w#,0|36C-uhCk:5D');
define('NONCE_KEY', '@0SdrqX0]C8@kEoRk7>X&:BvAA7C1nl4I8?U4H=urLS$XOOQS6<6y~K@9ptW,nWA');
define('AUTH_SALT', 'By*?`x9Xy;y#K{&%Ag;Tq/CYF>C#i&12(I.]zua4Q<~[:o~Ds7L+Tov(k}PrvLvo');
define('SECURE_AUTH_SALT', '=AntOhTpopelF~1?.zk<,PAT@rJ:%YQO28:W*JB2A4#Uo(GRHi^QVqhGtJ-5--:S');
define('LOGGED_IN_SALT', '<je)2Y{jZ889twTV%-RR`!kdc,1U*b}KV]lCci.qTIB-Vkh^;Jv$k*eA7yW%SxTL');
define('NONCE_SALT', 'gI4F:k)zP2NWbaYqZL}ubh1S{kmj)tIt<~FnJ|K;%N[(63{LsqI1F(H2oOaS=$?B');

/**#@-*/

/**
 * Prefijo de la base de datos de WordPress.
 *
 * Cambia el prefijo si deseas instalar multiples blogs en una sola base de datos.
 * Emplea solo números, letras y guión bajo.
 */
$table_prefix  = 'wp_';


/**
 * Para desarrolladores: modo debug de WordPress.
 *
 * Cambia esto a true para activar la muestra de avisos durante el desarrollo.
 * Se recomienda encarecidamente a los desarrolladores de temas y plugins que usen WP_DEBUG
 * en sus entornos de desarrollo.
 */
define('WP_DEBUG', false);

/* ¡Eso es todo, deja de editar! Feliz blogging */

/** WordPress absolute path to the Wordpress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

