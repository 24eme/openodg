<?php

class EtablissementAVAPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
      if ($this->configuration instanceof sfApplicationConfiguration) {
          $configCache = $this->configuration->getConfigCache();
          include($configCache->checkConfig('config/societe.yml'));
      }
  }

  public function setup() {
      if ($this->configuration instanceof sfApplicationConfiguration) {
          $configCache = $this->configuration->getConfigCache();
          $configCache->registerConfigHandler('config/societe.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'societe_'));
          $configCache->checkConfig('config/societe.yml');
      }
  }
}
