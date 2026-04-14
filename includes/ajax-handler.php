<?php

$ajax_services = glob(PM_PLUGIN_DIR . 'includes/ajax-services/*.php');

foreach ($ajax_services as $ajax_service) {
    require_once $ajax_service;
}