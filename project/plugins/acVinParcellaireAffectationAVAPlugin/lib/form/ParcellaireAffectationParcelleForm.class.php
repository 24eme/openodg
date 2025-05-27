<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParcellaireAffectationAjoutParcelleForm
 *
 * @author mathurin
 */
abstract class ParcellaireAffectationParcelleForm extends acCouchdbObjectForm {

    public function configure() {
        $appellationNode = $this->getAppellationNode();

        $hasLieuEditable = $appellationNode->getConfig()->hasLieuEditable();
        $produits = $this->getProduits();
        $communes = $this->getCommunes();
        $this->setWidget('commune', new sfWidgetFormChoice(array('choices' => $communes), array("placeholder" => "Saisissez une commune", "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)));
        $this->setWidget('section', new sfWidgetFormInput());
        $this->setWidget('numero_parcelle', new sfWidgetFormInput());

        if (!$hasLieuEditable) {
            $libelleLieuDitCepage = "Saisissez un lieu-dit/cépage";
            if($this->getAppellationNode()->getKey() == ParcellaireAffectationClient::APPELLATION_VTSGN){
                $libelleLieuDitCepage = "Saisissez un cépage";
            }
            $this->setWidget('lieuCepage', new sfWidgetFormChoice(array('choices' => $produits), array("placeholder" => $libelleLieuDitCepage, "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)));
        } else {
            $this->setWidget('cepage', new sfWidgetFormChoice(array('choices' => $produits)));
            $this->setWidget('lieuDit', new sfWidgetFormInput());
            $this->setWidget('lieuDitCadastral', new sfWidgetFormInput());
        }

        $this->widgetSchema->setLabel('commune', 'Commune :');
        $this->widgetSchema->setLabel('section', 'Section :');
        $this->widgetSchema->setLabel('numero_parcelle', 'Numéro :');
        if (!$hasLieuEditable) {
            if($this->getAppellationNode()->getKey() == 'appellation_'.ParcellaireAffectationClient::APPELLATION_ALSACEBLANC){
                    $this->widgetSchema->setLabel('lieuCepage', 'Cépage :');
            }else{

            $this->widgetSchema->setLabel('lieuCepage', 'Lieu-dit/cépage :');
            }
        } else {
            $this->widgetSchema->setLabel('lieuDit', 'Lieu-dit revendiqué:');
            $this->widgetSchema->setLabel('lieuDitCadastral', 'Lieu-dit cadastral:');
            $this->widgetSchema->setLabel('cepage', 'Cépage :');
        }

        $this->setValidator('commune', new sfValidatorChoice(array('required' => true,'choices' => array_keys($communes)), array('required' => "Aucune commune saisie.")));
        $this->setValidator('section', new sfValidatorRegex(array("required" => true, "pattern" => "/^[0-9A-Z]+$/"), array("invalid" => "La section doit être composée de numéro et lettres en majuscules")));
        $this->setValidator('numero_parcelle',new sfValidatorRegex(array("required" => true, "pattern" => "/^[0-9]+[A-Za-z]*$/"), array("invalid" => "La section doit être composée d'un numéro")));

        if (!$hasLieuEditable) {
            $this->setValidator('lieuCepage', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)), array('required' => "Aucun cépage saisie.")));
        } else {
            $this->setValidator('cepage', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)), array('required' => "Aucun cépage saisie.")));
            $this->setValidator('lieuDit', new sfValidatorString(array('required' => true)));
            $this->setValidator('lieuDitCadastral', new sfValidatorString(array('required' => false)));
        }

        $this->setWidget('superficie', new sfWidgetFormInputFloat(array('float_format' => '%01.2f')));
        $this->setValidator('superficie', new sfValidatorNumber(array('required' => true, 'min' => '0.01'), array('min' => 'La superficie doit être supérieure à 0')));

        $this->widgetSchema->setNameFormat('parcellaire_parcelle[%s]');
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();

        if($this->getObject() instanceof ParcellaireAffectationCepageDetail) {
            $this->setDefault('superficie', $this->getObject()->getSuperficie(ParcellaireClient::PARCELLAIRE_SUPERFICIE_UNIT_ARE));
        }
    }

    public function getProduits() {
        $appellationNode = $this->getAppellationNode();
        $this->allCepagesAppellation = array();

            foreach ($appellationNode->getConfig()->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_PARCELLAIRE) as $key => $cepage) {

            	if ($this->getAppellationNode()->getKey() == 'appellation_'.ParcellaireAffectationClient::APPELLATION_ALSACEBLANC && !$cepage->hasVtsgn()) {
            		continue;
            	}
                $keyCepage = str_replace('/', '-', $key);
                $libelleCepage = $cepage->getLibelleLong();
                $lieu = $cepage->getCouleur()->getLieu();
                $libelleLieu = $lieu->getLibelle();
                $this->allCepagesAppellation[$keyCepage] = trim($libelleLieu . ' ' . $libelleCepage);
            }

