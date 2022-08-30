<?php
/*
Plugin Name: Recomended products for WC
Description: Add Recomended products block to single product page
Version: 1.0
Author: Volodymyr Ponedilchuk
*/


// Activation hook
function recomendedproductsforwc_activate() {
}
register_activation_hook( __FILE__ , 'recomendedproductsforwc_activate' );

// Deactivation hook
function recomendedproductsforwc_deactivate() {
}
register_deactivation_hook( __FILE__ , 'recomendedproductsforwc_deactivate' );

// Plugin functions.
include_once('functions.php');


function recomendedproductsforwc_load_scripts_and_styles() {
    $plugin_url = plugin_dir_url( __FILE__ );
    $plugin_path = plugin_dir_path(  __FILE__ ) ;

    if (is_product() ){
        wp_enqueue_style( 'recomendedproductsforwc_style',  $plugin_url . "css/style.css", '', filemtime($plugin_path . "css/style.css"));
       // wp_enqueue_script( 'recomendedproductsforwc_script',  $plugin_url . "js/script.js", '', filemtime($plugin_path ."js/script.js"));
    }
}

add_action( 'wp_enqueue_scripts', 'recomendedproductsforwc_load_scripts_and_styles' );