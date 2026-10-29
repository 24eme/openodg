<?php

class DRPluginConfiguration extends sfPluginConfiguration
{
    public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/dr.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'dr_'));
            $configCache->checkConfig('config/dr.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/dr.yml'));
        }
    }
}
