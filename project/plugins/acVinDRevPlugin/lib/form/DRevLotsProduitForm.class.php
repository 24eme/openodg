<?php
class DRevLotsProduitForm extends acCouchdbObjectForm 
{
   	public function configure()
    {
  		$this->setWidgets(array(
        	'nb_vtsgn' => new sfWidgetFormInputText(),
  		    'nb_hors_vtsgn' => new sfWidgetFormInputText()
    	));
        $this->widgetSchema->setLabels(array(
        	'nb_vtsgn' => 'Lots VT / SGN :',
        	'nb_hors_vtsgn' => 'Lots Hors VT / SGN :',
        ));
        $this->setValidators(array(
        	'nb_vtsgn' => new sfValidatorNumber(array('required' => false)),
        	'nb_hors_vtsgn' => new sfValidatorNumber(array('required' => false))
        ));
  		$this->widgetSchema->setNameFormat('[%s]');
    }
    
    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }
}