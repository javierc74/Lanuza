<?php
/**
 * @package ARGPD
 * @subpackage Integration
 * @since 0.0.0
 *
 * @author César Maeso <superadmin@superadmin.es>
 *
 * @copyright (c) 2018, César Maeso (https://superadmin.es)
 */

/**
 * Integration class.
 *
 * @since  0.0.0
 */
class ARGPD_Integration {

	/**
	 * Parent plugin class.
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	protected $plugin = null;


	/**
	 * Constructor.
	 *
	 * @since  0.0.0
	 *
	 * @param string $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		// set parent plugin.
		$this->plugin = $plugin;

		// register scripts and styles.
		$this->register();

		// initiate our hooks.
		$this->hooks();
	}


	/**
	 * Register scripts and Styles for cookies banner
	 *
	 * @since  0.0.0
	 */
	public function register() {

		// Register JavaScript.
		wp_register_script(
			'argpd-cookies-banner',
			$this->plugin->url . 'assets/js/cookies-banner.js',
			array(
				'jquery',
			),
			$this->plugin->version
		);

		// Register Style.
		$settings = $this->plugin->argpd_settings->get_settings();
		wp_register_style(
			'argpd-cookies-banner',
			sprintf( '%sassets/css/cookies-banner-%s.css', $this->plugin->url, $settings['cookies-theme'] ),
			array(),
			$this->plugin->version
		);

		// Register Style duty to inform (Deber de informar).
		wp_register_style(
			'argpd-informbox',
			sprintf( '%sassets/css/inform-box-%s.css', $this->plugin->url, $settings['informbox-theme'] ),
			array(),
			$this->plugin->version
		);

		// Register admin styles.
		wp_register_style(
			'argpd-admin',
			$this->plugin->url . 'assets/css/argpd-admin.css',
			array(),
			$this->plugin->version
		);

	}

	/**
	 * Register scripts and Styles for admin panel
	 */
	public function enqueue_admin_assets() {

		wp_enqueue_style( 'argpd-admin' );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.0.0
	 */
	public function hooks() {

		$settings = $this->plugin->argpd_settings->get_settings();

		if ( $settings['option-footer'] ) {
			// action for show footer.
			add_action( 'wp_footer', array( $this, 'show_footer_links' ) );
			// register legal menu.
			add_action( 'init', array( $this, 'register_legal_menu' ) );
			// filter to add legal menu items.
			add_filter( 'wp_nav_menu_items', array( $this, 'add_menu_legal_items' ), 10, 2 );
		}

		// enque theme styles.
		wp_enqueue_style( 'argpd-cookies-banner' );

		// add hooks if option-comments option is checked.
		if ( $settings['option-comments'] ) {
			while ( true ) {

				// disable if jetpack-comments is active.
				if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'comments' ) ) {
					break;
				}

				add_action( 'pre_comment_on_post', array( $this, 'check_consentimiento' ) );
				add_filter( 'comment_form_submit_field', array( $this, 'add_field' ) );
				break;
			}
		}

		// add hooks if option-cookies option is checked.
		if ( $settings['option-cookies'] ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
			add_action( 'wp_footer', array( $this, 'cookiesbanner_footer' ) );
		}

		// add hooks if have a duty to inform.
		if ( $settings['option-cookies'] || $settings['option-forms'] ) {
			// add css styles.
			wp_enqueue_style( 'argpd-informbox' );
		}

		// add meta noindex.
		add_action( 'wp_head', array( $this, 'noindex_meta' ) );

		// enqueue admin styles and scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}


	/**
	 * Engueue scripts and styles
	 *
	 * @since  0.0.0
	 */
	public function enqueue() {

		/**
		  * Allows to disable enqueuing files on a particular page
		  */
		$enqueue_agrpd = apply_filters( 'wp_agrpd_enqueue', true );

		// Enqueue Scripts.
		wp_enqueue_script( 'argpd-cookies-banner' );

		// Enqueue Styles
		/* wp_enqueue_style( 'argpd-cookies-banner' ); */
	}


