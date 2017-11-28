<?php

class rhoneVinDRevPluginConfiguration extends sfPluginConfiguration
{
    public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/drev.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'drev_'));
            $configCache->checkConfig('config/drev.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/drev.yml'));
        }
    }


}
