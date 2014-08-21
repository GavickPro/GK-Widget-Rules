<?php 

/*
Plugin Name:    GK Widget Rules
Plugin URI:     http://wordpress.org/extend/plugins/gk-widget-rules/
Description:    Control widgets with WP's conditional tags is_home etc
Version:        1.0.0
Author:         GavickPro
Author URI:     http://www.gavick.com
 
Text Domain:   gk-widget-rules
Domain Path:   /languages/
*/ 

global $pagenow;

/**
 * i18n - language files should be like gk-widget-rules-en_GB.po and gk-widget-rules-en_GB.mo
 */
add_action( 'plugins_loaded', 'gk_widget_rules_load_textdomain' );

function gk_widget_rules_load_textdomain() {
    load_plugin_textdomain( 'gk-widget-rules', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}

// Loading necessary classes
include dirname( __FILE__ ) . "/back-end.php";
include dirname( __FILE__ ) . "/front-end.php";

// EOF
