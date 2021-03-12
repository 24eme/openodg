<?php

class DegustationPreleveUpdateLogementForm extends acCouchdbObjectForm
{
    private $lot = null;
    private $lots = [];
    private $degustation = null;

    public function __construct(acCouchdbJson $object, $lot, $options = [], $CSRFSecret = null)
    {
        $this->degustation = $object;
        $this->lot = $lot;
        $this->lots = $object->getLots();

        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure()
    {
        $lot = $this->lots->get($this->lot);

        $this->setWidget('lot_'.$this->lot, new sfWidgetFormInputText());
        $this->setValidator('lot_'.$this->lot, new sfValidatorString());

        $this->widgetSchema->setNameFormat('update_logement[%s]');
    }

    public function doUpdateObject($values)
    {
        parent::doUpdateObject($values);

        $this->degustation->updateLotLogement($this->lots->get($this->lot), $values['lot_'.$this->lot]);
    }
}
