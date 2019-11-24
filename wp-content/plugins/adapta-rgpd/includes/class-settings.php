<?php
/**
 * @package ARGPD
 * @subpackage Settings
 * @since 0.0.0
 *
 * @author César Maeso <superadmin@superadmin.es>
 *
 * @copyright (c) 2018, César Maeso (https://superadmin.es)
 */

/**
 * Settings class.
 *
 * @since  0.0.0
 */
class ARGPD_Settings {

	/**
	 * Parent plugin class.
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	protected $plugin = null;

	/**
	 * Property key
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	protected $key = 'argpd';

	/**
	 * Property themes
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	protected $informbox_themes = null;

	/**
	 * Property themes
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	protected $cookie_themes = null;

	/**
	 * Property countries
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	protected $countries = null;

	/**
	 * Property states
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	protected $states = null;


	/**
	 * Property settings array
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	protected $settings = array(
		'renuncia'              => 0,
		// pages.
		'avisolegalID'          => 0,
		'privacidadID'          => 0,
		'cookiesID'             => 0,
		'avisolegalURL'         => '',
		'privacidadURL'         => '',
		'cookiesURL'            => '',
		'avisolegal-disabled'   => 0,
		'cookies-disabled'      => 0,
		'privacidad-disabled'   => 0,
		'cookies-label'         => '',
		'cookies-btnlabel'      => '',
		'cookies-linklabel'     => '',
		'cookies-theme'         => 'modern-light',
		'consentimiento-label'  => '',
		'informbox-theme'       => 'simple',
		'robots-index'          => 0,
		// owner.
		'dominio'               => '',
		'titular'               => '',
		'id-fiscal'             => '',
		'colegio'               => '',
		'domicilio'             => '',
		'provincia'             => '',
		'provincia-code'        => '',
		'pais'                  => 'ES',
		'pais-nombre'           => '',
		'pais-ue'               => 1,
		'correo'                => '',
		// settings.
		'finalidad'             => '',
		'hosting-info'          => '',
		// options.
		'option-comments'       => 0,
		'option-cookies'        => 0,
		'option-forms'          => 0,
		'option-footer'         => 0,
		'option-formal'			=> 0,
		// clauses.
		'clause-exclusion'      => 0,
		'clause-thirdparty'     => 0,
		'clause-edad'           => 0,
		'clause-terceros'       => 0,
		'clause-protegidos'     => 0,
		'clause-portabilidad'   => 0,
		'thirdparty-dclick'     => 0,
		'thirdparty-ganalytics' => 0,
		'thirdparty-social'     => 0,
		'thirdparty-mailchimp'  => 0,
		'thirdparty-mailrelay'  => 0,
		'thirdparty-amazon'     => 0,
		'thirdparty-sendinblue' => 0,
		// 'thirdparty-links'        => 1,
		// deber de informar.
		'deber-finalidad'       => '',
		'deber-destinatarios'   => '',
	);


	/**
	 * Constructor.
	 *
	 * @since  0.0.0
	 *
	 * @param  string $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		$this->informbox_themes = [
			'simple'        => __( 'Simple', 'argpd' ),
			'border'        => __( 'Con borde', 'argpd' ),
			'border-number' => __( 'Borde + Números', 'argpd' ),
		];

		$this->cookie_themes = [
			'classic'      => __( 'Clásico', 'argpd' ),
			'classic-top'  => __( 'Clásico en parte superior', 'argpd' ),
			'modern-light' => __( 'Moderno Claro', 'argpd' ),
			'modern-dark'  => __( 'Moderno Oscuro', 'argpd' ),
		];

		$this->countries = [
			'AR' => __( 'Argentina', 'argpd' ),
			'CO' => __( 'Colombia', 'argpd' ),
			'CL' => __( 'Chile', 'argpd' ),
			'EC' => __( 'Ecuador', 'argpd' ),
			'ES' => __( 'España', 'argpd' ),
			'FR' => __( 'Francia', 'argpd' ),
			'GT' => __( 'Guatemala', 'argpd' ),
			'MX' => __( 'Méjico', 'argpd' ),
			'PE' => __( 'Perú', 'argpd' ),
			'VE' => __( 'Venezuela', 'argpd' ),
		];

		$this->plugin = $plugin;

		$this->init_settings();
	}


	/**
	 * Init settings.
	 *
	 * @since  0.0.0
	 */
	public function init_settings() {

		$network_id = null;
		if ( is_multisite() ) {
			$network_id = get_current_blog_id();
		}

		// get all settings.
		foreach ( $this->settings as $name => $text ) {
			$value = get_network_option( $network_id, sprintf( '%s_%s', $this->key, $name ) );
			if ( $value ) {
				$this->settings[ $name ] = $value;
			}
		}

		// get url option stored or get_site_url
		$dominio = $this->settings['dominio'];
		$this->settings['dominio'] = esc_url( strlen( $dominio ) == 0 ? get_site_url() : $dominio );

		// get legal pages permalinks.
		$cookiesID = intval( $this->settings['cookiesID'] );
		if ( is_int( $cookiesID ) && $cookiesID > 0 ) {
			$this->settings['cookiesURL'] = get_permalink( $cookiesID );
		}

		$avisolegalID = intval( $this->settings['avisolegalID'] );
		if ( is_int( $avisolegalID ) && $avisolegalID > 0 ) {
			$this->settings['avisolegalURL'] = get_permalink( $avisolegalID );
		}

		$privacidadID = intval( $this->settings['privacidadID'] );
		if ( is_int( $privacidadID ) && $privacidadID > 0 ) {
			$this->settings['privacidadURL'] = get_permalink( $privacidadID );
		}

		// configure cookies-btnlabel default value.
		if ( ! strlen( $this->settings['cookies-btnlabel'] ) ) {
			$this->settings['cookies-btnlabel'] = __( 'Aceptar', 'argpd' );
		}

		// configure cookies-linklabel default value.
		if ( ! strlen( $this->settings['cookies-linklabel'] ) ) {
			$this->settings['cookies-linklabel'] = __( 'Ver', 'argpd' );
		}

		$this->convert_regional_codes();
		//
		//$this->update_setting( 'options', $this->settings );
	}

