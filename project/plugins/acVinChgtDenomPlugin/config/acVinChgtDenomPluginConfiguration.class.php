<?php

class acVinChgtDenomPluginConfiguration extends sfPluginConfiguration
{
    public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/chgtdenom.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'chgtdenom_'));
            $configCache->checkConfig('config/chgtdenom.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/chgtdenom.yml'));
        }
    }
}
