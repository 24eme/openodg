<?php
class DRevRevendicationCepageProduitForm extends acCouchdbObjectForm 
{
    public function configure()
    {
        $this->setWidgets(array(
            'superficie_revendique' => new sfWidgetFormInputFloat(),
            'volume_revendique' => new sfWidgetFormInputFloat(),
            'volume_revendique_vtsgn' => new sfWidgetFormInputFloat(),
        ));
        $this->widgetSchema->setLabels(array(
            'superficie_revendique' => 'Superficie ,:',
            'volume_revendique' => 'Volume revendiqué (hl):',
            'volume_revendique_vtsgn' => 'Volume revendiqué (hl):',
        ));
        $this->setValidators(array(
            'superficie_revendique' => new sfValidatorNumber(array('required' => false)),
            'volume_revendique' => new sfValidatorNumber(array('required' => false)),
            'volume_revendique_vtsgn' => new sfValidatorNumber(array('required' => false)),
        ));
        $this->widgetSchema->setNameFormat('[%s]');
    }
    
    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }
    
}