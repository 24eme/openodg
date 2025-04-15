<?php
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('gaillac', 'prod', false);
sfConfig::set('app_region', 'AOCGAILLAC');
session_name('AOCGAILLAC');

sfContext::createInstance($configuration)->dispatch();
