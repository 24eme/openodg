<?php

class DRevRevendicationCepageVCIForm extends acCouchdbObjectForm {

    public function configure() {
        $this->setWidgets(array(
          'destruction' => new sfWidgetFormInputFloat(),
          'complement' => new sfWidgetFormInputFloat(),
          'substitution' => new sfWidgetFormInputFloat(),
          'rafraichi' => new sfWidgetFormInputFloat()
        ));
        $this->widgetSchema->setLabels(array(
            'destruction' => 'Volumes detruits avant le 31/12 (en hl))',
            'complement' => 'Volumes revendiqués en complément de la récolte (en hl)',
            'substitution' => 'Volumes substitués (en hl)',
            'rafraichi' => 'Volumes rafraichis (en hl)'
        ));
        $this->setValidators(array(
            'superficie_revendique' => new sfValidatorNumber(array('required' => false)),
            'destruction' => new sfValidatorNumber(array('required' => false)),
            'complement' => new sfValidatorNumber(array('required' => false)),
            'substitution' => new sfValidatorNumber(array('required' => false)),
            'rafraichi' => new sfValidatorNumber(array('required' => false))
        ));

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $this->getObject()->updateStock();
    }

}
