<?php

class DRevRevendicationProduitVolumeForm extends acCouchdbObjectForm {

    public function configure() {
        $this->setWidgets(array(
            'volume_revendique_recolte' => new sfWidgetFormInputFloat()
        ));
        $this->widgetSchema->setLabels(array(
            'volume_revendique_recolte' => 'Volume revendiqué (hl):',
        ));
        $this->setValidators(array(
            'volume_revendique_recolte' => new sfValidatorNumber(array('required' => false))
        ));

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
