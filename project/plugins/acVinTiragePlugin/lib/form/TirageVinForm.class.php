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
class TirageVinForm extends acCouchdbObjectForm {

    protected $tirage = null;
    protected $annee = null;

    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $this->tirage = $object;
        $this->annee = ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent();
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        $this->setWidget('couleur', new bsWidgetFormChoice(array('expanded' => true, 'multiple' => false, 'choices' => $this->getCouleurs())));
        $this->setWidget('cepages_actifs', new bsWidgetFormChoice(array('expanded' => true, 'multiple' => true, 'choices' => $this->getCepages())));
        $this->setWidget('millesime', new bsWidgetFormChoice(array('expanded' => true, 'multiple' => false, 'choices' => $this->getMillesimes())));
        $this->setWidget('millesime_ventilation', new bsWidgetFormInput());
        $this->setWidget('fermentation_lactique', new bsWidgetFormInputCheckbox(array()));


        $this->widgetSchema->setLabel('couleur', 'Couleur :');
        $this->widgetSchema->setLabel('cepages_actifs', 'Cépages :');
        $this->widgetSchema->setLabel('millesime', 'Millesime :');
        $this->widgetSchema->setLabel('millesime_ventilation', 'Ventilation des millesimes :');
        $this->widgetSchema->setLabel('fermentation_lactique', 'Fermentation malo-lactique :');

        $this->setValidator('couleur', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getCouleurs())), array('required' => "Aucune couleur n'a été choisie")));
        $this->setValidator('cepages_actifs', new sfValidatorChoice(array("multiple" => true, "required" => true, 'choices' => array_keys($this->getCepages())), array('required' => "Aucun cépage n'a été choisi")));

        $this->setValidator('millesime', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getMillesimes())), array('required' => "Aucun millesiume n'a été choisi")));

        $this->setValidator('millesime_ventilation', new sfValidatorString(array('required' => false)));

        $this->setValidator('fermentation_lactique', new sfValidatorBoolean(array('required' => false)));

        $this->widgetSchema->setNameFormat('tirage_vin[%s]');
    }

    public function getCouleurs() {
        return TirageClient::$couleurs;
    }

    public function getCepages() {
        $cepageslist = array();
        foreach ($this->tirage->cepages as $cepage) {
            $cepageslist[$cepage->getkey()] = $cepage->getLibelle();
        }
        return $cepageslist;
    }

    public function getMillesimes() {

        return array(($this->annee - 3)."" => ($this->annee - 3)."",($this->annee - 2)."" => ($this->annee - 2)."",($this->annee - 1)."" => ($this->annee - 1)."", $this->annee => $this->annee, TirageClient::MILLESIME_ASSEMBLE => "Assemblage");
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $cepagesValues = $values['cepages_actifs'];
        foreach ($this->getCepages() as $key => $cepage) {
            $this->getObject()->cepages->get($key)->selectionne = intval(in_array($key, $cepagesValues));
        }
        $this->getObject()->couleur_libelle = TirageClient::$couleurs[$values['couleur']];
        $this->getObject()->millesime_libelle = ($values['millesime'] == TirageClient::MILLESIME_ASSEMBLE)? 'Assemblage' : $values['millesime'];
    }

    public function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        if (!$this->getObject()->getMillesime()) {
            $this->setDefault('millesime', $this->annee);
        }
        if (!$this->getObject()->getCouleur()) {
            $this->setDefault('couleur', TirageClient::COULEUR_BLANC);
        }
        $cepagesDefault = array();
        foreach ($this->getObject()->getCepages() as $cepageKey => $cepage) {
            if($cepage->selectionne){
                $cepagesDefault[] = $cepageKey;
            }
        }
        $this->setDefault('cepages_actifs', $cepagesDefault);
    }

}
