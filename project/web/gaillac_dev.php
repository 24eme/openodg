<?php
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('gaillac', 'dev', true);
sfConfig::set('app_region', 'AOPGAILLAC');
session_name('AOPGAILLAC');

if (!in_array(@$_SERVER["REMOTE_ADDR"], sfConfig::get("app_debug_authorized_ip", array("127.0.0.1", "::1")) ))
{
  die('You ('.$_SERVER['REMOTE_ADDR'].') are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

$context = sfContext::createInstance($configuration);
$user = $context->getUser()->getCompteOrigin();

if ($user && sfConfig::has('app_redirect_domain_DEFAUT')) {
    $request = $context->getRequest();

    $location = sfConfig::has('app_redirect_domain_'.$user->identifiant)
              ? sfConfig::get('app_redirect_domain_'.$user->identifiant)
              : sfConfig::get('app_redirect_domain_DEFAUT');

    if ($location !== null && $location !== $request->getHost()) {
        $path = $request->getPathInfo();

        header("Location: http://$location$path");
        exit;
    }
}

$context->dispatch();
