<?php
class DegustationLotForm extends acCouchdbObjectForm
{
    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        $this->setWidget('volume', new bsWidgetFormInputFloat());
        $this->setValidator('volume', new sfValidatorNumber(array('required' => true)));

        $this->setWidget('numero', new bsWidgetFormInput());
        $this->setValidator('numero', new sfValidatorString(array('required' => false)));

        $this->widgetSchema->setNameFormat('lot_form[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);

    }
}
