<?php

class DRevRevendicationProduitForm extends acCouchdbObjectForm {

    public function configure() {
        $this->setWidgets(array(
            'superficie_revendique' => new sfWidgetFormInputFloat(),
            'volume_revendique' => new sfWidgetFormInputFloat()
        ));
        $this->widgetSchema->setLabels(array(
            'superficie_revendique' => 'Superficie totale (ares):',
            'volume_revendique' => 'Volume revendiqué (hl):',
        ));
        $this->setValidators(array(
            'superficie_revendique' => new sfValidatorNumber(array('required' => false)),
            'volume_revendique' => new sfValidatorNumber(array('required' => false))
        ));

        if ($this->getObject()->detail->superficie_total) {
            unset($this->widgetSchema['superficie_revendique']);
            unset($this->validatorSchema['superficie_revendique']);
        }

        if($this->getObject()->canHaveVtsgn()) {
            $this->setWidget('superficie_revendique_vtsgn', new sfWidgetFormInputFloat());
            $this->setWidget('volume_revendique_vtsgn', new sfWidgetFormInputFloat());

            $this->widgetSchema->setLabel('superficie_revendique_vtsgn', 'Superficie totale (ares):');
            $this->widgetSchema->setLabel('volume_revendique_vtsgn', 'Volume revendiqué (hl)');

            $this->setValidator('superficie_revendique_vtsgn', new sfValidatorNumber(array('required' => false)));
            $this->setValidator('volume_revendique_vtsgn', new sfValidatorNumber(array('required' => false)));

            if ($this->getObject()->detail_vtsgn->superficie_total) {
                unset($this->widgetSchema['superficie_revendique_vtsgn']);
                unset($this->validatorSchema['superficie_revendique_vtsgn']);
            }
        }

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}
