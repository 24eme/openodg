<?php

class acVinConfigurationAVAPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
      $this->dispatcher->connect('routing.load_configuration', array('ConfigurationRouting', 'listenToRoutingLoadConfigurationEvent'));

  }
}
