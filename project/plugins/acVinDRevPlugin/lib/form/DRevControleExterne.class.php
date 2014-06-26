<?php

class DRevControleExterneForm extends acCouchdbObjectForm
{

    public function configure() {
        $form_alsace = new DRevPrelevementForm($this->getObject()->getDocument()->addPrelevement(Drev::BOUTEILLE_ALSACE));
        $form_grdcru = new DRevPrelevementForm($this->getObject()->getDocument()->addPrelevement(Drev::BOUTEILLE_GRDCRU));
        $form_vtsgn = new DRevPrelevementForm($this->getObject()->getDocument()->addPrelevement(Drev::BOUTEILLE_VTSGN));

        $this->embedForm(Drev::BOUTEILLE_ALSACE, $form_alsace);
        $this->embedForm(Drev::BOUTEILLE_GRDCRU, $form_grdcru);
        $this->embedForm(Drev::BOUTEILLE_VTSGN, $form_vtsgn);

        $this->widgetSchema->setNameFormat('controle_externe[%s]');
    }

    public function doUpdateObject($values) 
    {
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
            $embedForm->doUpdateObject($values[$key]);
        }
    }
}