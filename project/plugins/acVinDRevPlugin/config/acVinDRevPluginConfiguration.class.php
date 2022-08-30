<?php

class acVinDRevPluginConfiguration extends sfPluginConfiguration
{
    public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/drev.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'drev_'));
            $configCache->checkConfig('config/drev.yml');
            $configCache->registerConfigHandler('config/region.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'region_'));
            $configCache->checkConfig('config/region.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/drev.yml'));
            include($configCache->checkConfig('config/region.yml'));
        }
    }


}
