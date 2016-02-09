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
            'declarer' => new sfWidgetFormInputCheckbox(), 
        ));
        $this->widgetSchema->setLabels(array(
            'declarer' => 'Superficie (ares):'
        ));
        $this->setValidators(array(
            'declarer' => new sfValidatorBoolean(array('required' => false))
        ));

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
    }

}
