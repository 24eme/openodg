<?php
class DRevMarcRevendicationForm extends acCouchdbObjectForm 
{    
	public function configure()
    {
            
        $this->widgetSchema->setNameFormat('drevmarc_revendication[%s]');
         $this->setWidget('debut_distillation', new sfWidgetFormInput());
            $this->setValidator('debut_distillation', new sfValidatorRegex(array('pattern' => '/[0-9]{4}-[0-9]{2}-[0-9]{2}/','required' => true)));
            $this->getWidget('debut_distillation')->setLabel("du");

        $this->setWidget('fin_distillation', new sfWidgetFormInput());
            $this->setValidator('fin_distillation', new sfValidatorRegex(array('pattern' => '/[0-9]{4}-[0-9]{2}-[0-9]{2}/','required' => true)));
            $this->getWidget('fin_distillation')->setLabel("au");
        
           $this->setWidget('qte_marc', new sfWidgetFormInput());
            $this->setValidator('qte_marc', new sfValidatorNumber(array('required' => true)));
            $this->getWidget('qte_marc')->setLabel("Quantité de marc mise en oeuvre :");
            
             $this->setWidget('volume_obtenu', new sfWidgetFormInput());
            $this->setValidator('volume_obtenu', new sfValidatorNumber(array('required' => true)));
            $this->getWidget('volume_obtenu')->setLabel("Volume total obtenu :");
            
            $this->setWidget('titre_alcool_vol', new sfWidgetFormInput());
            $this->setValidator('titre_alcool_vol', new sfValidatorNumber(array('required' => true)));
            $this->getWidget('titre_alcool_vol')->setLabel("Titre alcoométrique volumique :");
    }
    
    protected function doUpdateObject($values) 
    {
        parent::doUpdateObject($values);
    }
}