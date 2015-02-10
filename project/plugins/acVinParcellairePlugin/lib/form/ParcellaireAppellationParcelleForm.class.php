<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParcellaireAppellationProduitForm
 *
 * @author mathurin
 */
class ParcellaireAppellationParcelleForm extends acCouchdbObjectForm {

    private $allCepagesAppellation;

    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);        
    }

    public function configure() {
        $this->setWidgets(array(
            'superficie' => new sfWidgetFormInputFloat(),
            'cepage' => new sfWidgetFormChoice(array('multiple' => false, 'expanded' => false, 'choices' => $this->getCepagesForLieu(),
        ))));
        $this->widgetSchema->setLabels(array(
            'superficie' => 'Superficie (ares):',
            'cepage' => 'Cepage'
        ));
        $this->setValidators(array(
            'superficie' => new sfValidatorNumber(array('required' => false)),
            'cepage' => new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getCepagesForLieu())))
        ));



        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

    public function getCepagesForLieu() {
        $this->allCepagesLieu = array();
        foreach ($this->getObject()->getLieuNode()->getConfig()->getProduits() as $key => $cepage) {
            $keyCepage = str_replace('/', '-', $key);
            $libelle = $cepage->getLibelle();
            $this->allCepagesAppellation[$keyCepage] = $libelle;
        }
        return $this->allCepagesAppellation;
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $this->setDefault('cepage', $this->getObject()->getCepage()->getHashForKey());
    }

}
