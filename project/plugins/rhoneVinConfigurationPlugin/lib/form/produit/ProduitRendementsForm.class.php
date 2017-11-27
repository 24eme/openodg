<?php
class ProduitRendementsForm extends acCouchdbObjectForm {

    public function configure() {
            $this->setWidget('rendement', new bsWidgetFormInput());
            $this->setWidget('rendement_vci', new bsWidgetFormInput());
            $this->setWidget('rendement_vci_total', new bsWidgetFormInput());

            $this->getWidget('rendement')->setLabel("Rendement :");
            $this->getWidget('rendement_vci')->setLabel("Rendement VCI :");
            $this->getWidget('rendement_vci_total')->setLabel("Rendement VCI Total :");

            $this->setValidator('rendement', new sfValidatorNumber(array('required' => false), array('required' => 'Champ obligatoire')));
            $this->setValidator('rendement_vci', new sfValidatorNumber(array('required' => false), array('required' => 'Champ obligatoire')));
            $this->setValidator('rendement_vci_total', new sfValidatorNumber(array('required' => false), array('required' => 'Champ obligatoire')));
        $this->widgetSchema->setNameFormat('%s');
    }
    
   
}