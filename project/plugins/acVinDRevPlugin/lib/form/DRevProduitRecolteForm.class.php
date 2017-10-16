<?php

class DRevProduitRecolteForm extends acCouchdbObjectForm {

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getDocable()->remove();
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);
    }

    public function configure() {

        $this->setWidgets(array(
            'superficie_total' => new bsWidgetFormInputFloat(),
            'volume_total' => new bsWidgetFormInputFloat(),
            'recolte_nette' => new bsWidgetFormInputFloat(),
            'volume_sur_place' => new bsWidgetFormInputFloat(),
        ));

        $this->setValidators(array(
            'superficie_total' => new sfValidatorNumber(array('required' => false)),
            'volume_total' => new sfValidatorNumber(array('required' => false)),
            'recolte_nette' => new sfValidatorNumber(array('required' => false)),
            'volume_sur_place' => new sfValidatorNumber(array('required' => false)),
        ));

        if ($this->getOption('disabled_dr') && ($this->getObject()->superficie_total || $this->getObject()->volume_total || $this->getObject()->recolte_nette || $this->getObject()->volume_sur_place)) {
        	$this->getWidget('superficie_total')->setAttribute('readonly', 'readonly');
        	$this->getWidget('volume_total')->setAttribute('readonly', 'readonly');
        	$this->getWidget('recolte_nette')->setAttribute('readonly', 'readonly');
        	$this->getWidget('volume_sur_place')->setAttribute('readonly', 'readonly');
        }

        $this->widgetSchema->setNameFormat('[%s]');
    }

}
