<?php

class acVinHabilitationPluginConfiguration extends sfPluginConfiguration
{
    public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/habilitation.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'habilitation_'));
            $configCache->checkConfig('config/habilitation.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/habilitation.yml'));
        }
    }


}
