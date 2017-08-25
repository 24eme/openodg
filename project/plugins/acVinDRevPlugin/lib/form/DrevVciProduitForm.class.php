<?php

class DRevVciProduitForm extends acCouchdbObjectForm {

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getDocable()->remove();
    }
    public function configure() {
        $this->setWidgets(array(
            'vci' => new sfWidgetFormInputFloat(),
            'vci_destruction' => new sfWidgetFormInputFloat(),
            'vci_complement_dr' => new sfWidgetFormInputFloat(),
        ));
        $this->widgetSchema->setLabels(array(
            'vci' => "Volume VCI de l'année",
            'vci_destruction' => 'VCI à détruire',
            'vci_complement_dr' => 'VCI en complément de DR',
        ));
        $this->setValidators(array(
            'vci' => new sfValidatorNumber(array('required' => false)),
            'vci_destruction' => new sfValidatorNumber(array('required' => false)),
            'vci_complement_dr' => new sfValidatorNumber(array('required' => false)),
        ));

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}
