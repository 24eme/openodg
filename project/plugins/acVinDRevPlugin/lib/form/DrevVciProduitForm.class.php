<?php

class DRevVciProduitForm extends acCouchdbObjectForm {

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getDocable()->remove();
    }
    public function configure() {
        $this->setWidgets(array(
            'vci_stock_initial' => new sfWidgetFormInputFloat(),
            'vci' => new sfWidgetFormInputFloat(),
            'vci_destruction' => new sfWidgetFormInputFloat(),
            'vci_substitution' => new sfWidgetFormInputFloat(),
            'vci_rafraichi' => new sfWidgetFormInputFloat(),
        ));
        $this->setValidators(array(
            'vci_stock_initial' => new sfValidatorNumber(array('required' => false)),
            'vci' => new sfValidatorNumber(array('required' => false)),
            'vci_destruction' => new sfValidatorNumber(array('required' => false)),
            'vci_substitution' => new sfValidatorNumber(array('required' => false)),
            'vci_rafraichi' => new sfValidatorNumber(array('required' => false)),
        ));

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}
