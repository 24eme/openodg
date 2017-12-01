<?php
$_ENV["APPLICATION"] = "ava";

require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('ava', 'prod', false);
sfContext::createInstance($configuration)->dispatch();
