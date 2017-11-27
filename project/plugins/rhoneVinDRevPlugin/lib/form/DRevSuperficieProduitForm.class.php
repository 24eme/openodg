<?php

class DRevSuperficieProduitForm extends acCouchdbObjectForm {

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);
        $this->getDocable()->remove();
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $this->setDefault('has_stock_vci',  $this->getObject()->hasVci());
    }

    public function configure() {
        $this->setWidgets(array(
            'superficie_revendique' => new bsWidgetFormInputFloat(),
            'has_stock_vci' => new sfWidgetFormInputCheckbox(),
        ));

        $this->setValidators(array(
            'superficie_revendique' => new sfValidatorNumber(array('required' => false)),
            'has_stock_vci' => new sfValidatorBoolean(array('required' => false)),
        ));
        $this->embedForm('recolte', new DRevProduitRecolteForm($this->getObject()->recolte, array_merge($this->getOptions(), array('fields' => array('superficie_total')))));

        if($this->getObject()->hasVci(true)) {
            $this->getWidget('has_stock_vci')->setAttribute('disabled', 'disabled');
        }

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        if ($this->getOption('disabled_dr')) {
            foreach($this->getEmbeddedForm('recolte')->getWidgetSchema()->getFields() as $key => $item) {
                if(!$item->getAttribute('disabled')) {
                    continue;
                }
                unset($values['recolte'][$key]);
            }
        }

        parent::doUpdateObject($values);

        if($values['has_stock_vci'] && !$this->getObject()->hasVci()) {
            $this->getObject()->vci->stock_precedent = 0;
        }
        if(!$values['has_stock_vci']) {
        	$this->getObject()->vci->stock_precedent = null;
        }
    }

}