	/**
	 * Add view for comment form submit
	 *
	 * @since  0.0.0
	 */
	public function add_field( $submit_field = '' ) {
		$consentimiento_view    = $this->plugin->pages->consentimiento_view();
		$deber_de_informar_view = $this->plugin->pages->deber_de_informar_view();
		return $consentimiento_view . $deber_de_informar_view . $submit_field;
	}


	/**
	 * Test if privacy is checked in comments
	 *
	 * @since  0.0.0
	 */
	public function check_consentimiento() {
		if ( ! isset( $_POST['agdpr-consentimiento'] ) ) {
			wp_die( __( 'Para poder comentar debes aceptar la política de privacidad.' ) );
		}
	}


	/**
	 * Echo cookies banner
	 *
	 * @since  0.0.0
	 */
	public function cookiesbanner_footer() {
		echo $this->plugin->pages->cookiesbanner_view();
	}

	/**
	 * Echo cookies banner
	 *
	 * @since  1.0.0
	 */
	public function show_footer_links() {

		if ( has_nav_menu( 'menu-argpd' ) ) {
			wp_nav_menu(
				array(
					'container'       => 'div',
					'container_class' => 'argpd-footer',
					'menu_class'      => '',
					'theme_location'  => 'menu-argpd',
					'fallback_cb'     => false,
				)
			);
		} else {
			echo $this->plugin->pages->footer_links_view();
		}
	}


	/**
	 * Noindex_meta
	 *
	 * @since  1.0.0
	 */
	public function noindex_meta() {
		if ( ! is_singular() ) {
			return;
		}

		$settings    = $this->plugin->argpd_settings;
		$legal_pages = array(
			(int) $settings->get_setting( 'cookiesID' ),
			(int) $settings->get_setting( 'privacidadID' ),
			(int) $settings->get_setting( 'avisolegalID' ),
		);

		$noindex = ( (int) $settings->get_setting( 'robots-index' ) == 1 ) ? false : true;
		if ( $noindex && in_array( get_the_ID(), $legal_pages ) ) {
				echo "\n\n" . '<meta name="robots" content="noindex,follow" />' . "\n\n";
		}
	}

	/**
	 * Register legal menu.
	 *
	 * @since  1.1
	 */
	public function register_legal_menu() {
		register_nav_menus(
			array(
				'menu-argpd' => esc_html__( 'Menú para los textos legales - RGPD', 'argpd' ),
			)
		);
	}

	/**
	 * Add menu items to legal menu.
	 *
	 * @param string $items items.
	 * @param string $args args.
	 * @since  1.1
	 */
	public function add_menu_legal_items( $items, $args ) {

		if ( 'menu-argpd' == $args->theme_location ) {

			$settings = $this->plugin->argpd_settings;

			$i = '';
			if ( (int) $settings->get_setting( 'avisolegalID' ) > 0 && ! $settings->get_setting( 'avisolegal-disabled' ) ) {
				$aviso_legal_url = $settings->get_setting( 'avisolegalURL' );
				$i              .= sprintf( '<li><a href="%s">%s</a></li>', esc_url( $aviso_legal_url ), esc_html__( 'Aviso Legal', 'argpd' ) );
			}

			if ( (int) $settings->get_setting( 'privacidadID' ) > 0 && ! $settings->get_setting( 'privacidad-disabled' ) ) {
				$privacidad_url = $settings->get_setting( 'privacidadURL' );
				$i             .= sprintf( '<li><a href="%s">%s</a></li>', esc_url( $privacidad_url ), esc_html__( 'Política de Privacidad', 'argpd' ) );
			}

			if ( (int) $settings->get_setting( 'cookiesID' ) > 0 && ! $settings->get_setting( 'cookies-disabled' ) ) {
				$cookies_url = $settings->get_setting( 'cookiesURL' );
				$i          .= sprintf( '<li><a href="%s">%s</a></li>', esc_url( $cookies_url ), esc_html__( 'Política de Cookies', 'argpd' ) );
			}
			$items = $i . $items;
		}
		return $items;
	}

}
