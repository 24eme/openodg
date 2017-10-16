<?php

class DRevVciProduitForm extends acCouchdbObjectForm {

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getDocable()->remove();
    }
    public function configure() {
        $this->setWidgets(array(
            'stock_precedent' => new bsWidgetFormInputFloat(),
            'destruction' => new bsWidgetFormInputFloat(),
            'complement' => new bsWidgetFormInputFloat(),
            'substitution' => new bsWidgetFormInputFloat(),
            'rafraichi' => new bsWidgetFormInputFloat(),
        ));
        $this->setValidators(array(
            'stock_precedent' => new sfValidatorNumber(array('required' => false)),
            'destruction' => new sfValidatorNumber(array('required' => false)),
            'complement' => new sfValidatorNumber(array('required' => false)),
            'substitution' => new sfValidatorNumber(array('required' => false)),
            'rafraichi' => new sfValidatorNumber(array('required' => false)),
        ));

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}
