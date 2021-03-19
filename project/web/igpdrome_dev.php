<?php
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('igpdrome', 'dev', true);

if (!in_array(@$_SERVER["REMOTE_ADDR"], sfConfig::get("app_debug_authorized_ip", array("127.0.0.1", "::1")) ))
{
  die('You ('.$_SERVER['REMOTE_ADDR'].') are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

sfContext::createInstance($configuration)->dispatch();
