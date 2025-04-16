<?php
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('gaillac', 'prod', false);
sfConfig::set('app_region', 'IGPTARN');
session_name('IGPTARN');

sfContext::createInstance($configuration)->dispatch();
