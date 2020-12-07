<?php


require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('igpvdl', 'prod', false);
sfContext::createInstance($configuration)->dispatch();
