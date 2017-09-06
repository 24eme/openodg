<?php

class DRevRevendicationProduitDRForm extends acCouchdbObjectForm {

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getDocable()->remove();
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);
    }

    public function configure() {

        $this->setWidgets(array(
            'superficie_total' => new sfWidgetFormInputFloat(),
            'volume_total' => new sfWidgetFormInputFloat(),
            'volume_sur_place' => new sfWidgetFormInputFloat(),
            'recolte_nette' => new sfWidgetFormInputFloat(),
        ));

        $this->setValidators(array(
            'superficie_total' => new sfValidatorNumber(array('required' => false)),
            'volume_total' => new sfValidatorNumber(array('required' => false)),
            'volume_sur_place' => new sfValidatorNumber(array('required' => false)),
            'recolte_nette' => new sfValidatorNumber(array('required' => false)),
        ));

        $this->widgetSchema->setNameFormat('[%s]');
    }

}
