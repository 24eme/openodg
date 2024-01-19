<?php

class acVinAdelphePluginConfiguration extends sfPluginConfiguration
{
    public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/adelphe.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'adelphe_'));
            $configCache->checkConfig('config/adelphe.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/adelphe.yml'));
        }
    }
}
