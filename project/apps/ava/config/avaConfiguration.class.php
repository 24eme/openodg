<?php

class avaConfiguration extends sfApplicationConfiguration
{

    public function setup() {
        parent::setup();

        $this->enablePlugins('CompteAVAPlugin');
        $this->enablePlugins('EtablissementAVAPlugin');
        $this->enablePlugins('acVinDRevAVAPlugin');
        $this->enablePlugins('acVinConfigurationAVAPlugin');

        $this->enablePlugins('acVinAbonnementPlugin');
        $this->enablePlugins('acVinTiragePlugin');
        $this->enablePlugins('acVinDRevMarcPlugin');
        $this->enablePlugins('acVinTravauxMarcPlugin');
        $this->enablePlugins('acVinDegustationPlugin');
        $this->enablePlugins('acVinParcellairePlugin');
        $this->enablePlugins('acVinTourneePlugin');
        $this->enablePlugins('acVinConstatsVTSGNPlugin');
    }

    public function configure() {

    }
}
