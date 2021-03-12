<?php

class acVinParcellairePluginConfiguration extends sfPluginConfiguration
{
    public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/parcellaire.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'parcellaire_'));
            $configCache->checkConfig('config/parcellaire.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/parcellaire.yml'));
        }
    }}
