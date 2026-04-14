<?php

/**
 * Initialize custom post types for the Product Manager plugin
 * This file loads all post type definitions from the 'post-types' folder.
 * 
 * note: add for scalability and maintainability.
 * 
 */
$post_types = glob(PM_PLUGIN_DIR . 'includes/post-types/*.php');

foreach ($post_types as $post_type) {
    require_once $post_type;
}