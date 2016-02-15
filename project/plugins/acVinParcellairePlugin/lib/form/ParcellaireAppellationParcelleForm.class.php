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
        $type = 'active';
        $this->setWidgets(array(
            $type => new sfWidgetFormInputCheckbox(array(), array('class' => 'bsswitch', 'data-size' => 'mini', 'data-on-text' => '<span class="glyphicon glyphicon-ok-sign"></span>', 'data-off-text' => '<span class="glyphicon"></span>', 'data-on-color' => 'success' )), 
        ));
        $this->widgetSchema->setLabels(array(
            $type => 'Déclarer'
        ));
        $this->setValidators(array(
            $type => new sfValidatorBoolean(array('required' => false))
        ));

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        $values['active'] = $values['active'] * 1;
        parent::doUpdateObject($values);
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $this->setDefault('active', $this->getObject()->getActive());
    }

}
