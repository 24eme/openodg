<?php

class acVinConditionnementPluginConfiguration extends sfPluginConfiguration
{
    public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/conditionnement.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'cond_'));
            $configCache->checkConfig('config/conditionnement.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/conditionnement.yml'));
        }
    }


}
