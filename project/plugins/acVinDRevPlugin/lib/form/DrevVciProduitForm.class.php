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
            'vci_complement_dr' => new sfWidgetFormInputFloat(),
            'vci_substitution' => new sfWidgetFormInputFloat(),
            'vci_rafraichi' => new sfWidgetFormInputFloat(),
            'vci_stock_final' => new sfWidgetFormInputFloat(),
        ));
        $this->widgetSchema->setLabels(array(
            'vci_stock_initial' => "Stock avant récolte",
            'vci' => "Volume de l'année",
            'vci_destruction' => 'À détruire',
            'vci_complement_dr' => 'Complément de DR',
            'vci_substitution' => 'Substitution',
            'vci_rafraichi' => 'Rafraichi',
            'vci_stock_final' => 'Stock après récolte',
        ));
        $this->setValidators(array(
            'vci_stock_initial' => new sfValidatorNumber(array('required' => false)),
            'vci' => new sfValidatorNumber(array('required' => false)),
            'vci_destruction' => new sfValidatorNumber(array('required' => false)),
            'vci_complement_dr' => new sfValidatorNumber(array('required' => false)),
            'vci_substitution' => new sfValidatorNumber(array('required' => false)),
            'vci_rafraichi' => new sfValidatorNumber(array('required' => false)),
            'vci_stock_final' => new sfValidatorNumber(array('required' => false)),
        ));

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}
