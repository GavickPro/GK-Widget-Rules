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

load_plugin_textdomain( 'gk-widget-rules', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

include dirname( __FILE__ ) . "/back-end.php";
include dirname( __FILE__ ) . "/front-end.php";

// EOF