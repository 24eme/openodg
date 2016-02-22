<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TirageVinForm
 *
 * @author mathurin
 */
class TirageVinForm extends acCouchdbForm {

    protected $tirage = null;

    public function __construct(\acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $this->tirage = $doc;
        parent::__construct($doc, $defaults, $options, $CSRFSecret);
    }

    public function configure() {
        $this->setWidget('couleur', new sfWidgetFormChoice(array('expanded' => true, 'multiple' => false, 'choices' => $this->getCouleurs())));
        $this->setWidget('cepage', new sfWidgetFormChoice(array('expanded' => true, 'multiple' => true, 'choices' => $this->getCepages())));
        $this->setWidget('millesime', new sfWidgetFormChoice(array('expanded' => true, 'multiple' => false, 'choices' => $this->getMillesimes())));
        $this->setWidget('volume_ventile', new sfWidgetFormTextarea());
        $this->setWidget('fermentation_lactique', new sfWidgetFormInputCheckbox());
        

        $this->widgetSchema->setLabel('couleur', 'Couleur :');
        $this->widgetSchema->setLabel('cepage', 'Cépages :');
        $this->widgetSchema->setLabel('millesime', 'Millesime :');
        $this->widgetSchema->setLabel('volume_ventile', 'Indiquer le volume ventilé :');
        $this->widgetSchema->setLabel('fermentation_lactique', 'Fermentation lactique :');

        $this->setValidator('couleur', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getCouleurs())), array('required' => "Aucune couleur n'a été choisie.")));
        $this->setValidator('cepage', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getCepages())), array('required' => "Aucune couleur n'a été choisie.")));

        $this->setValidator('millesime', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getMillesimes())), array('required' => "Aucune couleur n'a été choisie.")));

        $this->setValidator('volume_ventile', new sfValidatorString(array('required' => false)));
       
        $this->setValidator('fermentation_lactique',  new sfValidatorBoolean(array('required' => false)));

        $this->widgetSchema->setNameFormat('tournee_add_agent[%s]');
    }

    public function getCouleurs() {
        return array("BLANC" => "Blanc", "ROSE" => "Rosé");
    }

    public function getCepages() {
        $cepageslist = array();
        foreach ($this->tirage->getConfigurationCepages() as $keyCepage => $cepage) {
            $cepageslist[$keyCepage] = $cepage->getLibelle();
        }
    return $cepageslist;
    
        }

    public function getMillesimes() {
        $annee = ConfigurationClient::getInstance()->getCampagneManager()->getCurrent();
        
        return array($annee => $annee, TirageClient::MILLESIME_ASSEMBLE => "Assemblé");
    }

    
}
