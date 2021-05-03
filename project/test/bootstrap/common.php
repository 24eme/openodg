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

$infosOrganisme = [
    'adresse' => '1 rue du chemin',
    'code_postal' => '12345',
    'ville' => 'Ville',
    'service_facturation' => 'Syndicat',
    'telephone' => 'Tel. 01 02 03 04 05',
    'email' => 'email@domain.com',
    'responsable' => 'Re Sponsable',
    'siret' => '12345678912345',
    'tva_intracom' => 'FR123456789123',
    'iban' => 'FR123456789123974747 (BICXXXXXX)',
    'oi' => 'OI'
];

sfConfig::set('app_facture_emetteur', [
    'IGP13' => $infosOrganisme
]);
