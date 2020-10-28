<?php
class DegustationLotForm extends acCouchdbObjectForm
{
    protected $drev = null;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);

        $this->drev = DRevClient::getInstance()->find($object->id_document);
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

        $modificatrice = $this->drev->generateModificative();
        $modificatrice->addLotFromDegustation($this->object);
        $modificatrice->generateMouvementsLots();

        $mvmt = $this->drev->get($this->object->origine_mouvement);
        $mvmt->prelevable = 0;

        $this->drev->save();
        $modificatrice->save();
    }
}
