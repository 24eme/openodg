<?php
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('centre', 'prod', true);
sfConfig::set('app_region', 'CHATEAUMEILLANT');
session_name('CHATEAUMEILLANT');

sfContext::createInstance($configuration)->dispatch();
