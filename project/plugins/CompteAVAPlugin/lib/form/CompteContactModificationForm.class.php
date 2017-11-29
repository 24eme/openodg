<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CompteContactModificationForm
 *
 * @author mathurin
 */
class CompteContactModificationForm extends CompteModificationForm {

    private $syndicats;

    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->initDefaultSyndicats();
    }

    public function configure() {
        parent::configure();

        $this->setWidget("civilite", new sfWidgetFormChoice(array('choices' => $this->getCivilites())));
        $this->setWidget("prenom", new sfWidgetFormInput(array("label" => "Prénom")));
        $this->setWidget("nom", new sfWidgetFormInput(array("label" => "Nom")));

        $this->setWidget("raison_sociale", new sfWidgetFormInput(array("label" => "Société")));

        $this->setValidator('civilite', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getCivilites())), array('required' => "Aucune civilité choisie.")));
        $this->setValidator('prenom', new sfValidatorString(array("required" => false)));
        $this->setValidator('nom', new sfValidatorString(array("required" => false)));

        $this->setValidator('raison_sociale', new sfValidatorString(array("required" => false)));

        $this->setWidget("syndicats", new sfWidgetFormChoice(array('multiple' => true, 'choices' => $this->getSyndicats())));
        $this->setValidator('syndicats', new sfValidatorChoice(array("required" => false, 'multiple' => true, 'choices' => array_keys($this->getSyndicats()))));
    }

    private function getSyndicats() {
        $compteClient = CompteClient::getInstance();
        if (!$this->syndicats) {
            $this->syndicats = array();
            foreach ($compteClient->getAllSyndicats() as $syndicatId) {
                $syndicat = CompteClient::getInstance()->find($syndicatId);
                $this->syndicats[$syndicatId] = $syndicat->nom_a_afficher;
            }
        }
        return $this->syndicats;
    }

    public function initDefaultSyndicats() {
        $default_syndicats = array();
        foreach ($this->getObject()->getInfosSyndicats() as $syndicats_key => $syndicats_libelle) {
            $default_syndicats[] = $syndicats_key;
        }        
        $this->widgetSchema['syndicats']->setDefault($default_syndicats);
    }

    public function save($con = null) {
        if (array_key_exists('syndicats', $this->values)) {
            $syndicats = ($this->values['syndicats']) ? $this->values['syndicats'] : array();
            $this->getObject()->updateLocalSyndicats($syndicats);
        }
        
        parent::save($con);
    }

}
