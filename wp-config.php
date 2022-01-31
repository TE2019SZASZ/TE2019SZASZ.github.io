<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'aaa_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Ts%,l>H]hnHmyj9]%q*_H0xeD~|V0wUW2O$)g{]Ks%tFSr6Fvy;t!]tw#/TGovaS' );
define( 'SECURE_AUTH_KEY',  '.vX e *ah}VC6`!DBo,B&F(D]>JGAw,|uTa#0ZWR;g6+!ggD6VM)TZ|.BFnFj}$d' );
define( 'LOGGED_IN_KEY',    'EIS!&`M%ua nh}Lg)&AP;<G0pR$[e^=X`xv!9fNE]nWWd8!wI1<KDr1&VB>8B).*' );
define( 'NONCE_KEY',        '/R,!;q1 jJmf%:rmd+q|i?DIEj7h  -qr?`@s(We*m`;@VM%W8h=bA:Bn&ptW}T~' );
define( 'AUTH_SALT',        '93aboTNbN/wiIYZqx:ey~qu.hr{O2YCP~^n/I&LQ8#OXy6j.Jo*Kxw;fB]W-p:9|' );
define( 'SECURE_AUTH_SALT', ']1{roW&{WdZIH2*|[Tf/883qwbiE >jY@GXaJ t;-L9f6cr=s/580[vW4ysi)d)x' );
define( 'LOGGED_IN_SALT',   'mrh(: >>7JoJ!BvAenu*Ax|`28A/|HoX6{hq,1kym5v+yVUslfz*s5A99k(_Sw=p' );
define( 'NONCE_SALT',       'H%`Z@l2|:lptf)U/R9j{p.UFwvZnB}s4sbd;)@/)Jqc]9F^uEiTE3bJHm{v&5;=N' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
