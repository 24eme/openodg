<?php
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('gaillac', 'dev', true);
sfConfig::set('app_region', 'AOCGAILLAC');
session_name('AOCGAILLAC');

if (!in_array(@$_SERVER["REMOTE_ADDR"], sfConfig::get("app_debug_authorized_ip", array("127.0.0.1", "::1")) ))
{
  die('You ('.$_SERVER['REMOTE_ADDR'].') are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

$context = sfContext::createInstance($configuration);
if (sfConfig::has('app_redirect_domain_DEFAUT')) {
  $user = $context->getUser()->getCompteOrigin();
  if ($user && strpos($_SERVER['REQUEST_URI'], 'logout') === false) {
    $request = $context->getRequest();

    $location = sfConfig::has('app_redirect_domain_'.$user->identifiant)
              ? sfConfig::get('app_redirect_domain_'.$user->identifiant)
              : sfConfig::get('app_redirect_domain_DEFAUT');

    if ($location !== null && $location !== $request->getHost()) {
        $path = $request->getPathInfo();
        $query = parse_url($request->getUri(), PHP_URL_QUERY)
               ? '?'.parse_url($request->getUri(), PHP_URL_QUERY)
               : '';

       $context->getUser()->signOutOrigin();
       unset($_SESSION['phpCAS']);

        header("Location: http://$location$path$query");
        exit;
    }
  }
}

$context->dispatch();
