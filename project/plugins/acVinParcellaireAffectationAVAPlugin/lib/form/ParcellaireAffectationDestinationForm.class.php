<?php

class ParcellaireAffectationDestinationForm extends acCouchdbForm {

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {

        parent::__construct($doc, $defaults, $options, $CSRFSecret);

        $this->setDefaults(array_merge($this->getDefaults(), $this->getDefaultsAcheteurs()));
    }

    public function configure() {

        foreach(ParcellaireAffectationClient::$destinations_libelles as $destination_key => $destination_libelle) {
            $form = new BaseForm();
            $form->setWidget('declarant', new sfWidgetFormInputCheckbox());
            $form->setValidator('declarant', new sfValidatorBoolean());
            $form->widgetSchema->setLabel('declarant', $destination_libelle);

            if($destination_key != ParcellaireAffectationClient::DESTINATION_SUR_PLACE) {
                $acheteurs = $this->getAcheteurs($destination_key);
                $form->setWidget('acheteurs', new sfWidgetFormChoice(array('multiple' => true, 'choices' => $acheteurs)));
                $form->setValidator('acheteurs', new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys($acheteurs)), array()));
            }
            $this->embedForm($destination_key, $form);
        }

        $this->validatorSchema->setPostValidator(new ParcellaireAffectationDestinationsValidator());

        $this->widgetSchema->setNameFormat('parcellaire_destination[%s]');
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

        if($type == ParcellaireAffectationClient::DESTINATION_SUR_PLACE) {
            $values["acheteurs"] = array($this->getDocument()->identifiant);
        }

        $noeud = $this->getDocument()->acheteurs->add($type);

        $values_acheteurs = array();

        if(isset($values["acheteurs"]) && is_array($values["acheteurs"])) {

           $values_acheteurs = $values["acheteurs"];
        }

        foreach($noeud as $acheteur) {
            if(!in_array($acheteur->getKey(), $values) && !count($acheteur->produits)) {
                $acheteurs_to_delete[] = $acheteur->getKey();
            }
        }

        foreach($acheteurs_to_delete as $cvi) {
            $noeud->remove($cvi);
        }

        foreach($values["acheteurs"] as $cvi) {
            $this->getDocument()->getDocument()->addAcheteur($type, $cvi);
        }

    }

    public function getTypesProprietaire() {

        return ParcellaireAffectationClient::$type_proprietaire_libelles;
    }

    public function getAcheteurs($type) {
        $types_acheteurs = array($type);

        $query = "doc.statut:ACTIF AND (";
        foreach ($types_acheteurs as $type_acheteurs) {
            $query .="doc.tags.attributs:\"" . CompteClient::getInstance()->getAttributLibelle($type_acheteurs) . "\" OR ";
        }
        $query = substr($query, 0, strlen($query) - 4) . ")";

        $qs = new acElasticaQueryQueryString($query);
        $q = new acElasticaQuery();
        $q->setLimit(9999);
        $q->setQuery($qs);
        try {
        $index = acElasticaManager::getType('COMPTE');
        } catch(Exception $e) {
            return array();
        }
        $resset = $index->search($q);
        $results = $resset->getResults();

        $list = array();
        foreach ($results as $res) {
            $data = $res->getData()['doc'];
            $list[$data['cvi']] = sprintf("%s - %s - %s", $data['nom_a_afficher'], $data['cvi'], $data['commune']);
        }

        return $list;
    }

}
