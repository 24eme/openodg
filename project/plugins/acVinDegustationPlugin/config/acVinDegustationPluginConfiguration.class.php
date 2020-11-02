<?php

class acVinDegustationPluginConfiguration extends sfPluginConfiguration
{
    public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/degustation.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'degustation_'));
            $configCache->checkConfig('config/degustation.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/degustation.yml'));
        }
    }
}