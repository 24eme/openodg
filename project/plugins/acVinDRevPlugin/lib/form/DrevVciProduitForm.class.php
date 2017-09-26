<?php

class DRevVciProduitForm extends acCouchdbObjectForm {

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getDocable()->remove();
    }
    public function configure() {
        $this->setWidgets(array(
            'vci_stock_initial' => new bsWidgetFormInputFloat(),
            'vci' => new bsWidgetFormInputFloat(),
            'vci_destruction' => new bsWidgetFormInputFloat(),
            'vci_substitution' => new bsWidgetFormInputFloat(),
            'vci_rafraichi' => new bsWidgetFormInputFloat(),
        ));
        $this->setValidators(array(
            'vci_stock_initial' => new sfValidatorNumber(array('required' => false)),
            'vci' => new sfValidatorNumber(array('required' => false)),
            'vci_destruction' => new sfValidatorNumber(array('required' => false)),
            'vci_substitution' => new sfValidatorNumber(array('required' => false)),
            'vci_rafraichi' => new sfValidatorNumber(array('required' => false)),
        ));
        
        if($this->getObject()->detail->vci > 0) {
        	$this->getWidget('vci')->setAttribute('readonly', 'readonly');
        }

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}
