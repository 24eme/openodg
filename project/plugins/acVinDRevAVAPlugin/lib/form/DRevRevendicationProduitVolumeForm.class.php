<?php

class DRevRevendicationProduitVolumeForm extends acCouchdbObjectForm {

    public function configure() {
        $this->setWidgets(array(
            'volume_revendique' => new sfWidgetFormInputFloat()
        ));
        $this->widgetSchema->setLabels(array(
            'volume_revendique' => 'Volume revendiqué (hl):',
        ));
        $this->setValidators(array(
            'volume_revendique' => new sfValidatorNumber(array('required' => false))
        ));

        if ($this->getObject()->detail->superficie_total) {
            unset($this->widgetSchema['superficie_revendique']);
            unset($this->validatorSchema['superficie_revendique']);
        }

        if($this->getObject()->canHaveVtsgn()) {
            $this->setWidget('volume_revendique_vtsgn', new sfWidgetFormInputFloat());

            $this->widgetSchema->setLabel('volume_revendique_vtsgn', 'Volume revendiqué (hl)');

            $this->setValidator('volume_revendique_vtsgn', new sfValidatorNumber(array('required' => false)));

        }

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}
