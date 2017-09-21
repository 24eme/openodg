<?php
class DrevLotsValidator extends sfValidatorSchema 
{
	
	public function configure($options = array(), $messages = array()) {
        $this->setMessage('required', "Vous devez saisir les lots des produits dont le volume a été revendiqué.");
    }

    protected function doClean($values) {
    	$empty = true;
    	foreach ($values['lots'] as $lot) {
    		if ($lot['nb_hors_vtsgn']) {
    			$empty = false;
    			break;
    		}
    	} 
    	if ($empty) {
        	throw new sfValidatorErrorSchema($this, array(new sfValidatorError($this, 'required')));
    	}
    	return $values;
    }
}