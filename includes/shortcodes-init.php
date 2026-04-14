<?php

/**
 * Initialize shortcodes for the Product Manager plugin
 * This file loads all shortcode definitions from the 'shortcodes' folder.
 * 
 * note: add for scalability and maintainability.
 * 
 */

$shortcodes = glob(PM_PLUGIN_DIR . 'includes/shortcodes/*.php');

foreach ($shortcodes as $shortcode) {
    require_once $shortcode;
}