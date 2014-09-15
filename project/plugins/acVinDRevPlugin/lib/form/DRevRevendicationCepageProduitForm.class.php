<?php
class DRevRevendicationCepageProduitForm extends acCouchdbObjectForm 
{
    public function configure()
    {
        $this->setWidgets(array(
            'volume_sur_place_revendique' => new sfWidgetFormInputFloat(),
            'volume_sur_place_revendique_vtsgn' => new sfWidgetFormInputFloat()
        ));
        $this->widgetSchema->setLabels(array(
            'volume_sur_place_revendique' => 'Volume revendiqué (hl):',
            'volume_sur_place_revendique_vtsgn' => 'Volume revendiqué (hl):',
        ));
        $this->setValidators(array(
            'volume_sur_place_revendique' => new sfValidatorNumber(array('required' => false)),
            'volume_sur_place_revendique_vtsgn' => new sfValidatorNumber(array('required' => false))
        ));
        $this->widgetSchema->setNameFormat('[%s]');
    }
    
    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }
    
}