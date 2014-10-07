<?php

class DRevControleExterneForm extends acCouchdbObjectForm
{

    public function configure() {
        if(($this->getObject()->getDocument()->prelevements->exist(Drev::BOUTEILLE_ALSACE))) {
            $form_alsace = new DRevPrelevementForm($this->getObject()->getDocument()->prelevements->get(Drev::BOUTEILLE_ALSACE));
            $this->embedForm(Drev::BOUTEILLE_ALSACE, $form_alsace);
        }

        if(($this->getObject()->getDocument()->prelevements->exist(Drev::BOUTEILLE_GRDCRU))) {
            $form_grdcru = new DRevPrelevementForm($this->getObject()->getDocument()->prelevements->get(Drev::BOUTEILLE_GRDCRU));
            $this->embedForm(Drev::BOUTEILLE_GRDCRU, $form_grdcru);
        }

        $form_vtsgn = new DRevPrelevementForm($this->getObject()->getDocument()->addPrelevement(Drev::BOUTEILLE_VTSGN));

        $form_vtsgn->setWidget('total_lots', new sfWidgetFormInputText());
        $form_vtsgn->setValidator('total_lots', new sfValidatorNumber(array('required' => false)));
        $form_vtsgn->getWidget('total_lots')->setLabel("Nombre de lots");

        $this->embedForm(Drev::BOUTEILLE_VTSGN, $form_vtsgn);

        if(count($this->getObject()->getDocument()->getEtablissementObject()->chais) > 1) {
            $this->setWidget("chai", new sfWidgetFormChoice(array('choices' => $this->getChaiChoice())));    
            $this->setValidator("chai", new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getChaiChoice()))));
        }

        $this->widgetSchema->setNameFormat('controle_externe[%s]');
    }

    public function getChaiChoice() {
        $choices = array();
        foreach($this->getObject()->getDocument()->getEtablissementObject()->chais as $chai) {
            $choices[$chai->getKey()] = sprintf("%s %s %s", $chai->adresse, $chai->code_postal, $chai->commune);
        }

        return $choices;
    }

    public function doUpdateObject($values) 
    {
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
            $embedForm->doUpdateObject($values[$key]);
        }

        if(isset($values["chai"])) {
            $this->getObject()->getDocument()->chais->set(DRev::BOUTEILLE, $this->getObject()->getDocument()->getEtablissementObject()->chais->get($values["chai"])->toArray(true, false));
        }
    }
}