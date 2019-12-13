<?php
/**
 * @package ARGPD
 * @subpackage Ui
 * @since 0.0.0
 *
 * @author César Maeso <info@superadmin.es>
 *
 * @copyright (c) 2018, César Maeso (https://superadmin.es)
 */

/**
 * Ui class.
 *
 * @since  0.0.0
 */
class ARGPD_Ui {

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
	 * @param string $plugin Plugin name.
	 */
	public function __construct( $plugin ) {

		// set parent plugin.
		$this->plugin = $plugin;

		// initiate our hooks.
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.0.0
	 */
	public function hooks() {

		// config tab.
		add_action( 'argpd_settings_tab', array( $this, 'argpd_ajustes_tab' ), 1 );
		add_action( 'argpd_settings_content', array( $this, 'argpd_ajustes_content' ) );

		// pages tab.
		add_action( 'argpd_settings_tab', array( $this, 'argpd_paginas_tab' ), 1 );
		add_action( 'argpd_settings_content', array( $this, 'argpd_paginas_content' ) );

		// ayuda.
		add_action( 'argpd_settings_tab', array( $this, 'argpd_ayuda_tab' ), 1 );
		add_action( 'argpd_settings_content', array( $this, 'argpd_ayuda_content' ) );

		// ajax scripts.
		add_action( 'admin_footer', array( $this, 'argpd_change_country' ) );
		add_action( 'wp_ajax_argpd_get_states', array( $this, 'argpd_get_states' ) );

		add_action( 'admin_footer', array( $this, 'create_page_events' ) );
		add_action( 'wp_ajax_argpd_create_page', array( $this, 'create_page' ) );
	}


	/**
	 * Function wp-ajax to create pages
	 *
	 * @since  1.0.1
	 */
	public function create_page() {
		check_ajax_referer( 'argpd_create_page', 'security' );

		$id   = 0;
		$page = ! empty( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : '';

		switch ( $page ) {
			case 'crear-pagina-legal':
				$id = $this->plugin->pages->create_legal_page();
				break;
			case 'crear-pagina-privacidad':
				$id = $this->plugin->pages->create_privacy_page();
				break;
			case 'crear-pagina-cookies':
				$id = $this->plugin->pages->create_cookies_page();
				break;
			default:
				break;
		}
		echo esc_attr( $id );
		wp_die();
	}

	/**
	 * Javascript events to create page
	 *
	 * @since  1.0.1
	 */
	public function create_page_events() { ?>

		<script type="text/javascript" >			
			
			var ajaxurl = '<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>';

			jQuery(document).ready(function($) {				
				$('.crear-pagina').on('click', function(e){    				
					var pagename= e.target.id;
					if(pagename != '') {
						var data = {
							action: 'argpd_create_page',
							page: pagename,
							'security': '<?php echo esc_attr( wp_create_nonce( 'argpd_create_page' ) ); ?>'
						}
						$.post(ajaxurl, data, function(response) {    						
							  if (!isNaN(parseFloat(response)) && isFinite(response) && response>0){
								  location.replace(location.href+"&message=saved");
							  } else {
								  location.replace(location.href+"&message=");
							  }
						});
					}
				});
			});
		</script> 
		<?php
	}

	/**
	 * Function wp-ajax to echo states
	 *
	 * @since  1.0.0
	 */
	public function argpd_change_country() {
		?>

		<script type="text/javascript" >			
			
			var ajaxurl = '<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>';

			jQuery(document).ready(function($) {
				
				$('body').on('change', '.countries', function() {    				
					  var countryid = $(this).val();
					  if(countryid != '') {
						var data = {
							  action: 'argpd_get_states',
							  country: countryid,
							  'security': '<?php echo esc_attr( wp_create_nonce( 'load_states' ) ); ?>'
						}
						
						$('.load-state').html("<span><?php esc_html_e( 'cargando...', 'argpd' ); ?></span>");
						$.post(ajaxurl, data, function(response) {
							  $('.load-state').html(response);
						});
					  }
				});
			});
		</script> 
		<?php
	}

	/**
	 * Function wp-ajax to get states by country
	 *
	 * @since  1.0.0
	 */
	public function argpd_get_states() {
		check_ajax_referer( 'load_states', 'security' );
		$country = ! empty( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : 'ES';

		$settings = $this->plugin->argpd_settings;
		$states   = $settings->get_states( $country );
		?>
		 
			<select name="provincia-code" id="provincia-code">
				<option value="" selected="selected">Selecciona</option>
				<?php
				foreach ( $states as $i ) {
					$selected = ( $i['code'] == $settings->get_setting( 'provincia-code' ) ) ? ( 'selected="selected"' ) : '';
					printf( '<option value="%s" %s>%s</option>', esc_attr( $i['code'] ), esc_attr( $selected ), esc_html( $i['name'] ) );
				}
				?>
			</select>

		<?php
		wp_die();
	}


	/**
	 * Echo 'Titular' tab of plugin settings
	 *
	 * @since  0.0.0
	 */
	public function argpd_ajustes_tab() {
		global $argpd_active_tab;
		$classname = ( ! empty( $argpd_active_tab ) && 'ajustes' == $argpd_active_tab ) ? 'nav-tab-active' : '';
		?>
		<a 	class="nav-tab <?php echo esc_attr( $classname ); ?>" 
			href="<?php echo esc_attr( admin_url( 'admin.php?page=argpd&tab=ajustes' ) ); ?>">
			<?php esc_html_e( 'Titular', 'argpd' ); ?> 
		</a>
		<?php
	}


	/**
	 * Echo 'Titular' content of plugin settings
	 *
	 * @since  0.0.0
	 */
	public function argpd_ajustes_content() {
		global $argpd_active_tab;
		if ( empty( $argpd_active_tab ) || 'ajustes' != $argpd_active_tab ) {
			return;
		}

		$settings = $this->plugin->argpd_settings;
		?>
		
		
		<form method="post" action="admin-post.php" style="padding-top: 20px">
			<?php wp_nonce_field( 'argpd' ); ?>
			<input type="hidden" value="argpd_setup" name="action"/>
			
			<div id="message" class="argpd-message">
				<p>
					Haz los cambios que necesites y pulsa en <b>Guardar Cambios</b> para actualizar los textos legales.
				</p>
			</div>

			<div>
				<h2 class="title"><?php esc_html_e( 'Sobre el Titular', 'argpd' ); ?></h2>
				<!--<p>
					Aquí añades información sobre el titular (Persona física o empresa) y sobre el sitio Web.
				</p>-->

				<!--<p>
					Al pulsar el botón <b>Guardar cambios</b> actualizarás el contenido de las páginas <i>Aviso Legal</i>, <i>Política de Privacidad</i> y <i>Política de Cookies</i>.
				</p>-->


				

			</div>

							
			<table class="form-table">
				<tbody>

					<?php /* Titular */ ?>
					<tr>
						<th scope="row">
							<label for="titular">
								<?php esc_html_e( 'Titular', 'argpd' ); ?>
							</label>
						</th>
						<td>
							<input 	type="text" 
									name="titular" 
									value="<?php echo esc_attr( $settings->get_setting( 'titular' ) ); ?>" 
									/>
							<p class="description">
								<?php
								printf(
									'%s<br>%s',
									esc_html__( 'Nombre y apellidos del titular o razón', 'argpd' ),
									esc_html__( 'social si es una empresa.', 'argpd' )
								);
								?>
							</p>				
						</td>
					</tr>
					
					<?php /* Identificador fiscal */ ?>
					<tr>
						<th scope="row">
							<label for="id-fiscal">
							<?php
								printf(
									'%s<br>%s',
									esc_html__( 'Identificador fiscal', 'argpd' ),
									esc_html__( 'NIF o CIF', 'argpd' )
								);
							?>
							</label>
						</th>
						<td>

							<input 	type="text" 
									name="id-fiscal" 
									value="<?php echo esc_attr( $settings->get_setting( 'id-fiscal' ) ); ?>" 
									/>
							<!--<p class="description">
								NIF o CIF del titular
							</p>-->				
						</td>
					</tr>

					<?php /* Colegio Profesional */ ?>
					<tr>
						<th scope="row">
							<label for="colegio">								
							<?php
								printf(
									'%s<br>%s',
									esc_html__( 'Datos del', 'argpd' ),
									esc_html__( 'Colegio Profesional', 'argpd' )
								);
							?>
							</label>
						</th>
						<td>
							<input 	type="text" 
									name="colegio" 
									value="<?php echo esc_attr( $settings->get_setting( 'colegio' ) ); ?>" 
									/>
							<!--<p class="description">
								Datos del Colegio Profesional si ejereces una profesión regulada.
							</p>-->	
							<p class="description">								
								<?php esc_html_e( 'Opcional.', 'argpd' ); ?>
							</p>			
						</td>
					</tr>					

					<?php /* Domicilio */ ?>
					<tr>
						<th scope="row">
							<label for="domicilio"><?php esc_html_e( 'Domicilio', 'argpd' ); ?></label>
						</th>
						<td>

							<input 	type="text" 
									name="domicilio" 
									value="<?php echo esc_attr( $settings->get_setting( 'domicilio' ) ); ?>" 
									/>
							<p>								
							
							<span class="load-state">
							<select name="provincia-code" id="provincia-code">
								<option value="" selected="selected">Selecciona</option>
								<?php
								// $provincias= $settings->get_states();
								$country = $settings->get_setting( 'pais' );
								$states  = $settings->get_states( $country );
								foreach ( $states as $i ) {
									$selected = ( $i['code'] == $settings->get_setting( 'provincia-code' ) ) ? ( 'selected="selected"' ) : '';
									printf( '<option value="%s" %s>%s</option>', esc_attr( $i['code'] ), esc_attr( $selected ), esc_html( $i['name'] ) );
								}
								?>
							</select>

							</span>

							<select name="pais" id="pais" class="countries">
								<?php
									$countries = $settings->get_countries();
								foreach ( $countries as $key => $value ) {
									$selected = ( $key == $settings->get_setting( 'pais' ) ) ? ( 'selected="selected"' ) : '';
									printf( '<option value="%s" %s>%s</option>', esc_attr( $key ), esc_attr( $selected ), esc_html( $value ) );
								}
								?>
							</select>
							</p>

							<!--<p class="description">
								Domicilio del titular o Domicilio Fiscal
							</p>-->				
						</td>
					</tr>						
					
					
					<?php /* correo electrónico */ ?>
					<tr>
						<th scope="row">
							<label for="correo">								
							<?php
								printf(
									'%s<br>%s',
									esc_html__( 'Correo electrónico', 'argpd' ),
									esc_html__( 'de contacto', 'argpd' )
								);
							?>
							</label>
						</th>
						<td>

							<input 	type="text" name="correo" value="<?php echo esc_attr( $settings->get_setting( 'correo' ) ); ?>" />
							<p class="description">
								<?php
									printf(
										'%s<br>%s',
										esc_html__( 'Es la dirección de contacto dónde ejercen', 'argpd' ),
										esc_html__( 'sus derechos los usuarios.', 'argpd' )
									);
								?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
			<?php submit_button(); ?>

			<?php /* Sobre el sitio web */ ?>
			<br>
			<div>
				<h2 class="title"><?php esc_html_e( 'Sobre el sitio Web', 'argpd' ); ?></h2>
				<!--<p>
					En este apartado configurás datos de tu sitio web.
				</p>-->
			</div>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="dominio">
								<?php
									/* translators: en el contexto: Dirección web del sitio */
									printf(
										'%s<br>%s',
										esc_html__( 'Dirección web', 'argpd' ),
										esc_html__( 'del sitio', 'argpd' )
									);
								?>
							</label>			
						</th>
						<td>
							<input 	type="text" 
									name="dominio" 
									value="<?php echo esc_attr( $settings->get_setting( 'dominio' ) ); ?>" 
									/>							
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="finalidad"><?php esc_html_e( 'Finalidad', 'argpd' ); ?></label>
						</th>
						<td>
							<textarea 
								name="finalidad" 
								id="finalidad" 
								cols="40"
								rows="3"
								><?php echo esc_html( $settings->get_setting( 'finalidad' ) ); ?></textarea>
							<!--<p class="description">
								Añade una descripción de la finalidad de este sitio web. 
							</p>-->
							<p class="description">
								<?php esc_html_e( 'Por ejemplo, tienda de venta de zapatos.', 'argpd' ); ?>
							</p>
							<br/>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="hosting-info">								
							<?php
								printf(
									'%s<br>%s',
									esc_html__( 'Proveedor del', 'argpd' ),
									esc_html__( 'alojamiento web', 'argpd' )
								);
							?>
							</label>
						</th>
						<td>
							<input 	type="text" 
									name="hosting-info" 
									value="<?php echo esc_attr( $settings->get_setting( 'hosting-info' ) ); ?>" 
									/>
							<!--<p class="description">
								Indica tu proveedor de alojamiento o hosting y un enlace a su política de privacidad.
							</p>-->				
						</td>
					</tr>					
					<tr>
						<th scope="row">
							<label for="pais"><?php esc_html_e( 'Servicios de Terceros', 'argpd' ); ?></label>
							<p class="description">
								<?php esc_html_e( 'Marca los servicios que utilices', 'argpd' ); ?>
							</p>
						</th>
						<td>
							<fieldset>
								<label  for="thirdparty-dclick">
									<input 	name="thirdparty-dclick" 
											type="checkbox" 
											id="thirdparty-dclick" 
											value="1"
											<?php ( $settings->get_setting( 'thirdparty-dclick' ) == 1 ) && printf( 'checked' ); ?>
											>											
											DoubleClick
								</label>
								<br/>

								<label for="thirdparty-ganalytics">
									<input 	name="thirdparty-ganalytics" 
											type="checkbox" 
											id="thirdparty-ganalytics" 
											value="1"
											<?php ( $settings->get_setting( 'thirdparty-ganalytics' ) == 1 ) && printf( 'checked' ); ?>
											>											
											Google Analytics
								</label>
								<br/>

								<?php /* thidparte social */ ?>
								<label for="thirdparty-social">
									<input 	name="thirdparty-social" 
											type="checkbox" 
											id="thirdparty-social" 
											value="1" 
											<?php ( $settings->get_setting( 'thirdparty-social' ) == 1 ) && printf( 'checked' ); ?>
											>
											<?php esc_html_e( 'Redes Sociales', 'argpd' ); ?>
								</label>
								<br/>

								<?php /* thirdparty mailchimp */ ?>
								<label for="thirdparty-mailchimp">
									<input 	name="thirdparty-mailchimp" 
											type="checkbox" 
											id="thirdparty-mailchimp" 
											value="1" 
											<?php ( $settings->get_setting( 'thirdparty-mailchimp' ) == 1 ) && printf( 'checked' ); ?>
											>
											MailChimp
								</label>
								<br/>

								<?php /* thirdparty mailrelay */ ?>
								<label for="thirdparty-mailrelay">
									<input 	name="thirdparty-mailrelay" 
											type="checkbox" 
											id="thirdparty-mailrelay" 
											value="1" 
											<?php ( $settings->get_setting( 'thirdparty-mailrelay' ) == 1 ) && printf( 'checked' ); ?>
											>
											MailRelay											
								</label>							
								<br/>

								<?php /* thirdparty sendinblue */ ?>
								<label for="thirdparty-sendinblue">
									<input 	name="thirdparty-sendinblue"
											type="checkbox" 
											id="thirdparty-sendinblue"
											value="1" 
											<?php ( $settings->get_setting( 'thirdparty-sendinblue' ) == 1 ) && printf( 'checked' ); ?>
											>
											SendinBlue
								</label>							
								<br/>								

								<?php /* amazon afiliados */ ?>
								<label for="thirdparty-amazon">
									<input 	name="thirdparty-amazon" 
											type="checkbox" 
											id="thirdparty-amazon" 
											value="1" 
											<?php ( $settings->get_setting( 'thirdparty-amazon' ) == 1 ) && printf( 'checked' ); ?>
											>
											<?php esc_html_e( 'Programa de Afiliados de Amazon de la UE', 'argpd' ); ?>
								</label>							
								<br/>

							<?php
							/* thirdparty links
							<label for="thirdparty-links">
							<input  name="thirdparty-links"
									type="checkbox"
									id="thirdparty-links"
									value="1"
									<?php ($settings->get_setting('thirdparty-links') == 1) && printf("checked"); ?>
									>
									<?php _e('Enlaces o imágenes de otros sitios', 'argpd');?>
							</label>
							<p class="description">
								Marca si utilizas imágenes o pones enlaces a otros sitios.
							</p>
							<br/> */
							?>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>
			<?php submit_button(); ?>

			<?php /* Más ajustes */ ?>
			<br>
			<div>
				<h2 class="title"><?php esc_html_e( 'Más Ajustes', 'argpd' ); ?></h2>
			</div>
			<table class="form-table">
				<tbody>	
					<tr>
						<th scope="row">
							<label for="pais"><?php esc_html_e( 'Cláusulas', 'argpd' ); ?></label>
							<p class="description">
								<?php esc_html_e( 'Marca para activar', 'argpd' ); ?>
							</p>
						</th>
						<td>
							<fieldset>
							<label for="clause-exclusion">
							<input 	name="clause-exclusion" 
									type="checkbox" 
									id="clause-exclusion" 
									value="1"
									<?php ( $settings->get_setting( 'clause-exclusion' ) == 1 ) && printf( 'checked' ); ?>
									>
									<?php esc_html_e( 'Reservar el Derecho de exclusión', 'argpd' ); ?>
							</label>
							<!--<p class="description">
								Añadir una cláusula para reservar el Derecho de exclusión.
							</p>-->					
							<br/>
							<label for="clause-terceros">
							<input 	name="clause-terceros" 
									type="checkbox" 
									id="clause-terceros" 
									value="1" 
									<?php ( $settings->get_setting( 'clause-terceros' ) == 1 ) && printf( 'checked' ); ?>
									>
									<?php esc_html_e( 'Cesión de datos a terceros', 'argpd' ); ?>
							</label>
							<!--<p class="description">
								Añádir una cláusula para permitir la cesión a terceros.
							</p>-->
							<br/>
							
							<?php /* clausula mayoría edad */ ?>
							<label for="clause-edad">
							<input 	name="clause-edad" 
									type="checkbox" 
									id="clause-edad" 
									value="1" 
									<?php ( $settings->get_setting( 'clause-edad' ) == 1 ) && printf( 'checked' ); ?>
									>
									<?php esc_html_e( 'Requisito mayoría edad', 'argpd' ); ?>
							</label>
							<!--<p class="description">
								Añádir una cláusula para requerir una edad mínima.
							</p>
							<p class="description">
								Marca esta opción si página web esta destianada sólo a mayores de edad.
							</p>-->							
							<br/>

							<?php /* clausula protected data */ ?>
							<label for="clause-protegidos">
							<input 	name="clause-protegidos" 
									type="checkbox" 
									id="clause-protegidos" 
									value="1" 
									<?php ( $settings->get_setting( 'clause-protegidos' ) == 1 ) && printf( 'checked' ); ?>
									>
									<?php esc_html_e( 'Datos especialmente protegidos: médicos, religiosos, orientación sexual...', 'argpd' ); ?>
							</label>
							<!--<p class="description">
								Marcar si se recogen datos especialmento protegidos (médicos, religión, orientación sexual,...)
							</p>-->
							<br/>

							<?php /* clausula portabilidad  */ ?>
							<label for="clause-portabilidad">
							<input 	name="clause-portabilidad" 
									type="checkbox" 
									id="clause-portabilidad" 
									value="1" 
									<?php ( $settings->get_setting( 'clause-portabilidad' ) == 1 ) && printf( 'checked' ); ?>
									>
									<?php esc_html_e( 'Permitir la Portabilidad de datos', 'argpd' ); ?>
							</label>
							<!--<p class="description">
								Marcar para permitir la portabilidad de datos.
							</p>-->
							<br/>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>


			<?php submit_button(); ?>
		</form>
		<?php
	}


	/**
	 * Echo 'Integracion' tab of plugin settings
	 *
	 * @since  0.0.0
	 */
	public function argpd_paginas_tab() {
		global $argpd_active_tab;
		$classname = ( ! empty( $argpd_active_tab ) && 'paginas' == $argpd_active_tab ) ? 'nav-tab-active' : '';
		?>
		<a 	class="nav-tab <?php echo esc_attr( $classname ); ?>" 
			href="<?php echo esc_url( admin_url( 'admin.php?page=argpd&tab=paginas' ) ); ?>">
			<?php esc_html_e( 'Integración', 'argpd' ); ?> 
		</a>
		<?php
	}


	/**
	 * Echo 'Integracion' content of plugin settings
	 *
	 * @since  0.0.0
	 */
	public function argpd_paginas_content() {
		global $argpd_active_tab;
		if ( empty( $argpd_active_tab ) || 'paginas' != $argpd_active_tab ) {
			return;
		}

		$settings = $this->plugin->argpd_settings;
		?>
 
		<br/>
		<div>
			<h2 class="title"><?php esc_html_e( 'Páginas Legales', 'argpd' ); ?></h2>
			<!--<p>
				El plugin ha creado las páginas legales al activarse. 
			</p>-->
			<p>
				<?php esc_html_e( 'Activa o desactiva cada texto legal y escoge en que página aparece.', 'argpd' ); ?>
			</p>
		</div>

		<form method="post" action="admin-post.php" style="padding-top: 20px">
			<div>

				<?php wp_nonce_field( 'argpd' ); ?>
				<input type="hidden" value="argpd_pages_setup" name="action"/>
	
				<table class="wp-list-table widefat fixed striped posts">
					<thead>
						<tr>
							<td><?php esc_html_e( 'Texto Legal', 'argpd' ); ?></td>
							<td><?php esc_html_e( 'Página', 'argpd' ); ?></td>
							<!--<td>Código Abreviado</td>-->
							<td><?php esc_html_e( 'Ayuda', 'argpd' ); ?></td>
						</tr>
					</thead>
					<tbody>

						<?php /* Aviso Legal */ ?>
						<tr>
							<th scope="row">

								<?php
									$checked = ( $settings->get_setting( 'avisolegal-disabled' ) == 0 ) ? ( 'checked' ) : '';
								?>
								<label class="argpd-switch">
								  <input type="checkbox" name="avisolegal-enabled" <?php echo esc_attr( $checked ); ?>>
								  <span class="argpd-slider argpd-round"></span>
								</label>

								<label for="avisolegal"><?php esc_html_e( 'Aviso Legal', 'argpd' ); ?></label>
								<?php if ( $checked && $settings->get_setting( 'avisolegalID' ) != 0 ) { ?>								
								<div class="row-actions">
									<span class="view argpd-view">
										<?php
										printf(
											'<a href="%s">%s</a>',
											esc_attr( $settings->get_setting( 'avisolegalURL' ) ),
											esc_html__( 'Ver', 'argpd' )
										);
										?>
									</span>
								</div>
								<?php } ?> 
							</th>
							<td>
								<select name="avisolegal" id="avisolegal">
									<option value="0"
											<?php
											if ( $settings->get_setting( 'avisolegalID' ) == 0 ) {
												printf( 'selected="selected"' );}
											?>
											>
											Ninguna</option>
									<?php
									foreach ( get_pages() as $page ) {
										$selected = ( $page->ID == $settings->get_setting( 'avisolegalID' ) ) ? ( 'selected="selected"' ) : '';
										printf( '<option value="%s" %s>%s</option>', esc_attr( $page->ID ), esc_attr( $selected ), esc_html( $page->post_title ) );
									}
									?>
								</select>

								<?php
								$match = false;
								foreach ( get_pages() as $page ) {
									if ( $page->ID == $settings->get_setting( 'avisolegalID' ) ) {
										$match = true;
									}
								}
								if ( ! $match ) {
									?>
								<p class="description">
									Selecciona una página o <a id="crear-pagina-legal" class="crear-pagina" style="cursor:pointer">créala</a>.
								</p>
								<?php } ?>
							</td>
							<!--<td>
								<span class="shortcode">[argpd_aviso-legal]</span>
							</td>-->
							<td></td>
						</tr>

						<?php /* Política de privacidad */ ?>
						<tr>
							<th scope="row">
								<?php
									$checked = ( $settings->get_setting( 'privacidad-disabled' ) == 0 ) ? ( 'checked' ) : '';
								?>
								<label class="argpd-switch">
								  <input type="checkbox" name="privacidad-enabled" <?php echo esc_attr( $checked ); ?>>
								  <span class="argpd-slider argpd-round"></span>
								</label>

								<label for="privacidad"><?php esc_html_e( 'Política de Privacidad', 'argpd' ); ?></label>
								<?php if ( $checked && $settings->get_setting( 'privacidadID' ) != 0 ) { ?>
								<div class="row-actions">
									<span class="view argpd-view">
										<?php
										printf(
											'<a href="%s">%s</a>',
											esc_attr( $settings->get_setting( 'privacidadURL' ) ),
											esc_html__( 'Ver', 'argpd' )
										);
										?>
									</span>				
								</div>
								<?php } ?> 
							</th>
							<td>
								<select name="privacidad" id="privacidad">
									<option value="0"
											<?php
											if ( $settings->get_setting( 'privacidadID' ) == 0 ) {
												printf( 'selected="selected"' );}
											?>
											>
											Ninguna</option>
									<?php
									foreach ( get_pages() as $page ) {
										$selected = ( $page->ID == $settings->get_setting( 'privacidadID' ) ) ? ( 'selected="selected"' ) : '';
										printf( '<option value="%s" %s>%s</option>', esc_attr( $page->ID ), esc_attr( $selected ), esc_html( $page->post_title ) );
									}
									?>
								</select>
								<?php
								$match = false;
								foreach ( get_pages() as $page ) {
									if ( $page->ID == $settings->get_setting( 'privacidadID' ) ) {
										$match = true;
									}
								}
								if ( ! $match ) {
									?>
								<p class="description">
									Selecciona una página o <a id="crear-pagina-privacidad" class="crear-pagina" style="cursor:pointer">créala</a>.
								</p>
								<?php } ?>

							</td>
							<!--<td>
								<span class="shortcode">[argpd_politica-privacidad]</span>
							</td>-->
							<td>
								<a 	href="https://superadmin.es/blog/wordpress/crear-politica-de-privacidad/?utm_source=wordpressorg&utm_campaign=adapta_rgpd&utm_medium=direct" 
									class="button" 
									target="_blank"
									style="background-color: #03A9F4;color: white;border-color: #03A9F4;"
								>Aprende a crear la Política de Privacidad</a>
							</td>
						</tr>

						<?php /* Política de cookies */ ?>
						<tr>
							<th scope="row">								
								<?php
									$checked = ( $settings->get_setting( 'cookies-disabled' ) == 0 ) ? ( 'checked' ) : '';
								?>
								<label class="argpd-switch">
								  <input type="checkbox" name="cookies-enabled" <?php echo esc_attr( $checked ); ?>>
								  <span class="argpd-slider argpd-round"></span>
								</label>

								<label for="cookies"><?php esc_html_e( 'Política de Cookies', 'argpd' ); ?></label>

								<?php if ( $checked && $settings->get_setting( 'cookiesID' ) != 0 ) { ?>
								<div class="row-actions">
									<span class="view argpd-view">
										<?php
										printf(
											'<a href="%s">%s</a>',
											esc_attr( $settings->get_setting( 'cookiesURL' ) ),
											esc_html__( 'Ver', 'argpd' )
										);
										?>
									</span>
								</div>
								<?php } ?> 
							</th>
							<td>
								<select name="cookies" id="cookies">
									<option value="0"
											<?php
											if ( $settings->get_setting( 'cookiesID' ) == 0 ) {
												printf( 'selected="selected"' );}
											?>
											>
											Ninguna</option>
									<?php
									foreach ( get_pages() as $page ) {
										$selected = ( $page->ID == $settings->get_setting( 'cookiesID' ) ) ? ( 'selected="selected"' ) : '';
										printf( '<option value="%s" %s>%s</option>', esc_attr( $page->ID ), esc_attr( $selected ), esc_html( $page->post_title ) );
									}
									?>
								</select>
								<?php
								$match = false;
								foreach ( get_pages() as $page ) {
									if ( $page->ID == $settings->get_setting( 'cookiesID' ) ) {
										$match = true;
									}
								}
								if ( ! $match ) {
									?>
									<p class="description">
										Selecciona una página o <a id="crear-pagina-cookies" class="crear-pagina" style="cursor:pointer">créala</a>
									</p>								
								<?php } ?>
							</td>
							<!--<td>
								<span class="shortcode">[argpd_politica-cookies]</span>
							</td>-->
							<td>
								<a 	href="https://superadmin.es/blog/wordpress/crear-banner-de-cookies/?utm_source=wordpressorg&utm_campaign=adapta_rgpd&utm_medium=direct" 
									class="button" 
									target="_blank"
									style="background-color: #03A9F4;color: white;border-color: #03A9F4;"
								>Aprende a cumplir la Ley de Cookies</a>
							</td>
						</tr>												
						<?php /* Información Básica */ ?>
						<!--<tr>
							<th scope="row">
								<label><span style="padding-left:40px"><?php esc_html_e( 'Deber de Informar', 'argpd' ); ?></span></label>								
								<div class="row-actions">
								</div>
							</th>
							<td>								
							</td>							
							<td>
								<span class="shortcode">[argpd_deber_de_informar]</span>
							</td>
							<td>
								<a 	href="https://superadmin.es/blog/emprendedor/cumplir-deber-de-informar-rgpd/" 
									class="button" 
									target="_blank"
									style="background-color: #03A9F4;color: white;border-color: #03A9F4;"
								>Cumplir el deber de informar</a>
							</td>
						</tr>-->			
					</tbody>
				</table>

	
				<table class="form-table">
					<tr>
						<td>
							<fieldset>
								<br/>
								<label  for="option-formal">
									<?php
										$checked = ( $settings->get_setting( 'option-formal' ) == 1 ) ? ( 'checked' ) : '';
									?>
									<input 	name="option-formal" 
											type="checkbox" 
											id="option-formal" 
											value="1"							
											<?php echo esc_attr( $checked ); ?>						
										>
									<?php esc_html_e( 'Tratamiento de usted', 'argpd' ); ?>
								</label>
								<p class="description">
									<?php esc_html_e( 'Por defecto los textos aparecen escritos de forma familiar o de tú.', 'argpd' ); ?>					
								</p>

								<br/>
								<label  for="robots-index">
									<?php
										$checked = ( $settings->get_setting( 'robots-index' ) == 1 ) ? ( 'checked' ) : '';
									?>
									<input 	name="robots-index" 
											type="checkbox" 
											id="robots-index" 
											value="1"							
											<?php echo esc_attr( $checked ); ?>						
										>
									<?php esc_html_e( 'Las páginas legales aparecen en buscadores (Google, Bing...)', 'argpd' ); ?>
									<a 	href="https://superadmin.es/adapta-rgpd/nofollow-politica-privacidad/?utm_source=wordpressorg&utm_campaign=adapta_rgpd&utm_medium=direct" 
										target="_blank"
										rel="nofollow"
										>
										<span style="text-decoration: none" class="dashicons dashicons-editor-help"></span>
										Indexar o no indexar
									</a>				
								</label>
								<p class="description">
									<?php esc_html_e( 'No recomendado.', 'argpd' ); ?>			
								</p>
							</fieldset>
						</td>
					</tr>
				</table>


				<?php submit_button(); ?>				
			</div>

			<br><br>
			<div>
				<h2 class="title"><?php esc_html_e( 'Consentimiento y Deber de Informar', 'argpd' ); ?></h2>
				<p>
					<?php esc_html_e( 'Este apartado sirve para cumplir el deber de informar. Aprende los conceptos que necesitas en', 'argpd' ); ?> 
					<a href="https://superadmin.es/blog/emprendedor/cumplir-deber-de-informar-rgpd/?utm_source=wordpressorg&utm_campaign=adapta_rgpd&utm_medium=direct" 
							 target="_blank"
							 >							
							<?php esc_html_e( 'esta guía.', 'argpd' ); ?>
					</a>
				</p>
			</div>
			
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label><?php esc_html_e( 'Activar en', 'argpd' ); ?></label>
						</th>
						<td>
							<fieldset>
							<label  for="option-comments">
								<input 	name="option-comments" 
									type="checkbox" 
									id="option-comments" 
									value="1"							
									<?php ( $settings->get_setting( 'option-comments' ) == 1 ) && printf( 'checked' ); ?>
									>
									<?php esc_html_e( 'Comentarios ', 'argpd' ); ?>
							</label>
							<p class="description">								
								<?php esc_html_e( 'Añade una casilla para obtener el consentimiento y mostrar la primera capa informativa.', 'argpd' ); ?>
							</p>													
							<br/>

							<label  for="option-forms">
								<input 	name="option-forms" 
										type="checkbox" 
										id="option-forms" 
										value="1"							
										<?php ( $settings->get_setting( 'option-forms' ) == 1 ) && printf( 'checked' ); ?>
										>
										<?php esc_html_e( 'Formularios', 'argpd' ); ?>
							</label>
							<p class="description">
								Marca si hay formularios, luego añade el shortcode [argpd_deber_de_informar] debajo <br>
								de cada formulario para mostrar la primera capa informativa.
								<a href="https://superadmin.es/blog/emprendedor/adecuar-formulario-al-rgpd/?utm_source=wordpressorg&utm_campaign=adapta_rgpd&utm_medium=direct" 
											target="_blank"><span style="text-decoration: none" class="dashicons dashicons-editor-help"></span>
											<?php esc_html_e( 'Guía para adecuar los formularios', 'argpd' ); ?>
										</a>
							</p>
							<br/>

							<label  for="option-footer">
								<input 	name="option-footer" 
										type="checkbox" 
										id="option-footer" 
										value="1"							
										<?php ( $settings->get_setting( 'option-footer' ) == 1 ) && printf( 'checked' ); ?>
										>
										<?php esc_html_e( 'Pie de página', 'argpd' ); ?>
										
										<!--<a href="https://superadmin.es/adapta-rgpd/pie-de-pagina-ley-proteccion-de-datos/" target="_blank">
											<span style="text-decoration: none;" class="dashicons dashicons-editor-help"></span>
											Añadir más enlaces
										</a>-->										
							</label>
							<p class="description">
								<?php esc_html_e( 'Crea un pie de página con enlaces a los textos legales.', 'argpd' ); ?>
								<a href="https://superadmin.es/adapta-rgpd/pie-de-pagina-ley-proteccion-de-datos/?utm_source=wordpressorg&utm_campaign=adapta_rgpd&utm_medium=direct" target="_blank">
									<span style="text-decoration: none;" class="dashicons dashicons-editor-help"></span>
									<?php esc_html_e( 'Añade más enlaces', 'argpd' ); ?>
								</a>
							</p>							
							</fieldset>
						</td>
					</tr>
					
					<?php /* Texto consentimiento */ ?>
					<tr>
						<th scope="row">
							<label for="consentimiento-label"><?php esc_html_e( 'Texto para el consentimiento', 'argpd' ); ?></label>
						</th>
						<td>
								<textarea 
									name="consentimiento-label" 
									id="consentimiento-label" 
									cols="60"
									rows="3"
									placeholder="He leído y acepto la política de privacidad."
									><?php echo esc_html( $settings->get_setting( 'consentimiento-label' ) ); ?></textarea>						
									<br><span class="description">
										<?php esc_html_e( 'Para mostrar el texto por defecto deja en blanco', 'argpd' ); ?>	
									</span>
						</td>
					</tr>

					<?php /* Texto consentimiento */ ?>
					<tr>
						<th scope="row">
							<label for="consentimiento-label">
								<?php
									printf(
										'%s<br>%s',
										esc_html__( 'Código para la', 'argpd' ),
										esc_html__( 'capa informativa:', 'argpd' )
									);
								?>
							</label>
						</th>
						<td>								
							<p>
								<?php
									printf(
										'%s<br>%s',
										esc_html__( 'Pega este código en tus formularios para que', 'argpd' ),
										esc_html__( 'aparezca la primera capa informativa.', 'argpd' )
									);
								?>
							</p>
							<br>
							<p>
								[argpd_deber_de_informar finalidad="Responder tus consultas."]
							</p>
						</td>
					</tr>					
				</tbody>
			</table>
			<?php submit_button(); ?>


			<br><br>
			<div>
				<h2 class="title"><?php esc_html_e( 'Ley de Cookies', 'argpd' ); ?></h2>			
				<p>
					<?php esc_html_e( 'Este apartado sirve para cumplir la ley de Cookies.', 'argpd'); ?>
					<a 	href="https://superadmin.es/blog/wordpress/crear-banner-de-cookies/?utm_source=wordpressorg&utm_campaign=adapta_rgpd&utm_medium=direct" 
						target="_blank"
						>
						Aprende más aquí.
					</a>
				</p>
				<div>					
					
						<table class="form-table">
							<tbody>	
								<?php /* Activar/Desactivar cookies */ ?>
								<tr>
									<th scope="row">
										<label for="option-cookies"><?php esc_html_e( 'Activar', 'argpd' ); ?></label>
									</th>
									<td>
										<label  for="option-cookies">
										<input 	name="option-cookies" 
												type="checkbox" 
												id="option-cookies" 
												value="1"							
												<?php ( $settings->get_setting( 'option-cookies' ) == 1 ) && printf( 'checked' ); ?>
												>
												<?php esc_html_e( 'Banner de Cookies ', 'argpd' ); ?>
										</label>
									</td>
								</tr>								
								<?php /* Texto */ ?>
								<tr>
									<th scope="row">
										<label for="cookies-label"><?php esc_html_e( 'Texto en el banner', 'argpd' ); ?></label>
									</th>
									<td>								
										<textarea 
											name="cookies-label" 
											id="cookies-label" 
											cols="60"
											rows="3"
											placeholder="Esta web utiliza cookies. Puedes ver aquí la Política de Cookies. Si continuas navegando estás aceptándola."
											><?php echo esc_html( $settings->get_setting( 'cookies-label' ) ); ?></textarea>
											<br>
											<span class="description">
												<?php esc_html_e( 'Para mostrar el texto por defecto deja en blanco', 'argpd' ); ?>										
											</span>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="cookies-btnlabel"><?php esc_html_e( 'Texto del bot&oacute;n Aceptar', 'argpd' ); ?></label>
									</th>
									<td>																		
										<label  for="cookies-btnlabel">
											<input 	name="cookies-btnlabel" 
													id="cookies-btnlabel" 
													value="<?php echo esc_attr( $settings->get_setting( 'cookies-btnlabel' ) ); ?>"
													>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="cookies-linklabel"><?php esc_html_e( 'Texto del enlace Ver', 'argpd' ); ?></label>
									</th>
									<td>																		
										<label  for="cookies-linklabel">
											<input 	name="cookies-linklabel" 
													id="cookies-linklabel" 
													value="<?php echo esc_attr( $settings->get_setting( 'cookies-linklabel' ) ); ?>"
													>
										</label>
									</td>
								</tr>

								<?php /* show setting if not cookiesID  */ ?>
								<?php if ( $settings->get_setting( 'cookiesID' ) == 0 ) : ?>
								<tr>
									<th scope="row">
										<label for="cookies"><?php esc_html_e( 'Enlace Ver', 'argpd' ); ?></label>
									</th>
									
									<td>
										<select name="cookies-url" id="cookies-url">
											<option value="0"
													<?php
													if ( ! strlen( $settings->get_setting( 'cookiesURL' ) == 0 ) ) {
														printf( 'selected="selected"' );}
													?>
													>
													Ninguna</option>
											
											<?php
											foreach ( get_pages() as $page ) {
												$permalink = get_permalink( $page->ID );
												$selected = ( $permalink == $settings->get_setting( 'cookiesURL' ) ) ? ( 'selected="selected"' ) : '';
												printf( '<option value="%s" %s>%s</option>', esc_attr( $permalink ), esc_attr( $selected ), esc_html( $page->post_title ) );
											}
											?>
										</select>
										<p>
											<span class="description">
												Selecciona la página a la que apunta el enlace "Ver".
											</span>
										</p>
									</td>
								</tr>
								<?php endif; ?>	

							</tbody>
						</table>
					
				</div>
			</div>			
			<?php submit_button(); ?>

			<br><br>
			<div>
				<h2 class="title"><?php esc_html_e( 'Estilo y diseño', 'argpd' ); ?></h2>
				<p>
					Para personalizar el diseño del banner de cookies y de la primera capa informativa.
				</p>		
				<div>					
					
						<table class="form-table">
							<tbody>	
								<?php /* Theme */ ?>
								<tr>
									<th scope="row">
										<label for="cookies-theme"><?php esc_html_e( 'Tema', 'argpd' ); ?></label>
									</th>
									<td>

									<select name="cookies-theme" id="cookies-theme">
										<?php
											$cookie_themes = $settings->get_cookie_themes();
										foreach ( $cookie_themes as $key => $value ) {
											$selected = ( $key == $settings->get_setting( 'cookies-theme' ) ) ? ( 'selected="selected"' ) : '';
											printf( '<option value="%s" %s>%s</option>', esc_attr( $key ), esc_attr( $selected ), esc_html( $value ) );
										}
										?>
									</select>
									
									<a 	href="https://superadmin.es/adapta-rgpd/personalizar-banner-cookies/?utm_source=wordpressorg&utm_campaign=adapta_rgpd&utm_medium=direct" 
										 target="_blank"
										 >
										<span style="text-decoration: none" class="dashicons dashicons-editor-help"></span>
											Cómo personalizar con CSS
									</a>

									</td>
								</tr>	

								<?php /* list style */ ?>
								<tr>
									<th scope="row">
										<label><?php esc_html_e( 'Listas', 'argpd' ); ?></label>
									</th>
									<td>
										<select name="informbox-theme" id="informbox-theme">
										<?php
											$informbox_themes = $settings->get_informbox_themes();
										foreach ( $informbox_themes as $key => $value ) {
											$selected = ( $key == $settings->get_setting( 'informbox-theme' ) ) ? ( 'selected="selected"' ) : '';
											printf( '<option value="%s" %s>%s</option>', esc_attr( $key ), esc_attr( $selected ), esc_html( $value ) );
										}
										?>
										</select>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<?php submit_button(); ?>
		</form>
		
		<?php
	}


	/**
	 * Echo 'Ayuda' tab of plugin settings
	 *
	 * @since  0.0.0
	 */
	public function argpd_ayuda_tab() {
		global $argpd_active_tab;
		$classname = ( ! empty( $argpd_active_tab ) && 'ayuda' == $argpd_active_tab ) ? 'nav-tab-active' : '';
		?>
		<a 	class="nav-tab <?php echo esc_attr( $classname ); ?>" 
			href="<?php echo esc_url( admin_url( 'admin.php?page=argpd&tab=ayuda' ) ); ?>">
			<?php esc_html_e( 'Ayuda', 'argpd' ); ?> 
		</a>
		<?php
	}


	/**
	 * Echo 'Ayuda' content of plugin settings
	 *
	 * @since  0.0.0
	 */
	public function argpd_ayuda_content() {
		global $argpd_active_tab;
		if ( empty( $argpd_active_tab ) || 'ayuda' != $argpd_active_tab ) {
			return;
		}
		?>
 
			<div>
				<h2 class="title"><?php esc_html_e( 'Ayuda', 'argpd' ); ?></h2>
				
				<?php
					echo $this->plugin->pages->ayuda_view();

					echo $this->plugin->pages->disclaimer();
				?>

			</div>		
		<?php
	}


	/**
	 * Echo plugin settings view
	 *
	 * @since  0.0.0
	 */
	public function options_ui() {

		global $argpd_active_tab;
		$argpd_active_tab = ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'ajustes';
		?>
		
		<?php /* ARGPD messages */ ?>
		<?php

			$message       = __( 'Algo fue mal.', 'argpd' );
			$message_class = 'notice-success';

		if ( isset( $_GET['message'] ) ) {
			switch ( $_GET['message'] ) {
				case 'saved':
					$message = __( 'Los cambios se han guardado.', 'argpd' );
					break;
				default:
					$message       = __( 'La página ya existe.', 'argpd' );
					$message_class = 'notice-error';
					break;
			}
			?>
				<div id="message" 
					 class="notice <?php echo esc_attr( $message_class ); ?> is-dismissible"
					 >
					 <p><?php echo esc_html( $message ); ?></p>
					 <button type="button" 
							  class="notice-dismiss"
							  >
							  <span class="screen-reader-text">
								  Descartar este aviso.
							  </span>
					</button>
				</div>
		<?php } ?>
		
		
		<div class="wrap">		
			<h1>Cumple con la RGPD</h1>

			<?php
				$settings = $this->plugin->argpd_settings;
			if ( $settings->get_setting( 'renuncia' ) == 0 ) {
				?>
				<div>
					<div>
						<?php
						echo $this->plugin->pages->disclaimer();
						?>

						<form method="post" action="admin-post.php">
						<?php wp_nonce_field( 'argpd' ); ?>
							<input type="hidden" value="argpd_disclaimer" name="action"/>

							<p class="submit">
								<input 	type="submit" 
										name="submit" 
										id="submit" 
										class="button button-primary" 
										value="Aceptar">
							</p>
						</form>
					</div>
				</div>
				

			<?php } else { ?>

			<div>
				<h2 class="nav-tab-wrapper">
					<?php
					// echo tabs by tab param.
					do_action( 'argpd_settings_tab' );
					?>
				</h2>
		
				<?php
					// echo content by tab param.
					do_action( 'argpd_settings_content' );
				?>
			</div>
			<?php } ?>
			<hr>
			<p>				
				Tu valoración de <a href="https://wordpress.org/support/plugin/adapta-rgpd/reviews?rate=5#new-post" target="_blank">★★★★★</a>&nbsp;&nbsp; ayuda a mejorar el plugin. ¡Muchas gracias!
			</p>
		</div>
		<?php
	}
}


