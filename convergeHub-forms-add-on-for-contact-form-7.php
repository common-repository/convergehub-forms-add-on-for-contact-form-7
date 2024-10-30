<?php
/*
Plugin Name: ConvergeHub For Contact Form 7
Plugin URI: 
Description: This plugin enable ConvergeHub integration with Contact Form 7 forms.
Author: ConvergeHub
Version: 1.0.12
Author URI: https://www.convergehub.com/
PREFIX: CFACF7
*/
// check to make sure contact form 7 is installed and active
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
	define( 'PLUGIN_PATH', plugins_url( __FILE__ ) ); 
	function CFACF7_root_url( $append = false ) {
		$base_url = plugin_dir_url( __FILE__ );
		return ($append ? $base_url . $append : $base_url);
	}
	function CFACF7_root_dir( $append = false ) {
		$base_dir = plugin_dir_path( __FILE__ );
		return ($append ? $base_dir . $append : $base_dir);
	}
include_once( CFACF7_root_dir('inc/CFACF7-constants.php') );
	function CFACF7_enqueue( $hook ) {
    if ( !strpos( $hook, 'wpcf7' ) )
    	return;
    wp_enqueue_style( 'CFACF7-styles',
    	CFACF7_root_url('assets/css/CFACF7-styles.css'),
    	false,
    	CFACF7_VERSION );
	}
/**
	@Register stylesheet here
  */	
add_action( 'admin_enqueue_scripts', 'CFACF7_enqueue' );
	function CFACF7_add_admin_panel ( $panels ) {
		$new_page = array(
			'convergehub-forms-integration-addon' => array(
				'title' => __( 'ConvergeHub Addon', 'contact-form-7' ),
				'callback' => 'CFACF7_add_admin_panel_content'
			)
		);
		$panels = array_merge($panels, $new_page);
		return $panels;
	}
/**
	@Collect Mail Tags From Conatct Form 7
  */	
add_action('wpcf7_collect_mail_tags','CFACF7_mail_tages');
function CFACF7_mail_tages( $cf7 ) {
	$GLOBALS['CFACF7_va'] = $cf7;
	return $cf7;
}
/**
	@Call Editor Panel From Conatct Form 7
  */
