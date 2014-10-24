<?php

require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->enablePlugins('acCouchdbPlugin');
    $this->enablePlugins('acVinLibPlugin');
    $this->enablePlugins('acTCPDFPlugin');
    $this->enablePlugins('acCASPlugin');
    $this->enablePlugins('acVinConfigurationPlugin');
    $this->enablePlugins('acVinDRevPlugin');
    $this->enablePlugins('acVinDRevMarcPlugin');
    $this->enablePlugins('acVinDocumentPlugin');
    $this->enablePlugins('EtablissementPlugin');
    $this->enablePlugins('EmailPlugin');
    $this->enablePlugins('acExceptionNotifierPlugin');
  }
}
