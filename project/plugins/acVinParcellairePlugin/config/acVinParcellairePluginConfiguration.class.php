<?php

class acVinParcellairePluginConfiguration extends sfPluginConfiguration
{
    public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/parcellaire.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'parcellaire_'));
            $configCache->checkConfig('config/parcellaire.yml');
            $configCache->registerConfigHandler('config/controle.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'controle_'));
            $configCache->checkConfig('config/controle.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/parcellaire.yml'));
            include($configCache->checkConfig('config/controle.yml'));
        }
    }}
