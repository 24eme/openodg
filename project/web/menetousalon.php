<?php
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('centre', 'prod', false);
sfConfig::set('app_region', 'MENETOUSALON');
session_name('MENETOUSALON');

sfContext::createInstance($configuration)->dispatch();
