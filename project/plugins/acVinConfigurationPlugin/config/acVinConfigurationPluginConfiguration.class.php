<?php

class acVinConfigurationPluginConfiguration extends sfPluginConfiguration
{
    public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/communes.yml', 'sfDefineEnvironmentConfigHandler');
            $configCache->checkConfig('config/communes.yml');            
            $configCache->registerConfigHandler('config/configuration.yml', 'sfDefineEnvironmentConfigHandler');
            $configCache->checkConfig('config/configuration.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/communes.yml'));
            include($configCache->checkConfig('config/configuration.yml'));
        }

        $this->dispatcher->connect('routing.load_configuration', array('ConfigurationRouting', 'listenToRoutingLoadConfigurationEvent'));
    }
}
