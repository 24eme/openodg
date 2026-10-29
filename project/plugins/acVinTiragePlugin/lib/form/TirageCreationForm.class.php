<?php

class TirageCreationForm extends BaseForm
{
    public function configure()
    {
        $this->setWidget('campagne', new sfWidgetFormChoice(array('choices' => $this->getCampagnes())));
        $this->getWidget('campagne')->setLabel("Campagne");
        $this->setValidator('campagne',  new sfValidatorChoice(array('required' => true, 'choices' => $this->getCampagnes())));
        $this->widgetSchema->setNameFormat('tirage_creation[%s]');
    }

    public function getCampagnes() {
        $campagnes = array();
        $year = ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent()*1;

        while($year >= 2014) {
            $campagnes[$year.""] = $year."";
            $year = $year - 1;
        }
        return $campagnes;
    }
}
