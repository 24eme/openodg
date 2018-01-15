<?php
class DRevLotsProduitForm extends acCouchdbObjectForm 
{
   	public function configure()
    {
        $this->setWidget('nb_hors_vtsgn', new sfWidgetFormInputText());
        $this->getWidget('nb_hors_vtsgn')->setLabel("Lots Hors VT / SGN :");
        $this->setValidator('nb_hors_vtsgn',  new sfValidatorNumber(array('required' => false)));
        

  		$this->widgetSchema->setNameFormat('[%s]');
    }
    
    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }
}