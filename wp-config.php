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
define( 'AUTH_KEY',         'H./p7J{zcru>8Pd N^Yma%7W<NRY=S`Q.IWM-eNHS0sp-lihn<i^8fh8gS]F0,n_' );
define( 'SECURE_AUTH_KEY',  '6mx(NhthU4_EV30tIo[en{SpxmMft[wBe?Or/CH<p{?=1E~C3_?>:$zKMjoUS{(J' );
define( 'LOGGED_IN_KEY',    'g+VP>2MkH7ek<9%)]25U E,#.dnma@tjW84aQ_)dP+AQ<fWBXEx2.(+-1wPV?0pn' );
define( 'NONCE_KEY',        '+4W~?(tWEJ#8[.8`0YW+[Q(]iD;-/jt~*?<qjJDH2_B{K ,`_R}TlK,Ne6%:r!gA' );
define( 'AUTH_SALT',        'N8G)kp6-~A039{5I)I-Ffhll3Ex-c4ZAT#6Ri5R }IYK=)o5ljz}5awO#{k&>ge+' );
define( 'SECURE_AUTH_SALT', 'L0|iT50JfL%!1$@A<O=?k{BY&/Y?8_<9Lhh?rB*CL9Lla/<a4aXw7W#$.xI>8Z{<' );
define( 'LOGGED_IN_SALT',   ';7KWxf2#X:eqe41Ms{rxI)m:3_~`|Q?jMjpD[<<{n?rrmXTxswRN4gUNV[W7(&Jc' );
define( 'NONCE_SALT',       '(Sw7Exv-=<d?`/[q:@)7,1J=m|r+Aa1y3s:ILG}+9n,d03XxGeqzLr&z2v<vKtpm' );

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
