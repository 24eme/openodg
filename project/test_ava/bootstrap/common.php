<?php

require_once(dirname(__FILE__).'/../../config/ProjectConfiguration.class.php');
require_once dirname(__FILE__).'/../../lib/vendor/symfony/test/bootstrap/unit.php';

$application = 'ava';

$configuration = ProjectConfiguration::getApplicationConfiguration($application, 'dev', true);

$db = new sfDatabaseManager($configuration);
