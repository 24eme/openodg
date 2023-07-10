<?php

class DegustationLotsForm extends acCouchdbForm
{
    public function configure()
    {
        $formLots = new BaseForm();

        foreach ($this->getDocument()->lots as $lot) {
            $formLots->embedForm($lot->getKey(), new LotForm($lot));
        }

        $this->embedForm('lots', $formLots);
        $this->widgetSchema->setNameFormat('degustation_lots[%s]');
    }

    public function save() {
        $values = $this->getValues();
        foreach ($this->getEmbeddedForm('lots')->getEmbeddedForms() as $key => $embedForm) {
            $embedForm->doUpdateObject($values['lots'][$key]);
        }
        $this->getDocument()->save();
    }
}
