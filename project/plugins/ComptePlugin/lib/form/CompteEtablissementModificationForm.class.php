<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CompteEtablissementModificationForm
 *
 * @author mathurin
 */
class CompteEtablissementModificationForm extends CompteModificationForm {

    private $syndicats;
    private $nbChais;

    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->initDefaultSyndicats();
        $this->initDefaultChais();
    }

    public function configure() {
        parent::configure();

        $this->setWidget("raison_sociale", new sfWidgetFormInput(array("label" => "Raison sociale")));

        $this->setValidator('raison_sociale', new sfValidatorString(array("required" => true)));

        if($this->getObject()->isNew()) {
            $this->setWidget("cvi", new sfWidgetFormInput(array("label" => "CVI")));
            $this->setValidator('cvi', new sfValidatorRegex(array("required" => true, "pattern" => "/^[0-9]{10}$/"), array("invalid" => "Le cvi doit être un nombre à 10 chiffres")));
        }

        $this->setWidget("siret", new sfWidgetFormInput(array("label" => "N° SIRET / SIREN")));
        $this->setValidator('siret', new sfValidatorRegex(array("required" => false, "pattern" => "/^([0-9]{14})|([0-9]{9})$/"), array("invalid" => "Le SIRET doit être un nombre à 14 chiffres ou à 9 chiffres pour le SIREN")));


        $this->setWidget("syndicats", new sfWidgetFormChoice(array('multiple' => true, 'choices' => $this->getSyndicats())));
        $this->setValidator('syndicats', new sfValidatorChoice(array("required" => false, 'multiple' => true, 'choices' => array_keys($this->getSyndicats()))));

        $this->getValidator('adresse')->setOption('required', true);

        $nbChais = $this->getNbChais();
        $formChais = new CompteChaisCollectionForm($this->getObject(), array(), array(
            'nbChais' => $nbChais));
        $this->embedForm('chais', $formChais);
    }

    public function initDefaultSyndicats() {
        $default_syndicats = array();
        foreach ($this->getObject()->getInfosSyndicats() as $syndicats_key => $syndicats_libelle) {
            $default_syndicats[] = $syndicats_key;
        }
        $this->widgetSchema['syndicats']->setDefault($default_syndicats);
    }

    public function initDefaultChais() {
        foreach($this->getObject()->chais as $key => $chai) {
            $this->defaults['chais'][$key]['attributs'] = array_keys($chai->attributs->toArray(true, false));
        }
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

    public function getNbChais() {
        if (is_null($this->nbChais)) {
            if (is_null($this->getObject()->getChais())) {
                $this->nbChais = 0;
            } else {
                $this->nbChais = count($this->getObject()->getChais());
            }
        }
        return $this->nbChais;
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);

        $newChais = array();
        foreach ($this->getObject()->chais as $chai) {
            if($chai->adresse && $chai->commune && $chai->code_postal){
                $newChai = $chai->toArray(false, false);
                $newChai['attributs'] = array();
                foreach($chai->attributs as $key) {
                    $newChai['attributs'][$key] = CompteClient::getInstance()->getChaiAttributLibelle($key);
                }
                $newChais[] = $newChai;
            }
            
        }
        $this->getObject()->remove("chais");
        $this->getObject()->add("chais", $newChais);
    }

    public function save($con = null) {
        if (array_key_exists('syndicats', $this->values)) {
            $syndicats = ($this->values['syndicats']) ? $this->values['syndicats'] : array();
            $this->getObject()->updateLocalSyndicats($syndicats);
        }
        
        parent::save($con);
    }

}
