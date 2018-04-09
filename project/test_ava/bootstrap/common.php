<?php

require_once(dirname(__FILE__).'/../../config/ProjectConfiguration.class.php');
require_once dirname(__FILE__).'/../../lib/vendor/symfony/test/bootstrap/unit.php';

$application = 'ava';

$configuration = ProjectConfiguration::getApplicationConfiguration($application, 'test', true);
$routing = clone ProjectConfiguration::getAppRouting();
$context = sfContext::createInstance($configuration);
$context->set('routing', $routing);
$db = new sfDatabaseManager($configuration);
