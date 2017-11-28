<?php

class avaConfiguration extends sfApplicationConfiguration
{

    public function setup() {
        parent::setup();

        $this->enablePlugins('ComptePlugin');
        $this->enablePlugins('EtablissementPlugin');
        $this->enablePlugins('acVinAbonnementPlugin');
        $this->enablePlugins('acVinTiragePlugin');
        $this->enablePlugins('avaVinDRevPlugin');
        $this->enablePlugins('acVinDRevMarcPlugin');
        $this->enablePlugins('acVinTravauxMarcPlugin');
        $this->enablePlugins('avaVinConfigurationPlugin');
        $this->enablePlugins('acVinDegustationPlugin');
        $this->enablePlugins('acVinParcellairePlugin');
        $this->enablePlugins('acVinTourneePlugin');
        $this->enablePlugins('acVinConstatsVTSGNPlugin');

    }

    public function configure() {

    }
}
