<?php

class DegustationCourrierForm extends acCouchdbObjectForm {

    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->updateDefaults();
    }

    public function configure() {
        foreach ($this->getObject()->getNotes() as $note) {
            $this->setWidget($note->prelevement->getHashForKey(), new sfWidgetFormChoice(array('choices' => $this->getTypesCourrier())));
            $this->setValidator($note->prelevement->getHashForKey(), new sfValidatorChoice(array('choices' => array_keys($this->getTypesCourrier()), 'required' => false)));
        }
        $this->widgetSchema->setNameFormat('degustation_courrier[%s]');
    }

    protected function updateDefaults() {
        foreach ($this->getObject()->getNotes() as $note) {
            if ($this->getObject()->get($note->prelevement->getHash())->exist('type_courrier')) {
                $this->setDefault($note->prelevement->getHashForKey(), $this->getObject()->get($note->prelevement->getHash())->type_courrier);
            }
        }
    }

    public function getTypesCourrier() {
        return array_merge(array("" => ""), DegustationClient::$types_courrier_libelle);
    }

    public function update() {
        $values = $this->values;
        foreach ($values as $key => $value) {
            if (preg_match('/^\-operateurs.*/', $key) && $value) {
                $realKey = str_replace('-', '/', $key);
                $this->getObject()->getOrAdd($realKey)->add('type_courrier', $value);
            }
        }
    }

}
