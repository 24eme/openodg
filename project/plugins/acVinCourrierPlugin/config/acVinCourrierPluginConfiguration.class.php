<?php

class acVinCourrierPluginConfiguration extends sfPluginConfiguration
{
    public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/courrier.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'courrier_'));
            $configCache->checkConfig('config/courrier.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/courrier.yml'));
        }
    }


}
