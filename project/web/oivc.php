<?php

require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('oivc', 'prod', false);
session_name('OIVC');
sfConfig::set('app_region', 'OIVC');

sfContext::createInstance($configuration)->dispatch();
