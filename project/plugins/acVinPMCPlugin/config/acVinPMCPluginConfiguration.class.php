<?php

class acVinPMCPluginConfiguration extends sfPluginConfiguration
{
    public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/pmc.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'pmc_'));
            $configCache->checkConfig('config/pmc.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/pmc.yml'));
        }
    }


}
