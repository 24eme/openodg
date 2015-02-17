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
            'superficie' => new sfWidgetFormInputFloat(array('float_format' => '%01.4f'))
        ));
        $this->widgetSchema->setLabels(array(
            'superficie' => 'Superficie (ares):'
        ));
        $this->setValidators(array(
            'superficie' => new sfValidatorNumber(array('required' => false))
        ));

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        if (!$this->getObject()->exist('superficie') || !$this->getObject()->superficie) {
            $this->setDefault('superficie', '0.0000');
        }
    }

}
