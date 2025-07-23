<?php
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('gaillac', 'prod', false);
sfConfig::set('app_region', 'AOPGAILLAC');
session_name('AOPGAILLAC');

$context = sfContext::createInstance($configuration);
$user = $context->getUser()->getCompteOrigin();

if ($user && sfConfig::has('app_redirect_domain_DEFAUT')) {
    $request = $context->getRequest();

    $location = sfConfig::has('app_redirect_domain_'.$user->identifiant)
              ? sfConfig::get('app_redirect_domain_'.$user->identifiant)
              : sfConfig::get('app_redirect_domain_DEFAUT');

    if ($location !== null && $location !== $request->getHost()) {
        $path = $request->getPathInfo();
        $query = parse_url($request->getUri(), PHP_URL_QUERY)
               ? '?'.parse_url($request->getUri(), PHP_URL_QUERY)
               : '';

        header("Location: http://$location$path$query");
        exit;
    }
}

$context->dispatch();
