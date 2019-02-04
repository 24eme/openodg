<?php

class DRevRevendicationCepageProduitForm extends acCouchdbObjectForm {

    protected $vtsgn = false;

    public function configure() {
        $this->vtsgn = $this->getObject()->getConfig()->hasVtsgn();

        $this->setWidgets(array(
            'volume_revendique_recolte' => new sfWidgetFormInputFloat()));

        $this->widgetSchema->setLabels(array(
            'volume_revendique_recolte' => 'Volume revendiqué (hl):'
        ));
        $this->setValidators(array(
            'volume_revendique_recolte' => new sfValidatorNumber(array('required' => false))
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

            if ($this->vtsgn) {
                $this->setWidget('superficie_vinifiee_vt', new sfWidgetFormInputFloat());
            	$this->getWidget('superficie_vinifiee_vt')->setLabel("Superficie vinifiée (ares):");
            	$this->setValidator('superficie_vinifiee_vt', new sfValidatorNumber(array('required' => false)));

                $this->setWidget('superficie_vinifiee_sgn', new sfWidgetFormInputFloat());
            	$this->getWidget('superficie_vinifiee_sgn')->setLabel("Superficie vinifiée (ares):");
            	$this->setValidator('superficie_vinifiee_sgn', new sfValidatorNumber(array('required' => false)));
            }
        }

        if ($this->getObject()->getConfig()->getRendementVci() > 0) {
            $this->setWidget('has_stock_vci', new sfWidgetFormInputCheckbox());
            $this->setValidator('has_stock_vci', new sfValidatorBoolean(array('required' => false)));

            $this->setWidget('vci_constitue', new sfWidgetFormInputFloat());
            $this->setValidator('vci_constitue', new sfValidatorNumber(array('required' => false)));
        }

        $this->widgetSchema->setNameFormat('[%s]');
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $this->setDefault('has_stock_vci',  $this->getObject()->hasVci());

        if(!$this->getObject()->exist('volume_revendique_recolte')) {
           $this->setDefault('volume_revendique_recolte',  $this->getObject()->volume_revendique);
       }
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
        if(isset($values['has_stock_vci']) && $values['has_stock_vci']) {
            $this->getObject()->activateVci();
        } else {
            $this->getObject()->deactivateVci();
        }
        $this->getObject()->getDocument()->calculateVolumeRevendiqueVCI();
    }

}
