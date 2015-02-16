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
        $this->setValidator('section', new sfValidatorString(array('required' => true), array('required' => "Aucune section saisie.")));
        $this->setValidator('numero_parcelle', new sfValidatorString(array('required' => true), array('required' => "Aucun numéro de parcelle saisi.")));

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
        foreach ($appellationNode->getConfig()->getProduits() as $key => $cepage) {
            $keyCepage = str_replace('/', '-', $key);
            $libelleCepage = $cepage->getLibelleLong();
            $couleur = $cepage->getCouleur();
            $libelleCouleur = $couleur->getLibelle();
            $lieu = $couleur->getLieu();
            $libelleLieu = $lieu->getLibelle();
            $this->allCepagesAppellation[$keyCepage] = $libelleLieu . ' ' . $libelleCouleur . ' ' . $libelleCepage;
        }
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
        if (!$this->getAppellationNode()->getConfig()->hasLieuEditable()) {
            $cepage = $values['lieuCepage'];
        } else {
            $cepage = $values['cepage'];
        }
        
        $this->getObject()->addParcelleForAppellation($this->appellationKey, $cepage, $commune, $section, $numero_parcelle);
    }

}
