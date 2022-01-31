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
define( 'DB_NAME', 'egyalma_db' );

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
define( 'AUTH_KEY',         'j/oS)]TxQ6fCjS%g=~js?HHs}~.m9;#v`61RC/l*3apYxg{lb,Nn:-|dy})q+AR0' );
define( 'SECURE_AUTH_KEY',  '2!Tq`3Pe_7QmFg7|0E%^RbeI8THsL9Z:0L:9qVwcsY?Ug(]k=Xw$xvNYAy@~|h}6' );
define( 'LOGGED_IN_KEY',    '-A5G;fhuzN6oc9~U2D^|Jq{j`6XFr/d8e9eJSQx[=7ua+)ScJ[jqd.<86ORX{Zfa' );
define( 'NONCE_KEY',        '5z;b3@=2]H[Yv_(WlSF0vW9i{|,qh1(-rx@DWu2^DiXLbF#)Ae_Kbz7C:9L$> >`' );
define( 'AUTH_SALT',        'KDQl;U&>}u~!b[K]/(CO1EXFf?@X8e`BT}5(=[mmT=eLMIaHI}sF>iNd 5CCQ:,]' );
define( 'SECURE_AUTH_SALT', '[K?B;z!~ZT:Paa!jn=DD&q2d8RCv0t$GTDmsfa[b11j~fGk#S45Th1%+3-uHC_cA' );
define( 'LOGGED_IN_SALT',   'kcN?~0i1@g5 %@_yuLd>aB=MoxuM>Zj3y/MM07<o^)p?vvVUrjlri?ae*(yA)[9f' );
define( 'NONCE_SALT',       'yP&Y!X_h2t<i*P_ruL|yOK`6RQx<=o[Jw1)<BJc{jV]mROoK|@kPE*i.0UMXiV:-' );

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
