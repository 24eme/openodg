<?php

class DRevRevendicationProduitSuperficieForm extends acCouchdbObjectForm {

    public function configure() {
        $this->setWidgets(array(
            'superficie_revendique' => new sfWidgetFormInputFloat(),
        ));
        $this->widgetSchema->setLabels(array(
            'superficie_revendique' => 'Superficie totale (ares):',
        ));
        $this->setValidators(array(
            'superficie_revendique' => new sfValidatorNumber(array('required' => false)),
        ));

        if ($this->getObject()->detail->superficie_total) {
            unset($this->widgetSchema['superficie_revendique']);
            unset($this->validatorSchema['superficie_revendique']);
        }

        if($this->getObject()->canHaveVtsgn()) {
            $this->setWidget('superficie_revendique_vtsgn', new sfWidgetFormInputFloat());
            $this->widgetSchema->setLabel('superficie_revendique_vtsgn', 'Superficie totale (ares):');
            $this->setValidator('superficie_revendique_vtsgn', new sfValidatorNumber(array('required' => false)));
            if ($this->getObject()->detail_vtsgn->superficie_total) {
                unset($this->widgetSchema['superficie_revendique_vtsgn']);
            }
        }

        if ($this->getObject()->canHaveSuperficieVinifiee()) {
        	$this->setWidget('superficie_vinifiee', new sfWidgetFormInputFloat());
        	$this->getWidget('superficie_vinifiee')->setLabel("Superficie vinifiée (ares):");
        	$this->setValidator('superficie_vinifiee', new sfValidatorNumber(array('required' => false)));

        	if($this->getObject()->canHaveVtsgn()) {
        		$this->setWidget('superficie_vinifiee_vtsgn', new sfWidgetFormInputFloat());
        		$this->getWidget('superficie_vinifiee_vtsgn')->setLabel("Superficie vinifiée (ares):");
        		$this->setValidator('superficie_vinifiee_vtsgn', new sfValidatorNumber(array('required' => false)));
        	}
        }

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}
