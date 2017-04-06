<?php

class DRevRevendicationCepageProduitForm extends acCouchdbObjectForm {

    protected $vtsgn = false;

    public function configure() {
        $this->vtsgn = $this->getObject()->getConfig()->hasVtsgn();

        $this->setWidgets(array(
            'volume_revendique' => new sfWidgetFormInputFloat()));

        $this->widgetSchema->setLabels(array(
            'volume_revendique' => 'Volume revendiqué (hl):'
        ));
        $this->setValidators(array(
            'volume_revendique' => new sfValidatorNumber(array('required' => false))
        ));

        if ($this->vtsgn) {

            $this->setWidget('volume_revendique_vt', new sfWidgetFormInputFloat());
            $this->setWidget('volume_revendique_sgn', new sfWidgetFormInputFloat());

            $this->getWidget('volume_revendique_vt')->setLabel("Volume revendiqué (hl):");
            $this->getWidget('volume_revendique_sgn')->setLabel("Volume revendiqué (hl):");

            $this->setValidator('volume_revendique_vt', new sfValidatorNumber(array('required' => false)));
            $this->setValidator('volume_revendique_sgn', new sfValidatorNumber(array('required' => false)));
        }
        

        if ($this->getObject()->canHaveSuperficieVinifiee()) {
        	$this->setWidget('superficie_vinifiee', new sfWidgetFormInputFloat());
        	$this->getWidget('superficie_vinifiee')->setLabel("Superficie vinifiée (ares):");
        	$this->setValidator('superficie_vinifiee', new sfValidatorNumber(array('required' => false)));
        }

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function hasVtSgn() {
        return $this->vtsgn;
    }

    public function doUpdateObject($values) {
        if ($this->hasVtSgn()) {
            if ($values['volume_revendique_vt']) {
                $this->getObject()->volume_revendique_vt = $values['volume_revendique_vt'];
            }
            if ($values['volume_revendique_sgn']) {
                $this->getObject()->volume_revendique_sgn = $values['volume_revendique_sgn'];
            }
        }
        parent::doUpdateObject($values);
    }

}
