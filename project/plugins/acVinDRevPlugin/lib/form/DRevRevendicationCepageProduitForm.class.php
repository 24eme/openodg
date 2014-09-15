<?php
class DRevRevendicationCepageProduitForm extends acCouchdbObjectForm 
{
    public function configure()
    {
        $this->setWidgets(array(
            'volume_sur_place_revendique' => new sfWidgetFormInputFloat()
        ));
        $this->widgetSchema->setLabels(array(
            'volume_sur_place_revendique' => 'Volume revendiqué (hl):',
        ));
        $this->setValidators(array(
            'volume_sur_place_revendique' => new sfValidatorNumber(array('required' => false))
        ));
        $this->widgetSchema->setNameFormat('[%s]');
    }
    
    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }
    
}