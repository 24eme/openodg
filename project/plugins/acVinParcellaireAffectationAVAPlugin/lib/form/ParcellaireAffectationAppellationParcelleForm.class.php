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
class ParcellaireAffectationAppellationParcelleForm extends acCouchdbObjectForm {

    private $allCepagesAppellation;

    public function __construct(\acCouchdbJson $object, $appellationKey, $options = array(), $CSRFSecret = null) {
        $this->appellationKey = $appellationKey;
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        $checkarray = array('class' => 'switch');
        if ($this->appellationKey == ParcellaireAffectationClient::APPELLATION_VTSGN) {
            $type = 'vtsgn';
        }else{
            $type = 'active';
            if ($this->getObject()->vtsgn) {
                $checkarray['data-disabled'] = 'true';
                $checkarray['onclick'] = 'return false';
                $checkarray['data-toggle'] = 'tooltip';
                $checkarray['title'] = "Cette parcelle est affectée en VT/SGN, elle n'est donc pas activable ici";
            }
        }
        $this->setWidgets(array(
            $type => new sfWidgetFormInputCheckbox(array(), $checkarray),
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
        if (isset($values['vtsgn']) && strpos($this->getObject()->getAppellation()->libelle, 'Alsace blanc') !== false) {
            $values['active'] = $values['vtsgn'];
        }
        parent::doUpdateObject($values);
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
    }

}
