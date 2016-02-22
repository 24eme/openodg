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
class TirageVinForm extends sfForm {

    public function __construct($defaults = array(), $options = array(), $CSRFSecret = null) {
        parent::__construct($defaults, $options, $CSRFSecret);
    }

    public function configure() {
        $this->setWidget('couleur', new sfWidgetFormChoice(array('expanded' => false, 'multiple' => false, 'choices' => $this->getCouleurs())));
        $this->setWidget('cepage', new sfWidgetFormChoice(array('expanded' => false, 'multiple' => true, 'choices' => $this->getCepages())));
        $this->setWidget('millesime', new sfWidgetFormChoice(array('expanded' => false, 'multiple' => false, 'choices' => $this->getMillesimes())));
        $this->setWidget('volume_ventile', new sfWidgetFormTextarea());
        $this->setWidget('fermentation_lactique', new sfWidgetFormChoice(array('expanded' => false, 'multiple' => false, 'choices' => $this->getFermentationLactique())));

        $this->widgetSchema->setLabel('couleur', 'Couleur');
        $this->widgetSchema->setLabel('cepage', 'Cépages');
        $this->widgetSchema->setLabel('millesime', 'Millesime');
        $this->widgetSchema->setLabel('volume_ventile', 'Indiquer le volume ventilé');
        $this->widgetSchema->setLabel('fermentation_lactique', 'fermentation lactique');

        $this->setValidator('couleur', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getCouleurs())), array('required' => "Aucune couleur n'a été choisie.")));
        $this->setValidator('cepage', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getCepages())), array('required' => "Aucune couleur n'a été choisie.")));

        $this->setValidator('millesime', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getMillesimes())), array('required' => "Aucune couleur n'a été choisie.")));

        $this->setValidator('volume_ventile', new sfValidatorString(array('required' => false)));
        $this->setValidator('fermentation_lactique', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getFermentationLactique())), array('required' => "Aucune couleur n'a été choisie.")));


        $this->widgetSchema->setNameFormat('tournee_add_agent[%s]');
    }

}
