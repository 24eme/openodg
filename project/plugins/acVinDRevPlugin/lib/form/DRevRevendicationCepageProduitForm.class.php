<?php
class DRevRevendicationCepageProduitForm extends acCouchdbObjectForm 
{
    public function configure()
    {
        $this->setWidgets(array(
            'superficie_total' => new sfWidgetFormInputFloat(),
            'volume_sur_place' => new sfWidgetFormInputFloat(),
            'volume_sur_place_vtsgn' => new sfWidgetFormInputFloat(),
        ));
        $this->widgetSchema->setLabels(array(
            'superficie_total' => 'Superficie ,:',
            'volume_sur_place' => 'Volume revendiqué (hl):',
            'volume_sur_place_vtsgn' => 'Volume revendiqué (hl):',
        ));
        $this->setValidators(array(
            'superficie_total' => new sfValidatorNumber(array('required' => false)),
            'volume_sur_place' => new sfValidatorNumber(array('required' => false)),
            'volume_sur_place_vtsgn' => new sfValidatorNumber(array('required' => false)),
        ));
        $this->widgetSchema->setNameFormat('[%s]');
    }
    
    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }
    
}