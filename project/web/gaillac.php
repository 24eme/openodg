<?php
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('gaillac', 'prod', false);
sfConfig::set('app_region', 'AOPGAILLAC');
session_name('AOPGAILLAC');

sfContext::createInstance($configuration)->dispatch();
