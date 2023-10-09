<?php
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('centre', 'prod', true);
sfConfig::set('app_region', 'MENETOUSALON');

sfContext::createInstance($configuration)->dispatch();
