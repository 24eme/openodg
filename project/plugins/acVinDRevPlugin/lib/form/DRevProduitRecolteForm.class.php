<?php

class DRevProduitRecolteForm extends acCouchdbObjectForm {

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getDocable()->remove();
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);
    }

    public function configure() {

        if(in_array('superficie_total', $this->getOption('fields'))) {
            $this->setWidget('superficie_total', new bsWidgetFormInputFloat());
            $this->setValidator('superficie_total', new sfValidatorNumber(array('required' => false)));
        }

        if(in_array('volume_total', $this->getOption('fields'))) {
            $this->setWidget('volume_total', new bsWidgetFormInputFloat());
            $this->setValidator('volume_total', new sfValidatorNumber(array('required' => false)));
        }

        if(in_array('recolte_nette', $this->getOption('fields'))) {
            $this->setWidget('recolte_nette', new bsWidgetFormInputFloat());
            $this->setValidator('recolte_nette', new sfValidatorNumber(array('required' => false)));
        }

        if(in_array('volume_sur_place', $this->getOption('fields'))) {
            $this->setWidget('volume_sur_place', new bsWidgetFormInputFloat());
            $this->setValidator('volume_sur_place', new sfValidatorNumber(array('required' => false)));
        }

        $dejaSaisi = false;
        foreach($this as $key => $item) {
            if($this->getObject()->get($key)) {
                $dejaSaisi = true;
            }
        }

        if ($this->getOption('disabled_dr') && $dejaSaisi) {
            foreach($this as $key => $item) {
                $this->getWidget($key)->setAttribute('readonly', 'readonly');
            }
        }

        $this->widgetSchema->setNameFormat('[%s]');
    }

}
