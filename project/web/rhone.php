<?php


require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('nantes', 'dev', false);
sfContext::createInstance($configuration)->dispatch();
