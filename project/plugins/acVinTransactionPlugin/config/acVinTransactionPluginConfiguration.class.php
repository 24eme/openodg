<?php

class acVinTransactionPluginConfiguration extends sfPluginConfiguration
{
    public function setup() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            $configCache->registerConfigHandler('config/transaction.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'cond_'));
            $configCache->checkConfig('config/transaction.yml');
        }
    }

    public function initialize() {
        if ($this->configuration instanceof sfApplicationConfiguration) {
            $configCache = $this->configuration->getConfigCache();
            include($configCache->checkConfig('config/transaction.yml'));
        }
    }


}
