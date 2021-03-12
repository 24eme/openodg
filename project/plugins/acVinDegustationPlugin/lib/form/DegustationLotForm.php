<?php
class DegustationLotForm extends acCouchdbObjectForm
{
    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        $this->setWidget('volume', new bsWidgetFormInputFloat());
        $this->setValidator('volume', new sfValidatorNumber(array('required' => true)));

        $this->widgetSchema->setNameFormat('lot_form[%s]');
    }

    public function doUpdateObject($values)
    {
        return $this->getObject()->getDocument()->modifyVolumeLot($this->getObject()->getHash(), $values['volume']);

    }
}
