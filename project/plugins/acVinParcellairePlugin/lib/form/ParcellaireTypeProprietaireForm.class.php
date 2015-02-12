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
class ParcellaireTypeProprietaireForm extends acCouchdbObjectForm {

    public function configure() {
        $this->disableCSRFProtection();
        $typesProprietaire = $this->getTypesProprietaire();
        $vendeurs = $this->getAllVendeurs();

        $this->setWidget('type_proprietaire', new sfWidgetFormChoice(array('multiple' => true, 'expanded' => true, 'choices' => $typesProprietaire)));
        $this->getWidget('type_proprietaire')->setLabel("type_proprietaire", "Type proprietaire:");
        $this->setValidator('type_proprietaire', new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys($typesProprietaire)), array('required' => "Aucun type de propriétaire n'a été choisi.")));


        $this->setWidget('acheteurs_select', new sfWidgetFormChoice(array('multiple' => true, 'choices' => $vendeurs)));
        $this->getWidget('acheteurs_select')->setLabel("acheteurs_select");
        $this->setValidator('acheteurs_select', new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys($vendeurs)), array()));

        $this->widgetSchema->setNameFormat('parcellaire_type_proprietaire[%s]');
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();

        $this->widgetSchema['acheteurs_select']->setDefault(array_keys($this->getDefaultsAcheteurs()));
    }

    public function getDefaultsAcheteurs() {
        $default_acheteurs = array();
        
        foreach($this->getObject()->acheteurs as $acheteur) {
            $default_acheteurs[$acheteur->getKey()] = sprintf("%s (%s)", $acheteur->nom, $acheteur->cvi);
        }

        return $default_acheteurs;
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);

        $acheteurs_to_delete = array();
        $values_acheteurs = (isset($values['acheteurs_select']) && is_array($values['acheteurs_select'])) ? $values['acheteurs_select'] : array();

        foreach($this->getObject()->acheteurs as $acheteur) {
            if(!in_array($acheteur->getKey(), $values_acheteurs) && !count($acheteur->produits)) {
                $acheteurs_to_delete[] = $acheteur->getKey();
            }
        }

        foreach($acheteurs_to_delete as $cvi) {
            $this->getObject()->acheteurs->remove($cvi);
        }
        
        foreach($values_acheteurs as $cvi) {
            $this->getObject()->addAcheteurNode($cvi);
        }
    }

    public function getTypesProprietaire() {
        return ParcellaireClient::$type_proprietaire_libelles;
    }

    public function getAllVendeurs() {
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
            $list[$data['cvi']] = $data['nom_a_afficher'];
        }

        return $list + $this->getDefaultsAcheteurs();
    }

}
