<?php
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('centre', 'prod', false);
session_name('REUILLY');
sfConfig::set('app_region', 'REUILLY');

sfContext::createInstance($configuration)->dispatch();
