<?php
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('rhone', 'dev', true);

// this check prevents access to debug front controllers that are deployed by accident to production servers.
// feel free to remove this, extend it or make something more sophisticated.
if (!in_array(@$_SERVER["REMOTE_ADDR"], sfConfig::get("app_debug_authorized_ip", array("127.0.0.1", "::1")) ))
{
  die('You ('.$_SERVER['REMOTE_ADDR'].') are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}


sfContext::createInstance($configuration)->dispatch();
