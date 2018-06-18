<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*
Plugin Name: CMB2 Wiki
Description: An integrated wiki for the theme options page
Version: 1.0.0
Author: Niklas Rosenqvist
Author URI: https://www.nsrosenqvist.com/
*/

if (! class_exists('CMB2_Wiki')) {
    class CMB2_Wiki
    {
        static function init()
        {
            if (! class_exists('CMB2')) {
                return;
            }

            // Include files
            require_once __DIR__ .'/src/Integration.php';
            require_once __DIR__.'/src/helpers.php';

            // Initalize plugin
            \NSRosenqvist\CMB2\WikiField\Integration::init();
        }
    }
}
add_action('init', [CMB2_Wiki::class, 'init']);