	/**
	 * Convert_regional_codes
	 *
	 * @since  0.0.0
	 */
	private function convert_regional_codes() {

		// convert cc2 to string.
		$cc2 = $this->settings['pais'];
		foreach ( $this->countries as $key => $value ) {
			if ( $key == $cc2 ) {
				$this->settings['pais-nombre'] = $value;
			}
		}

		// is ue country.
		$this->settings['pais-ue'] = ( 'ES' == $cc2 || 'FR' == $cc2 ) ? 1 : 0;

		// convert state-cc2 to string.
		$state_code = $this->settings['provincia-code'];
		$states     = $this->get_states( $cc2 );
		foreach ( $states as $i ) {
			if ( $i['code'] == $state_code ) {
				$this->settings['provincia'] = $i['name'];
			}
		}
	}




	/**
	 * Reset settings.
	 *
	 * @since  0.0.0
	 */
	public function reset() {

		$this->update_setting( 'clause-exclusion', 0 );
		$this->update_setting( 'clause-terceros', 0 );
		$this->update_setting( 'clause-edad', 0 );
		$this->update_setting( 'clause-protegidos', 0 );
		$this->update_setting( 'clause-portabilidad', 0 );

		$this->update_setting( 'thirdparty-dclick', 0 );
		$this->update_setting( 'thirdparty-ganalytics', 0 );
		$this->update_setting( 'thirdparty-social', 0 );
		$this->update_setting( 'thirdparty-mailchimp', 0 );
		$this->update_setting( 'thirdparty-mailrelay', 0 );
		$this->update_setting( 'thirdparty-amazon', 0 );
		$this->update_setting( 'thirdparty-sendinblue', 0 );
		// $this->update_setting('thirdparty-links', 0);
	}


	/**
	 * Returns all settings
	 *
	 * @return array
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Returns themes
	 *
	 * @return array
	 */
	public function get_cookie_themes() {
		return $this->cookie_themes;
	}

	/**
	 * Returns themes
	 *
	 * @return array
	 */
	public function get_informbox_themes() {
		return $this->informbox_themes;
	}

	/**
	 * Returns countries
	 *
	 * @return array
	 */
	public function get_countries() {
		return $this->countries;
	}


	/**
	 * Returns states
	 *
	 * @param  string $country country.
	 * @return array
	 */
	public function get_states( $country ) {

		$fn = sprintf( '%s/../assets/json/%s.json', dirname( __FILE__ ), strtolower( $country ) );
		if ( file_exists( $fn ) ) {
			$str  = file_get_contents( $fn );
			$json = json_decode( $str, true );

			// catch error.
			if ( null === $json && JSON_ERROR_NONE !== json_last_error() ) {
				return array();
			}

			$states = array();
			foreach ( $json as $state ) {
				array_push(
					$states,
					array(
						'name' => $state['name'],
						'code' => $state['code'],
					)
				);
			}
			return $states;
		}

		return array();
	}


	/**
	 * Returns the value of given setting key, based on if network settings are enabled or not
	 *
	 * @param string $name Setting to fetch.
	 * @param string $default Default Value.
	 *
	 * @return bool|mixed|void
	 */
	public function get_setting( $name = '', $default = false ) {

		if ( empty( $name ) ) {
			return false;
		}

		return $this->settings[ $name ];
	}


	/**
	 * Update value for given setting key
	 *
	 * @param string $name Key.
	 * @param string $value Value.
	 *
	 * @return bool If the setting was updated or not
	 */
	public function update_setting( $name = '', $value = '' ) {

		if ( empty( $name ) ) {
			return false;
		}

		$network_id = null;
		if ( is_multisite() ) {
			$network_id = get_current_blog_id();
		}

		$value = trim( sanitize_text_field( $value ) );

		if ( update_network_option( $network_id, sprintf( '%s_%s', $this->key, $name ), $value ) ) {
			$this->settings[ $name ] = $value;
			( 'provincia-code' == $name || 'pais' == $name ) && $this->convert_regional_codes();
			return true;
		}
		return false;
	}

}
