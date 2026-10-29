<?php
class ProduitRendementsForm extends acCouchdbObjectForm {

    public function configure() {
            $this->setWidget('rendement', new bsWidgetFormInput());
            $this->setWidget('rendement_conseille', new bsWidgetFormInput());
            $this->setWidget('rendement_dr', new bsWidgetFormInput());
            $this->setWidget('rendement_dr_l5', new bsWidgetFormInput());
            $this->setWidget('rendement_dr_l15', new bsWidgetFormInput());
            $this->setWidget('rendement_vci', new bsWidgetFormInput());
            $this->setWidget('rendement_vci_total', new bsWidgetFormInput());

            $this->getWidget('rendement')->setLabel("Rendemt DREV Max. :");
            $this->getWidget('rendement_conseille')->setLabel("Rendemt DREV Conseillé :");
            $this->getWidget('rendement_dr')->setLabel("Rendemt DR (ne plus utiliser) :");
            $this->getWidget('rendement_dr_l5')->setLabel("Rendemt DR L5 :");
            $this->getWidget('rendement_dr_l15')->setLabel("Rendemt DR L15 :");
            $this->getWidget('rendement_vci')->setLabel("Rendemt DREV VCI :");
            $this->getWidget('rendement_vci_total')->setLabel("Rendemt VCI Total :");

            $this->setValidator('rendement', new sfValidatorNumber(array('required' => false)));
            $this->setValidator('rendement_conseille', new sfValidatorNumber(array('required' => false)));
            $this->setValidator('rendement_dr', new sfValidatorNumber(array('required' => false)));
            $this->setValidator('rendement_dr_l5', new sfValidatorNumber(array('required' => false)));
            $this->setValidator('rendement_dr_l15', new sfValidatorNumber(array('required' => false)));
            $this->setValidator('rendement_vci', new sfValidatorNumber(array('required' => false)));
            $this->setValidator('rendement_vci_total', new sfValidatorNumber(array('required' => false)));
        $this->widgetSchema->setNameFormat('%s');
    }


}
