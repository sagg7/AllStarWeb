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
define( 'DB_NAME', 'all_star_tech' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'Corral2010' );

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
define( 'AUTH_KEY',         'o$rQ|6.cl5XnGzs6NjVQ.=[e`R6zPB9N|^J>Gb!$gQ:PeAGOx7eA&K7e[uqVG? k' );
define( 'SECURE_AUTH_KEY',  'JGQZ3yql?~5Qle]8ms %eJ,j0d7&}U-?-95coEyLQr_HCOM^?r&f:>$~lsTao!Y}' );
define( 'LOGGED_IN_KEY',    'kT#+qzdr5gy?IU=CSBOm=6t6j2.I>r-+T?`OPK!M6pj**]>L0Tui=VBU*6OrVZ=B' );
define( 'NONCE_KEY',        'W1*w!?Py>[b@8YmwgJhYr&hVM$z@_u9?WZaMs]@;RD<ZMLS@N[^b@{v`8b8u%pB>' );
define( 'AUTH_SALT',        '5K12CW]FMBRR}KukesC7E?v|GBq8i#dJm~<a}i29_;.K~b )K2%6 @aeH3mH6Q$/' );
define( 'SECURE_AUTH_SALT', 'YD.Km!Y&r%_]QLk(h~o ooX(lr3]!mnEQ&#,hRL!KQ1iu@OENNv-=Pw55G1jt4`J' );
define( 'LOGGED_IN_SALT',   'e3Lo6 |0Abz$F6Pp`2kGiCCjv Jj*O|JF/N}[{bU(*4yTN>Ruk16Q<gZvp$nYhln' );
define( 'NONCE_SALT',       'NRR0CW.EKsqrNy,ZV}?^c.W@:(7Sp7+Wo8zdMK;|>{}i2#v+JyFuGG3xH9K)!}Al' );

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
