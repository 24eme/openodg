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

if (sfConfig::has('app_redirect_domain_DEFAUT')) {
    $user = sfContext::getInstance()->getUser()->getCompteOrigin();
    $path = $context->getRequest()->getPathInfo();

    if (sfConfig::has('app_redirect_domain_'.$user->identifiant)) {
        if (sfConfig::get('app_redirect_domain_'.$user->identifiant)) {
            header('Location: http://'.sfConfig::get('app_redirect_domain_'.$user->identifiant).$path);
            exit;
        }
    } else {
        header('Location: http://'.sfConfig::get('app_redirect_domain_DEFAUT').$path);
        exit;
    }
}

$context->dispatch();
