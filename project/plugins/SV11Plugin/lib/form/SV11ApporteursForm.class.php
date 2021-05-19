<?php

class SV11ApporteursForm extends BaseForm {

    protected $apporteurs = null;
    protected $sv11 = null;

    public function __construct($sv11)
    {
        $this->sv11 = $sv11;
        $this->apporteurs = $sv11->getApporteurs();
        $defaults = array();
        foreach($this->apporteurs as $apporteur) {
            $defaults[$apporteur->_id] = 1;
        }
        parent::__construct($defaults);
    }

    public function configure() {
        foreach($this->apporteurs as $apporteur) {
            $this->setWidget($apporteur->_id, new bsWidgetFormInputCheckbox());
            $this->setValidator($apporteur->_id, new ValidatorBoolean());
        }
        $this->widgetSchema->setNameFormat('sv11_apporteurs[%s]');
    }

    public function save() {
        $coop = $this->sv11->getEtablissementObject();

        foreach($this->getValues() as $id => $check) {
            if(!$check) {
                continue;
            }

            $apporteur = EtablissementClient::getInstance()->find($id);
            $apporteur->addLiaison(EtablissementClient::TYPE_LIAISON_COOPERATIVE, $coop);
            $apporteur->save();
        }
    }
}
