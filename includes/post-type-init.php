<?php

$post_types = glob(PM_PLUGIN_DIR . 'includes/post-types/*.php');

foreach ($post_types as $post_type) {
    require_once $post_type;
}