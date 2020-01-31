<?php


require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('nantes', 'dev', true);
sfContext::createInstance($configuration)->dispatch();
