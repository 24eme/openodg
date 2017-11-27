<?php


require_once(dirname(__FILE__).'/../config/RhoneProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('rhone', 'prod', false);
sfContext::createInstance($configuration)->dispatch();
