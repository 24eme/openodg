<?php

class rhoneConfiguration extends sfApplicationConfiguration
{

    public function setup()
    {
        parent::setup();

        $this->enablePlugins('rhoneVinDRevPlugin');
        $this->enablePlugins('rhoneVinConfigurationPlugin');
        $this->enablePlugins('acVinHabilitationPlugin');
        $this->enablePlugins('acVinComptePlugin');
        $this->enablePlugins('acVinSocietePlugin');
        $this->enablePlugins('acVinEtablissementPlugin');
        $this->enablePlugins('DRPlugin');
        $this->enablePlugins('SV11Plugin');
        $this->enablePlugins('SV12Plugin');
    }

    public function configure()
    {
        $configCache = $this->getConfigCache();
        $configCache->registerConfigHandler('config/points_aides.yml', 'sfDefineEnvironmentConfigHandler');
        $configCache->checkConfig('config/points_aides.yml');

    }

    public function initialize()
    {
        include($this->getConfigCache()->checkConfig('config/points_aides.yml'));
    }
}
