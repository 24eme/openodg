<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParcellaireAjoutParcelleForm
 *
 * @author mathurin
 */
class ParcellaireAjoutParcelleForm extends acCouchdbObjectForm {

    private $appellationKey;

    public function __construct(acCouchdbJson $object, $appellationKey, $options = array(), $CSRFSecret = null) {
        $this->appellationKey = $appellationKey;
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        $appellationNode = $this->getAppellationNode();

        $hasLieuEditable = $appellationNode->getConfig()->hasLieuEditable();
        $produits = $this->getProduits();
        $this->setWidget('commune', new sfWidgetFormInput());
        $this->setWidget('section', new sfWidgetFormInput());
        $this->setWidget('numero_parcelle', new sfWidgetFormInput());

        if (!$hasLieuEditable) {
            $this->setWidget('lieuCepage', new sfWidgetFormChoice(array('choices' => $produits)));
        } else {
            $this->setWidget('cepage', new sfWidgetFormChoice(array('choices' => $produits)));
            $this->setWidget('lieuDit', new sfWidgetFormInput());
        }

        $this->widgetSchema->setLabel('commune', 'Commune :');
        $this->widgetSchema->setLabel('section', 'Section :');
        $this->widgetSchema->setLabel('numero_parcelle', 'Numéro parcelle :');
        if (!$hasLieuEditable) {
            $this->widgetSchema->setLabel('lieuCepage', 'Lieu/cépage :');
        } else {
            $this->widgetSchema->setLabel('lieuDit', 'Lieu Dit:');
            $this->widgetSchema->setLabel('cepage', 'Cépage :');
        }

        $this->setValidator('commune', new sfValidatorString(array('required' => true), array('required' => "Aucune commune saisie.")));
        $this->setValidator('section', new sfValidatorRegex(array("required" => true, "pattern" => "/^[0-9A-Z]+$/"), array("invalid" => "La section doit être composée de numéro et lettres en majuscules")));
        $this->setValidator('numero_parcelle',new sfValidatorRegex(array("required" => true, "pattern" => "/^[0-9]+$/"), array("invalid" => "Le numéro doit être un nombre")));

        if (!$hasLieuEditable) {
            $this->setValidator('lieuCepage', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)), array('required' => "Aucun cépage saisie.")));
        } else {
            $this->setValidator('cepage', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)), array('required' => "Aucun cépage saisie.")));
            $this->setValidator('lieuDit', new sfValidatorString(array('required' => true)));
        }
        $this->widgetSchema->setNameFormat('parcellaire_ajout_parcelle[%s]');
    }

    public function getAppellationNode() {

        return $this->getObject()->getAppellationNodeFromAppellationKey($this->appellationKey, true);
    }

    public function getProduits() {
        $appellationNode = $this->getAppellationNode();

        $this->allCepagesAppellation = array();
        foreach ($appellationNode->getConfig()->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_PARCELLAIRE) as $key => $cepage) {
            $keyCepage = str_replace('/', '-', $key);
            $libelleCepage = $cepage->getLibelleLong();
            $lieu = $cepage->getCouleur()->getLieu();
            $libelleLieu = $lieu->getLibelle();
            $this->allCepagesAppellation[$keyCepage] = trim($libelleLieu . ' ' . $libelleCepage);
        }
        $this->allCepagesAppellation = array_merge(array('' => ''), $this->allCepagesAppellation);
        return $this->allCepagesAppellation;
    }

    protected function doUpdateObject($values) {

        if ((!isset($values['commune']) || empty($values['commune'])) ||
                (!isset($values['section']) || empty($values['section'])) ||
                (!isset($values['numero_parcelle']) || empty($values['numero_parcelle']))
        ) {
            return;
        }

        $commune = $values['commune'];
        $section = $values['section'];
        $numero_parcelle = $values['numero_parcelle'];
        $lieu = null;
        if (!$this->getAppellationNode()->getConfig()->hasLieuEditable()) {
            $cepage = $values['lieuCepage'];
        } else {
            $cepage = $values['cepage'];
            $lieu = $values['lieuDit'];
        }

        $this->getObject()->addParcelleForAppellation($this->appellationKey, $cepage, $commune, $section, $numero_parcelle, $lieu);
    }

    public function getLieuDetailForAutocomplete() {
        $lieuxDetail = array();
        foreach ($this->getAppellationNode()->getProduits() as $cepageKey => $cepage) {
            foreach ($cepage->detail as $keyDetail => $detail) {
                if ($detail->exist('lieu') && $detail->lieu) {
                    $lieuxDetail[] = $detail->lieu;
                }
            }
        }
        $entries = array();
        foreach(array_unique($lieuxDetail) as $lieu) {
            $entry = new stdClass();
            $entry->id = trim($lieu);
            $entry->text = trim($lieu);                    
            $entries[] = $entry;
        }
        return $entries;
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
    }
    

}
