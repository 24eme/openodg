<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// guess current application
if (!isset($app))
{
  $traces = debug_backtrace();
  $caller = $traces[0];

  $dirPieces = explode(DIRECTORY_SEPARATOR, dirname($caller['file']));
  $app = array_pop($dirPieces);
}

if(!$app || $app == "app") {
    $app = (getenv("APPLICATION")) ? getenv("APPLICATION") : 'rhone';
}

require_once dirname(__FILE__).'/../../config/ProjectConfiguration.class.php';
require_once dirname(__FILE__).'/Browser.class.php';

$configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);
sfContext::createInstance($configuration);
if(getenv("COUCHURL")) {
    $db = sfContext::getInstance()->getDatabaseManager();
    $db->setDatabase('default', new acCouchdbDatabase(array('dsn' => preg_replace('|[^/]+$|', '', getenv("COUCHURL")), 'dbname' => preg_replace('|^.+/([^/]+$)|', '\1', getenv("COUCHURL")))));
}

// remove all cache
sfToolkit::clearDirectory(sfConfig::get('sf_app_cache_dir'));

$facture_emetteur_test = [
    'IGP13' => [
        'adresse' => '1 rue du chemin',
        'code_postal' => '12345',
        'ville' => 'Ville',
        'service_facturation' => 'Syndicat',
        'telephone' => 'Tel. 01 02 03 04 05',
        'email' => 'email@domain.com',
        'responsable' => 'Re Sponsable'
    ]
];

define ('K_PATH_CACHE', sys_get_temp_dir().'/');
