<?php

class rhoneConfiguration extends sfApplicationConfiguration
{
    public function configure()
    {
        $configCache = $this->getConfigCache();
        $configCache->registerConfigHandler('config/points_aides.yml', 'sfDefineEnvironmentConfigHandler');
        $configCache->checkConfig('config/points_aides.yml');

    }

    public function initialize()
    {
        include($this->getConfigCache()->checkConfig('config/points_aides.yml'));
    }
}