add_filter( 'wpcf7_editor_panels', 'CFACF7_add_admin_panel' );
	function CFACF7_add_admin_panel_content( $cf7 ) {
		$post_id = intval($_GET['post']);
		$enabled = get_post_meta($post_id, "_CFACF7_enabled", true);
		$api_key = get_post_meta($post_id, "_CFACF7_api_key", true);
		$api_secret = get_post_meta($post_id, "_CFACF7_api_secret", true);
		$form_fields_str = get_post_meta($post_id, "_CFACF7_form_fields", true);
		$form_fields = $form_fields_str ? unserialize($form_fields_str) : false;
		$template = '';
		$fields_result = $select_val= $select = $form_fields_html = '';
		$activate_button = 'disabled';
		$description_api = $fields_result = $error_msg = '' ;
		$field_names_wrap = $error_msg_td ='style=display:none;';
		if($enabled && $api_key!="" && $api_secret!=""){
			$activate_button = 'enabled';
			$description_api = 'style=display:none;';
			$field_names_wrap ='';
			$fields_result = CFACF7_get_api_fields($enabled,$api_key,$api_secret);
			if(isset($fields_result->error) && $fields_result->error!="" && isset($fields_result->error_description) && $fields_result->error_description!="")
			{
	    		$error_msg  = $fields_result->error_description;
	    		$error_msg_td  = '';
	    		$description_api ='style=display:none;';
	    		$field_names_wrap ='style=display:none;';
	    	}
		}
		if(isset($fields_result->success))
		{
			foreach($GLOBALS['CFACF7_va'] as $form_tags)
			{
				$select = $selected_val = '';
				$form_fields_html .= '<input type="text" name="CFACF7_cf7_field[]" class="medium-text code cf7-field" value="'.$form_tags.'"  readonly="true"/> <i class="icon-arrow-right" style="line-height: 18px;"></i>';
				 $form_tags_new = $form_tags;
				foreach($form_fields as $key_select => $value_select){
					if( $form_tags_new == $value_select ) $selected_val = $key_select;
				}
				$form_fields_html .= '<select name="CFACF7_con_field[]" style="width:47%;font-weight: 400;" id="'.$selected_val.'">';
				$select ='';
				$form_fields_html .='<option value="-1">Select the field for mapping</option>';
				foreach ($fields_result->data as $key => $value) 
				{
					if($selected_val == $value->field_name) $select="selected";
					else $select="";
					$form_fields_html .='<option value="'.$value->field_name.'" '.$select.'>'.$value->field_label.'</option>';
				}
					$form_fields_html .='</select>';
			}
		}
		$search_replace = array(
			'{enabled}' 		=> ($enabled == 1 ? ' checked' : ''),
			'{api_key}'			=> $api_key,
			'{api_secret}' 		=> $api_secret,
			'{activate_button}' => $activate_button,
			'{description_api}' => $description_api,
			'{field_names_wrap}'=> $field_names_wrap,
			'{error_msg_td}'	=> $error_msg_td,
			'{error_msg}'		=> $error_msg,
			'{form_fields_html}'=> $form_fields_html,
		);
		$search = array_keys($search_replace);
		$replace = array_values($search_replace);
		$template = CFACF7_get_view_template('CFACF7-ui-tabs-panel.php');
		$admin_table_output = str_replace($search, $replace, $template);
		echo $admin_table_output;
	}
	function CFACF7_get_view_template( $template_name ) {
		$template_content = false;
		$template_path = CFACF7_VIEWS_DIR . $template_name;
		if( file_exists($template_path) ) {
			$search_replace = array(
				"<?php if(!defined( 'ABSPATH')) exit; ?>" => '',
				"{plugin_url}" => CFACF7_root_url(),
				"{site_url}" => get_site_url(),
			);
			$search = array_keys($search_replace);
			$replace = array_values($search_replace);
			$template_content = str_replace($search, $replace, file_get_contents( $template_path ));
		}
		return $template_content;
	}
	function CFACF7_admin_save_form( $cf7 ) {
		$post_id = intval($_GET['post']);
		$form_fields = array();
		foreach ($_POST['CFACF7_con_field'] as $key => $value) {
			if($_POST['CFACF7_cf7_field'][$key] == '' && $value == '') continue;
			$form_fields[$value] = sanitize_text_field($_POST['CFACF7_cf7_field'][$key]);
		}
		update_post_meta($post_id, '_CFACF7_enabled', sanitize_text_field($_POST['CFACF7_enabled']));
		update_post_meta($post_id, '_CFACF7_api_key', sanitize_text_field($_POST['CFACF7_api_key']));
		update_post_meta($post_id, '_CFACF7_api_secret', sanitize_text_field($_POST['CFACF7_api_secret']));
		update_post_meta($post_id, '_CFACF7_form_fields', serialize($form_fields));
	}
	add_action('wpcf7_save_contact_form', 'CFACF7_admin_save_form');

	function CFACF7_frontend_submit_form( $wpcf7_data ) {
		$post_id = intval($wpcf7_data->id());
		$enabled = sanitize_text_field(get_post_meta($post_id, "_CFACF7_enabled", true));
		$api_key = sanitize_text_field(get_post_meta($post_id, "_CFACF7_api_key", true));
		$api_secret = sanitize_text_field(get_post_meta($post_id, "_CFACF7_api_secret", true));
		$form_fields_str = sanitize_text_field(get_post_meta($post_id, "_CFACF7_form_fields", true));
		$form_fields = $form_fields_str ? unserialize($form_fields_str) : false;
    	if( $enabled == 1 && $form_fields && $api_key && $api_secret) {
			$post_array = [];
			foreach ($form_fields as $key => $value) {
				$search = array("[", "]");
				$post_key = str_replace($search, "", $value);
				if($key == "email") $post_array[$key] = [['address' => sanitize_email(wp_unslash(trim($_POST[$post_key])))]];
				else if($key == "phone") $post_array[$key] = [['number' => sanitize_text_field(wp_unslash(trim($_POST[$post_key])))]];
				else $post_array[$key] = sanitize_text_field(wp_unslash(trim($_POST[$post_key])));

			}
			CFACF7_create_lead_fn($api_key, $api_secret, $post_array);
    	}
	}
	add_action("wpcf7_before_send_mail", "CFACF7_frontend_submit_form");
}
/**
	@Create Lead on ConvergeHub.
  */
function CFACF7_create_lead_fn($apikey, $apiSecretkey, $request){ 
    $apiLog = [];
    $leadPostFields = [
        'apiSecret' =>  $apiSecretkey,
        'apiKey' => $apikey,
        'request' => $request,
    ];
    $module = "leads";
    $base_url = "https://api.convergehub.com/v2/";
    $url = $base_url . $module;
    $args = array(
	    'body' => json_encode($leadPostFields),
	    'timeout' => '45',
	    'redirection' => '5',
	    'httpversion' => '1.0',
	    'blocking' => true,
	    'headers' => array('content-type' => 'application/json'),
	    'cookies' => array()
	);
    $result = wp_remote_post( $url, $args );
}
/**
	@Get Custom Fields From ConvergeHub User.
  */
function CFACF7_get_api_fields($enabled=false,$apiKey,$apiSecret){

	if($enabled && $apiKey!="" && $apiSecret!="")
	{
	    $module = "leads";
	    $base_url = "https://api.convergehub.com/v2/";
	    $url = $base_url . "leads/fields?apiKey=".urlencode($apiKey)."&apiSecret=".$apiSecret;
	    $result = wp_remote_get($url);
	    $result = wp_remote_retrieve_body( $result );
	    if($result == "") $response ='error';
	    else $response = json_decode($result);
	    return $response;
	}
}
