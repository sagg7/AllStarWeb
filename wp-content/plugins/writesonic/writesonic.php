<?php

/**
 * Plugin Name: Writesonic
 * Description: Writesonic WordPress plugin
 * Version: 1.0.1
 * Author: <a href="https://writesonic.com/">Writesonic</a>
 * Author URI: https://writesonic.com/
 * Text Domain: writesonic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

const WRITESONIC_API_KEY_OPTION = 'writesonic_api_key';
const WRITESONIC_CONNECT_URL = 'https://app.writesonic.com/wordpress-authentication/';
const WRITESONIC_CHECK_AUTH_URL = 'http://api.writesonic.com/v1/thirdparty/wordpress-org-authorization-status';

if ( ! class_exists( 'WPM_Writesonic_Integration' ) ) {
	class WPM_Writesonic_Integration {
		/**
		 * Plugin init, filters, hooks
		 */
		public static function init() {
			// Initialize option with empty string
			add_option( WRITESONIC_API_KEY_OPTION, '' );

			add_action( 'admin_menu', array( 'WPM_Writesonic_Integration', 'create_settings_menu' ) );
			add_action( 'rest_api_init', array( 'WPM_Writesonic_Integration', 'register_api_endpoints' ) );
			register_deactivation_hook( __FILE__, array( 'WPM_Writesonic_Integration', 'deactivation' ) );
			add_action( 'admin_init', array( 'WPM_Writesonic_Integration', 'register_settings' ) );

			// Get users w/o posts published
			add_filter( 'rest_user_query', array( 'WPM_Writesonic_Integration', 'remove_has_published_posts_from_wp_api_user_query' ), 10, 2 );

			// Force custom posts to be visible in REST API
			add_filter( 'register_post_type_args', array( 'WPM_Writesonic_Integration', 'custom_post_types_show_in_rest_filter' ), 10, 2 );
		}

		/**
		 * Deactivation hook
		 */
		public static function deactivation() {
			// We can delete key on deactivation, but it's not needed now
			// delete_option(WRITESONIC_API_KEY_OPTION);
		}

		/**
		 * WP settings registration
		 */
		public static function register_settings() {
			register_setting( 'writesonic', WRITESONIC_API_KEY_OPTION );
		}

		/**
		 * Settings menu registration
		 */
		public static function create_settings_menu() {
			add_options_page( 'Writesonic Settings', 'Writesonic', 'manage_options', 'writesonic', array( 'WPM_Writesonic_Integration', 'create_settings_page' ) );
		}

		/**
		 * Settings page registration
		 */
		public static function create_settings_page() {
			include plugin_dir_path( __FILE__ ) . '/templates/settings.php';
		}

		/**
		 * @param $args
		 * @param $post_type
		 *
		 * @return mixed
		 */
		public static function custom_post_types_show_in_rest_filter( $args, $post_type ) {
			$args['show_in_rest'] = true;

			return $args;
		}

		/**
		 * Removes `has_published_posts` from the query args so even users who have not
		 * published content are returned by the request.
		 *
		 * @see https://developer.wordpress.org/reference/classes/wp_user_query/
		 *
		 * @param array $prepared_args Array of arguments for WP_User_Query.
		 * @param WP_REST_Request $request The current request.
		 *
		 * @return array
		 */
		function remove_has_published_posts_from_wp_api_user_query( $prepared_args, $request ) {
			unset( $prepared_args['has_published_posts'] );

			return $prepared_args;
		}

		/**
		 * Register VidApp custom REST API endpoints
		 */
		public static function register_api_endpoints() {
			/**
			 * Categories
			 */
			$categories_controller = new WP_REST_Terms_Controller( 'category' );
			register_rest_route( 'writesonic/v2', '/categories', array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( 'WPM_Writesonic_Integration', 'get_categories' ),
				'permission_callback' => array( 'WPM_Writesonic_Integration', 'get_categories_permissions_check' ),
				'args'                => $categories_controller->get_collection_params()
			) );

			/**
			 * Posts
			 */
			$posts_controller = new WP_REST_Posts_Controller( 'post' );
			register_rest_route( 'writesonic/v2', '/posts', array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( 'WPM_Writesonic_Integration', 'get_posts' ),
					'permission_callback' => array( 'WPM_Writesonic_Integration', 'get_posts_permission_check' ),
					'args'                => $posts_controller->get_collection_params()

				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( 'WPM_Writesonic_Integration', 'create_post' ),
					'permission_callback' => array( 'WPM_Writesonic_Integration', 'create_post_permissions_check' ),
					'args'                => $posts_controller->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( 'WPM_Writesonic_Integration', 'get_public_item_schema' ),
			) );

			/**
			 * Media
			 */
			$attachment_controller = new WP_REST_Attachments_Controller( 'attachment' );
			register_rest_route( 'writesonic/v2', '/media', array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( 'WPM_Writesonic_Integration', 'get_media' ),
					'permission_callback' => array( 'WPM_Writesonic_Integration', 'get_media_permission_check' ),
					'args'                => $attachment_controller->get_collection_params()

				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( 'WPM_Writesonic_Integration', 'create_media' ),
					'permission_callback' => array( 'WPM_Writesonic_Integration', 'create_media_permissions_check' ),
					'args'                => $attachment_controller->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( 'WPM_Writesonic_Integration', 'get_public_item_schema' ),
			) );

			/**
			 * Comments
			 */
			$comment_controller = new WP_REST_Comments_Controller();
			register_rest_route( 'writesonic/v2', '/comments', array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( 'WPM_Writesonic_Integration', 'get_comments' ),
					'permission_callback' => array( 'WPM_Writesonic_Integration', 'get_comments_permission_check' ),
					'args'                => $comment_controller->get_collection_params()

				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( 'WPM_Writesonic_Integration', 'create_comment' ),
					'permission_callback' => array( 'WPM_Writesonic_Integration', 'create_comment_permissions_check' ),
					'args'                => $comment_controller->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( 'WPM_Writesonic_Integration', 'get_public_item_schema' ),
			) );

			/**
			 * Users
			 */
			$users_controller = new WP_REST_Users_Controller();
			register_rest_route( 'writesonic/v2', '/users', array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( 'WPM_Writesonic_Integration', 'get_users' ),
				'permission_callback' => array( 'WPM_Writesonic_Integration', 'get_users_permissions_check' ),
				'args'                => $users_controller->get_collection_params()
			) );

			register_rest_route( 'writesonic/v2', '/password', array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( 'WPM_Writesonic_Integration', 'validate_user_password' ),
				'permission_callback' => array( 'WPM_Writesonic_Integration', 'validate_password_permissions_check' ),
				'args'                => array(
					'password' => array(
						'default'           => null,           // значение параметра по умолчанию
						'required'          => true,           // является ли параметр обязательным. Может быть только true
						'sanitize_callback' => 'sanitize_text_field', // функция очистки значения параметра. Должна вернуть очищенное значение
					)
				)
			) );
		}

		public static function get_user_by_token( $token, $user ) {
			$wpb_writesonic_tokens = get_option( WRITESONIC_API_KEY_OPTION );

			if ( ! is_array( $wpb_writesonic_tokens ) ) {
				return $user;
			}

			$user_email = array_search( $token, $wpb_writesonic_tokens );

			if ( $user_email ) {
				$user = get_user_by( 'email', $user_email );

				return $user->ID;
			}

			return $user;
		}

		public static function get_public_item_schema() {
			$posts_controller = new WP_REST_Posts_Controller( 'post' );

			return $posts_controller->get_public_item_schema();
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return bool
		 */
		public static function get_users_permissions_check( WP_REST_Request $request ) {
			return self::checkAPIKeyAuth( $request );
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return bool
		 */
		public static function get_categories_permissions_check( WP_REST_Request $request ) {
			return self::checkAPIKeyAuth( $request );
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return mixed
		 */
		public static function get_users( WP_REST_Request $request ) {
			$controller = new WP_REST_Users_Controller();

			$response = $controller->get_items( $request );

			return $response;
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return mixed
		 */
		public static function get_categories( WP_REST_Request $request ) {
			$controller = new WP_REST_Terms_Controller( 'category' );
			$response   = $controller->get_items( $request );

			return $response;
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return mixed
		 */
		public static function get_posts_permission_check( WP_REST_Request $request ) {
			return self::checkAPIKeyAuth( $request );
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return mixed
		 */
		public static function get_comments( WP_REST_Request $request ) {
			$controller = new WP_REST_Comments_Controller();
			$response   = $controller->get_items( $request );

			return $response;
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return bool
		 */
		public static function get_comments_permission_check( WP_REST_Request $request ) {
			return self::checkAPIKeyAuth( $request );
		}

		/**
		 * Get posts
		 *
		 * @param WP_REST_Request $request
		 * @return string
		 */
		public static function get_posts( WP_REST_Request $request ) {
			if ( isset( $request['post_type'] ) ) {
				$controller = new WP_REST_Posts_Controller( sanitize_text_field( $request['post_type'] ) );
			} else {
				$controller = new WP_REST_Posts_Controller( 'post' );
			}

			$response = $controller->get_items( $request );

			return $response;
		}

		/**
		 * Check if correct API key provided in request
		 *
		 * @param WP_REST_Request $request
		 *
		 * @return bool
		 */
		public static function checkAPIKeyAuth( WP_REST_Request $request ) {
			$auth             = isset( $_SERVER['HTTP_TOKEN'] ) ? sanitize_text_field( $_SERVER['HTTP_TOKEN'] ) : false;
			$writesonic_token_key = get_option( WRITESONIC_API_KEY_OPTION, true );

			wp_set_current_user(self::get_user_by_token($auth, $user));

			if ( is_array($writesonic_token_key) && in_array($auth, $writesonic_token_key) ) {
				return true;
			}

			return false;
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return bool
		 */
		public static function create_post_permissions_check( WP_REST_Request $request ) {
			return self::checkAPIKeyAuth( $request );
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return mixed
		 */
		public static function create_post( WP_REST_Request $request ) {
			$post_type = 'post';
			if ( isset( $request['post_type'] ) && $request['post_type'] ) {
				$post_type = $request['post_type'];
			}

			$controller = new WP_REST_Posts_Controller( $post_type );
			$response   = $controller->create_item( $request );

			return $response;
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return mixed
		 */
		public static function get_media_permission_check( WP_REST_Request $request ) {
			return self::checkAPIKeyAuth( $request );
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return mixed
		 */
		public static function get_media( WP_REST_Request $request ) {
			$controller = new WP_REST_Attachments_Controller( 'attachment' );
			$response   = $controller->get_items( $request );

			return $response;
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return mixed
		 */
		public static function create_media_permissions_check( WP_REST_Request $request ) {
			return self::checkAPIKeyAuth( $request );
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return mixed
		 */
		public static function create_media( WP_REST_Request $request ) {
			$controller = new WP_REST_Attachments_Controller( 'attachment' );
			$response   = $controller->create_item( $request );

			return $response;
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return mixed
		 */
		public static function create_comment_permissions_check( WP_REST_Request $request ) {
			return self::checkAPIKeyAuth( $request );
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return mixed
		 */
		public static function create_comment( WP_REST_Request $request ) {
			$controller = new WP_REST_Comments_Controller();
			$response   = $controller->create_item( $request );

			return $response;
		}

		/**
		 * @param WP_REST_Request $request
		 *
		 * @return bool
		 */
		public static function validate_password_permissions_check( WP_REST_Request $request ) {
			return self::checkAPIKeyAuth( $request );
		}

		/**
		 * Validate user's password
		 *
		 * @param WP_REST_Request $request
		 * @return string
		 */
		public static function validate_user_password( WP_REST_Request $request ) {
			if ( ! isset( $request['password'] ) || ! isset( $request['user_id'] ) ) {
				return [];
			}

			$password = $request['password'];
			$user     = get_user_by( 'id', $request['user_id'] );
			if ( wp_check_password( $password, $user->user_pass ) ) {
				$data = array( 'result' => 'true' );
			} else {
				$data = array( 'result' => 'false' );
			}

			$response = rest_ensure_response( $data );

			wp_send_json($response);
		}

		/**
		 * Check authorization
		 *
		 * @param string $token
		 * @param string $domain
		 * @return bool
		 */
		public static function checkAuthorization( $token, $domain ) {
			$body = [
				'token'  => $token,
				'domain' => $domain,
			];

			$body = wp_json_encode( $body );

			$options = [
				'body'        => $body,
				'headers'     => [
					'Content-Type' => 'application/json',
				],
				'data_format' => 'body',
			];

			$request = wp_remote_post( WRITESONIC_CHECK_AUTH_URL, $options );

			$tmp = json_decode( wp_remote_retrieve_body( $request ), true );
			
			return (bool) $tmp;
		}
	}

	WPM_Writesonic_Integration::init();
}