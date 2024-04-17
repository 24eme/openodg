<?php
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('centre', 'prod', false);
sfConfig::set('app_region', 'SANCERRE');
session_name('SANCERRE');

sfContext::createInstance($configuration)->dispatch();
