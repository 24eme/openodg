<?php

putenv('APPLICATION=ava');

require_once(dirname(__FILE__).'/../../config/ProjectConfiguration.class.php');
require_once sfConfig::get('sf_symfony_lib_dir').'/../test/bootstrap/unit.php';

$application = 'ava';

$configuration = ProjectConfiguration::getApplicationConfiguration($application, 'test', true);
$db = new sfDatabaseManager($configuration);
$context = sfContext::createInstance($configuration);
if(getenv("COUCHURL")) {
    $db = sfContext::getInstance()->getDatabaseManager();
    $db->setDatabase('default', new acCouchdbDatabase(array('dsn' => preg_replace('|[^/]+$|', '', getenv("COUCHURL")), 'dbname' => preg_replace('|^.+/([^/]+$)|', '\1', getenv("COUCHURL")))));
}
