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
define( 'DB_NAME', 'te2019szasz.github.io_db' );

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
define( 'AUTH_KEY',         '5#2M}rrJL<!ZsF1~v HvMdI?EOxua k7!~a,_BeY>wu|H3h+2YfZq$5O}wsX~D*@' );
define( 'SECURE_AUTH_KEY',  ':q]4j8BQ6{pJHO?w|RA]nKK>KmfPNJ~<pyP>]il(swLt*7D6xYCq( ^SF%TSx],}' );
define( 'LOGGED_IN_KEY',    '; VZ^|al8bk!EfJwLF&F4)v,{]c7K/y{]y.fCfR;9):#7E%Fbnvh&&k!xL)WlrHD' );
define( 'NONCE_KEY',        'V 703i}P8+vz$o3i?h1Dd>rW.qXxDL2^0u:v0>{@!6>,p&]$PSdN6tosiRN`0C#|' );
define( 'AUTH_SALT',        '%B,%,5LOQb03*9]ifjJ@iUfY2VWfZqW(&WGZaCzq{Mp^:rM__QvE`0BLbz4Pm(G[' );
define( 'SECURE_AUTH_SALT', '&G]^^q%/|jv:Kmy~Qm:y*DdtH$9!(3vCQlk,Gqxm2s}bKe;DIl7AU;/K~Nf?d${,' );
define( 'LOGGED_IN_SALT',   '61&?Pvt:3kr(4F[yg]_#omd=zL^cImyR,GHG~N2WvoNHHjxWy|~Xycaxuo?SX4GF' );
define( 'NONCE_SALT',       'eAji^p*^<=(Q8rPV;=i}tc3(_av%[0%Ay&gN4x>0w&7M=4zQm|`3|E<o9fFA) gT' );

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
