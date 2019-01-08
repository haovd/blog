<?php
/*
* Wetterwarner Admin Einstellungen
* Author: Tim Knigge
* https://tim.knigge-ronnenberg.de/projekte/wetterwarner/dokumentation/
*/ 
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
add_action( 'admin_menu', 'wetterwarner_add_admin_menu' );
add_action( 'admin_init', 'wetterwarner_settings_init' );
add_action( 'admin_enqueue_scripts', 'wetterwarner_admin_scripts' );
add_action( 'admin_init', 'wetterwarner_check_konfig' );

function wetterwarner_add_admin_menu(  ) { 
	add_options_page( 'Wetterwarner Einstellungen', 'Wetterwarner', 'manage_options', 'wetterwarner', 'wetterwarner_options_page' );
}
function wetterwarner_settings_init(  ) { 
	
	register_setting( 'pluginPage', 'wetterwarner_settings');
	
	add_settings_section(
		'wetterwarner_pluginPage_section', 
		__( 'Wetterwarner Einstellungen', 'wordpress' ), 
		'wetterwarner_settings_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'ww_cache', 
		__( 'Cache aktivieren (empfohlen)', 'wordpress' ), 
		'ww_cache_render', 
		'pluginPage', 
		'wetterwarner_pluginPage_section'
	);
	add_settings_field( 
		'ww_debug', 
		__( 'Debug Modus aktivieren', 'wordpress' ), 
		'ww_debug_render', 
		'pluginPage', 
		'wetterwarner_pluginPage_section' 
	);
    add_settings_field( 
	'ww_farbe_stufe1', 
	__( 'Hintergrundfarbe Stufe 1', 'wordpress' ), 
	'ww_farbe_stufe1_field',
	'pluginPage', 
	'wetterwarner_pluginPage_section'
	);
	    add_settings_field( 
	'ww_farbe_stufe2', 
	__( 'Hintergrundfarbe Stufe 2', 'wordpress' ), 
	'ww_farbe_stufe2_field',
	'pluginPage', 
	'wetterwarner_pluginPage_section'
	);
	    add_settings_field( 
	'ww_farbe_stufe3', 
	__( 'Hintergrundfarbe Stufe 3', 'wordpress' ), 
	'ww_farbe_stufe3_field',
	'pluginPage', 
	'wetterwarner_pluginPage_section'
	);
	    add_settings_field( 
	'ww_farbe_stufe4', 
	__( 'Hintergrundfarbe Stufe 4', 'wordpress' ), 
	'ww_farbe_stufe4_field',
	'pluginPage', 
	'wetterwarner_pluginPage_section'
	);
}
function wetterwarner_admin_scripts(  ) { 
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker-alpha', plugins_url( '/js/wp-color-picker-alpha.js',  __FILE__ ), array( 'wp-color-picker' ), '1.2.2', true );
}
function ww_cache_render(  ) { 
	$options = get_option( 'wetterwarner_settings' );
	if( !isset( $options['ww_cache'] ) ) $options['ww_cache'] = false;
	?>
    <input type='checkbox' name='wetterwarner_settings[ww_cache]' <?php checked( $options['ww_cache'], 1 ); ?> value='1'>
	<?php
}
function ww_debug_render(  ) {
	$options = get_option( 'wetterwarner_settings' );
	if( !isset( $options['ww_debug'] ) ) $options['ww_debug'] = false;
	?>
	<input type='checkbox' name='wetterwarner_settings[ww_debug]' <?php checked( $options['ww_debug'], 1 ); ?> value='1'>
	<?php
}
function ww_farbe_stufe1_field( ) {
	$options = get_option( 'wetterwarner_settings' );
	if( !isset( $options['ww_farbe_stufe1'] ) ) $options['ww_farbe_stufe1'] = 'rgba(255,255,,0.2)';
    echo '<input type="text" class="color-picker" name="wetterwarner_settings[ww_farbe_stufe1]" data-alpha="true" value="' .$options['ww_farbe_stufe1']. '" class="cpa-color-picker" >';
}
function ww_farbe_stufe2_field( ) {
	$options = get_option( 'wetterwarner_settings' );
	if( !isset( $options['ww_farbe_stufe2'] ) ) $options['ww_farbe_stufe2'] = 'rgba(255,125,0,0.2)';
    echo '<input type="text" class="color-picker" name="wetterwarner_settings[ww_farbe_stufe2]" data-alpha="true" value="' .$options['ww_farbe_stufe2']. '" class="cpa-color-picker" >';
}
function ww_farbe_stufe3_field( ) {
	$options = get_option( 'wetterwarner_settings' );
	if( !isset( $options['ww_farbe_stufe3'] ) ) $options['ww_farbe_stufe3'] = 'rgba(255,0,0,0.2)';
    echo '<input type="text" class="color-picker" name="wetterwarner_settings[ww_farbe_stufe3]" data-alpha="true" value="' .$options['ww_farbe_stufe3']. '" class="cpa-color-picker" >';
}
function ww_farbe_stufe4_field( ) {
	$options = get_option( 'wetterwarner_settings' );
	if( !isset( $options['ww_farbe_stufe4'] ) ) $options['ww_farbe_stufe4'] = 'rgba(200,0,180,0.2)';
    echo '<input type="text" class="color-picker" name="wetterwarner_settings[ww_farbe_stufe4]" data-alpha="true" value="' .$options['ww_farbe_stufe4']. '" class="cpa-color-picker" >';
}
function wetterwarner_check_folder_permissions( ) {
	$folder = __DIR__ . '/tmp/';
	if (is_writable($folder))
	return true;
	else
	return false;
}
function wetterwarner_check_konfig( ){
	// Check Temp Ordner
	$beschreibbar = wetterwarner_check_folder_permissions();
	if(!$beschreibbar)//if(!is_writable(__DIR__ . "/tmp/"))
	$error = "/tmp/ Ordner nicht beschreibbar, Wetterwarner Cache Funktion kann nicht aktiviert werden!";
	
	if(!function_exists('curl_version'))
	$error = "PHP Erweiterung curl deaktiviert oder nicht installiert , Wetterwarner Cache Funktion kann nicht aktiviert werden!";

	if(isset($error)){
	add_settings_error(
    'wetterwarner_settings',
    'wetterwarner', 
    __($error, 'ww'),
    'error'
		);
	 }
}
function wetterwarner_settings_section( ){
	wetterwarner_admin_notification();
		do_settings_sections( 'pluginPage' );
}
function wetterwarner_settings_footer(  ) { 
	echo __( '<a href="https://tim.knigge-ronnenberg.de/projekte/wetterwarner/dokumentation/" target="_blank">Dokumentation</a> | <a href="http://support.it93.de/open.php" target="_blank">Kontakt</a>', 'wordpress' );
}
function wetterwarner_settings_section_callback(  ) { 
	echo __( 'Nachfolgende Einstellungen sind unabhängig von den Widget Optionen.<br><br>', 'wordpress' );
	echo 'Ich empfehle den Cache zu aktivieren! So muss das Plugin nicht bei jedem Seitenaufruf die externen Inhalte neu laden, <br>sondern kann diese direkt von deinem Webspace laden. Dies verkürzt die Ladezeit für deine Besucher.';
}
function wetterwarner_admin_notification(  ) {
		$notification_content = file('https://tim.knigge-ronnenberg.de/wetterwarner/admin_notification.txt');
		if($notification_content[0] != 0)
		{
			?>
			<div class="notice notice-info is-dismissible">
			<p><?php echo $notification_content[1];?></p>
		</div>
		<?php
		}
}
function wetterwarner_options_page(  ) {
	?>
	<form action='options.php' method='post'>
		<?php
		settings_fields( 'pluginPage' );	
		wetterwarner_settings_section();
		submit_button();
		wetterwarner_settings_footer();
		?>
	</form>
	<?php
}
?>