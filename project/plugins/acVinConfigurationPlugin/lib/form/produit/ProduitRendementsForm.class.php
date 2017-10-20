<?php
class ProduitRendementsForm extends acCouchdbObjectForm {

    public function configure() {
            $this->setWidget('rendement', new bsWidgetFormInputFloat());
            $this->setWidget('rendement_vci', new bsWidgetFormInputFloat());
            $this->setWidget('rendement_vci_total', new bsWidgetFormInputFloat());

            $this->getWidget('rendement')->setLabel("Rendement :");
            $this->getWidget('rendement_vci')->setLabel("Rendement VCI :");
            $this->getWidget('rendement_vci_total')->setLabel("Rendement VCI Total :");

            $this->setValidator('rendement', new sfValidatorNumber(array('required' => false), array('required' => 'Champ obligatoire')));
            $this->setValidator('rendement_vci', new sfValidatorNumber(array('required' => false), array('required' => 'Champ obligatoire')));
            $this->setValidator('rendement_vci_total', new sfValidatorNumber(array('required' => false), array('required' => 'Champ obligatoire')));
        $this->widgetSchema->setNameFormat('%s');
    }
    
   
}