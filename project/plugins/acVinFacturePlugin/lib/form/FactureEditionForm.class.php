<?php

class FactureEditionForm extends acCouchdbObjectForm {

    public function configure()
    {
        //$this->getObject()->lignes->add();
        $this->embedForm('lignes', new FactureEditionLignesForm($this->getObject()->lignes));

        $this->widgetSchema->setNameFormat('facture_edition[%s]');
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);

        $this->getObject()->updateTotaux();
    }

}