        asort($this->allCepagesAppellation);
        $this->allCepagesAppellation = array_merge(array('' => ''), $this->allCepagesAppellation);
        return $this->allCepagesAppellation;
    }

    public function getCommunes() {
       $config = $this->getObject()->getDocument()->getConfiguration();
       $communes = array();

       foreach($config->communes as $communeName => $dpt) {
       $communes[strtoupper($communeName)] = $communeName;
       }

       if($this->getObject()->exist('commune') && $this->getObject()->commune)  {
           $communes[$this->getObject()->commune] = $this->getObject()->commune;
       }

       return array_merge(array('' => ''), $communes);
    }

    protected function mustCreateNew() {
        return !($this->getObject() instanceof ParcellaireAffectationCepageDetail) || $this->getObject()->getParcelleId();
    }

    protected function doUpdateObject($values) {

        if ((!isset($values['commune']) || empty($values['commune'])) ||
                (!isset($values['section']) || empty($values['section'])) ||
                (!isset($values['numero_parcelle']) || empty($values['numero_parcelle']))
        ) {
            return;
        }

        $config = $this->getObject()->getDocument()->getConfiguration();
        $commune = $values['commune'];
        $section = preg_replace('/^0*/','',$values['section']);
        $numero_parcelle = preg_replace('/^0*/','',$values['numero_parcelle']);
        $lieu = null;
        $dpt = null;
        if($this->getObject()->exist('departement')) {
            $dpt = $this->getObject()->departement;
        }
        if($config->communes->exist($commune)) {
            $dpt = $config->communes[$commune];
        }

        if (!$this->getAppellationNode()->getConfig()->hasLieuEditable()) {
            $cepage = $values['lieuCepage'];
        } else {
            $cepage = $values['cepage'];
            $lieu = $values['lieuDit'];
        }

        $isKeyModified = ($this->getObject()->getCepage()->getHash() != str_replace('-', '/', $cepage) || $this->getObject()->commune != $commune || $this->getObject()->section != $section || $this->getObject()->numero_parcelle != $numero_parcelle || $this->getObject()->lieu != $lieu);

        if($this->mustCreateNew() || $isKeyModified) {
            $parcelle = $this->getObject()->getDocument()->addParcelleForAppellation($this->getAppellationNode()->getKey(), $cepage, $commune, $section, $numero_parcelle, $lieu, $dpt);
        } else {
            $parcelle = $this->getObject();
        }

        if(isset($values['lieuDitCadastral']) && $values['lieuDitCadastral'] != $parcelle->getLieuDitCadastral()) {
            $parcelle->add('lieu_cadastral', $values['lieuDitCadastral']);
        }

        $parcelle->superficie = round($values['superficie'] / 100, 4);

        $parcelle->active = 1;
        if ($this->getAppellationNode()->getKey() == 'appellation_'.ParcellaireAffectationClient::APPELLATION_ALSACEBLANC) {
        	$parcelle->vtsgn = 1;
        }

        if($this->getObject() instanceof ParcellaireAffectationCepageDetail && $this->getObject()->getHash() != $parcelle->getHash()) {
            $this->getObject()->active = 0;
        }
    }

    public function getLieuDetailForAutocomplete() {
        $lieuxDetail = array();
        foreach ($this->getAppellationNode()->getLieuxEditable() as $libelle) {
        	$lieuxDetail[] = $libelle;
        }
        $entries = array();
        foreach ($lieuxDetail as $lieu) {
        	$entry = new stdClass();
            $entry->id = trim($lieu);
            $entry->text = trim($lieu);
            $entries[] = $entry;
        }
        sort($entries);
        return $entries;
    }

    public function getLieuCadastralForAutocomplete() {
        $lieuCadastralDetail = array();
        foreach ($this->getObject()->getDocument()->getParcellaire()->getDeclarationParcelles() as $libelle) {
            $lieuCadastralDetail[] = $libelle->getLieu();
        }
        $entries = array();
        foreach (array_unique($lieuCadastralDetail) as $lieuCadastral) {
            $entry = new stdClass();
            $entry->id = trim($lieuCadastral);
            $entry->text = trim($lieuCadastral);
            $entries[] = $entry;
        }
        sort($entries);
        return $entries;
    }

}
