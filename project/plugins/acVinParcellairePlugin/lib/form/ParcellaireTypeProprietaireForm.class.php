<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParcellaireExploitationTypeProprietaireForm
 *
 * @author mathurin
 */
class ParcellaireTypeProprietaireForm extends acCouchdbForm {

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {

        parent::__construct($doc, $defaults, $options, $CSRFSecret);

        $this->setDefaults(array_merge($this->getDefaults(), $this->getDefaultsAcheteurs()));
    }

    public function configure() {
        //$typesProprietaire = $this->getTypesProprietaire();
        $vendeurs = $this->getAllVendeurs(ParcellaireClient::DESTINATION_ADHERENT_CAVE_COOP);

        foreach(ParcellaireClient::$destinations_libelles as $destination_key => $destination_libelle) {
            $form = new BaseForm();
            $form->setWidget('declarant', new sfWidgetFormInputCheckbox());
            $form->setValidator('declarant', new sfValidatorBoolean());
            $form->widgetSchema->setLabel('declarant', $destination_libelle);

            $form->setWidget('acheteurs', new sfWidgetFormChoice(array('multiple' => true, 'choices' => $vendeurs)));
            $form->setValidator('acheteurs', new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys($vendeurs)), array()));

            $this->embedForm($destination_key, $form);
        }

        $this->widgetSchema->setNameFormat('parcellaire_type_proprietaire[%s]');
    }

    public function getDefaultsAcheteurs() {
        $default_acheteurs = array();
        
        foreach($this->getDocument()->acheteurs as $type => $acheteurs) {
            $default_acheteurs[$type]['declarant'] = true;
            foreach($acheteurs as $acheteur) {
                $default_acheteurs[$type]['acheteurs'][] = $acheteur->getKey();
            }
        }

        return $default_acheteurs;
    }

    public function update() {
        foreach($this->values as $key => $value) {
            if(!is_array($value)) {
                continue;
            }
            $this->updateAcheteurs($key, $value);
        }
        //$this->getDocument()->cleanAcheteurNode();
    }

    public function save() {

        $this->getDocument()->save();
    }

    public function updateAcheteurs($type, $values) {
        $acheteurs_to_delete = array();

        if(!isset($values["declarant"]) || !$values["declarant"]) {

            $this->getDocument()->acheteurs->remove($type);
            return;
        }

        $noeud = $this->getDocument()->acheteurs->add($type);

        foreach($noeud as $acheteur) {
            if(!in_array($acheteur->getKey(), $values) && !count($acheteur->produits)) {
                $acheteurs_to_delete[] = $acheteur->getKey();
            }
        }

        foreach($acheteurs_to_delete as $cvi) {
            $noeud->remove($cvi);
        }

        if(!isset($values["acheteurs"]) || !is_array($values["acheteurs"])) {

            return;
        }

        foreach($values["acheteurs"] as $cvi) {
            $this->getDocument()->getDocument()->addAcheteurNode($type, $cvi);
        }
    }

    public function getTypesProprietaire() {
        return ParcellaireClient::$type_proprietaire_libelles;
    }

    public function getAllVendeurs($type) {
        $types_vendeurs = array(CompteClient::ATTRIBUT_ETABLISSEMENT_NEGOCIANT,
            CompteClient::ATTRIBUT_ETABLISSEMENT_CAVE_COOPERATIVE);

        $query = "statut:ACTIF AND (";
        foreach ($types_vendeurs as $type_vendeurs) {
            $query .="infos.attributs." . $type_vendeurs . ":\"" . CompteClient::getInstance()->getAttributLibelle($type_vendeurs) . "\" OR ";
        }
        $query = substr($query, 0, strlen($query) - 4) . ")";

        $qs = new acElasticaQueryQueryString($query);
        $q = new acElasticaQuery();
        $q->setLimit(9999);
        $q->setQuery($qs);

        $index = acElasticaManager::getType('compte');

        $resset = $index->search($q);
        $results = $resset->getResults();

        $list = array();
        foreach ($results as $res) {
            $data = $res->getData();
            $list[$data['cvi']] = sprintf("%s - %s - %s", $data['nom_a_afficher'], $data['cvi'], $data['commune']);
        }

        return $list;
    }

}
