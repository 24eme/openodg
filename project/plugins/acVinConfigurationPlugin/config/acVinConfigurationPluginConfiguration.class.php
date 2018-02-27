<?php

class acVinConfigurationPluginConfiguration extends sfPluginConfiguration
{
    public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/communes.yml', 'sfDefineEnvironmentConfigHandler');
            $configCache->checkConfig('config/communes.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/communes.yml'));
        }

        $this->dispatcher->connect('routing.load_configuration', array('ConfigurationRouting', 'listenToRoutingLoadConfigurationEvent'));
    }
}
