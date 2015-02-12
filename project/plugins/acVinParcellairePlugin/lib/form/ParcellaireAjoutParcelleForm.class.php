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
        $produits = $this->getProduits();
        $this->setWidgets(array(
            'commune' => new sfWidgetFormInput(),
            'section' => new sfWidgetFormInput(),
            'numero_parcelle' => new sfWidgetFormInput(),
            'cepage' => new sfWidgetFormChoice(array('choices' => $produits)),
            'superficie' => new sfWidgetFormInputFloat(),
        ));
        $this->widgetSchema->setLabels(array(
            'commune' => 'Commune :',
            'section' => 'Section :',
            'numero_parcelle' => 'Numéro parcelle :',
            'cepage' => 'Lieu/cépage :',
            'superficie' => 'Superficie (ares):',
        ));

        $this->setValidators(array(
            'commune' => new sfValidatorString(array('required' => true), array('required' => "Aucune commune saisie.")),
            'section' => new sfValidatorString(array('required' => true), array('required' => "Aucune section saisie.")),
            'numero_parcelle' => new sfValidatorString(array('required' => true), array('required' => "Aucun numéro de parcelle saisi.")),
            'cepage' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)), array('required' => "Aucun cépage saisie.")),
            'superficie' => new sfValidatorNumber(array('required' => false)),
        ));



        $this->widgetSchema->setNameFormat('parcellaire_ajout_parcelle[%s]');
    }

    public function getProduits() {
        $appellationNode = $this->getObject()->getAppellationNodeFromAppellationKey($this->appellationKey);
        $this->allCepagesAppellation = array();
        foreach ($appellationNode->getConfig()->getProduits() as $key => $cepage) {
            $keyCepage = str_replace('/', '-', $key);
            $libelleCepage = $cepage->getLibelle();
            $couleur = $cepage->getCouleur();
            $libelleCouleur = $couleur->getLibelle();
            $lieu = $couleur->getLieu();
            $libelleLieu = $lieu->getLibelle();
            $this->allCepagesAppellation[$keyCepage] = $libelleLieu.' '.$libelleCouleur.' '.$libelleCepage;
        }
        return $this->allCepagesAppellation;
    }

    protected function doUpdateObject($values) {
        if ((!isset($values['commune']) || empty($values['commune'])) ||
                (!isset($values['section']) || empty($values['section'])) ||
                (!isset($values['numero_parcelle']) || empty($values['numero_parcelle'])) ||
                (!isset($values['cepage']) || empty($values['cepage']))
        ) {
            return;
        }

        $commune = $values['commune'];
        $section = $values['section'];
        $numero_parcelle = $values['numero_parcelle'];
        $cepage = $values['cepage'];
        $superficie = (!isset($values['superficie']) || $values['superficie'])? $values['superficie'] : 0;
        $this->getObject()->addParcelleForAppellation($this->appellationKey, $commune, $section, $numero_parcelle,$cepage,$superficie);
    }

}
