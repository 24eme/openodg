<?php

require_once(dirname(__FILE__).'/../../config/ProjectConfiguration.class.php');
require_once dirname(__FILE__).'/../../lib/vendor/symfony/test/bootstrap/unit.php';

$application = (getenv("APPLICATION")) ? getenv("APPLICATION") : 'rhone';

$configuration = ProjectConfiguration::getApplicationConfiguration($application, 'test', true);
$context = sfContext::createInstance($configuration);
if(getenv("COUCHURL")) {
    $db = sfContext::getInstance()->getDatabaseManager();
    $db->setDatabase('default', new acCouchdbDatabase(array('dsn' => preg_replace('|[^/]+$|', '', getenv("COUCHURL")), 'dbname' => preg_replace('|^.+/([^/]+$)|', '\1', getenv("COUCHURL")))));
}
$societeConfig = sfConfig::get('societe_configuration_societe');
unset($societeConfig['disable_save']);
sfConfig::set('societe_configuration_societe', $societeConfig);
sfConfig::set('app_facture_emetteur', [
    'IGP13' => [
        'email' => 'email@domain.com'
    ]
]);
