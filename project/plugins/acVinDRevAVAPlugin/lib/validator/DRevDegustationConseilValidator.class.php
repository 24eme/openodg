<?php
class DRevDegustationConseilValidator extends sfValidatorSchema 
{
	
	public function configure($options = array(), $messages = array()) {
        $this->setMessage('required', "La période de prélèvement est obligatoire.");
    }

    protected function doClean($values) {
    	if (isset($values['vtsgn_demande']) && $values['vtsgn_demande']) {
    		if (isset($values[Drev::CUVE_VTSGN]) && $values[Drev::CUVE_VTSGN]) {
    			if (isset($values[Drev::CUVE_VTSGN]['date']) && $values[Drev::CUVE_VTSGN]['date']) {
    				return $values;
    			}
    		}
    	} else {
    		return $values;
    	}
        throw new sfValidatorErrorSchema($this, array('vtsgn_demande' => new sfValidatorError($this, 'required')));
    }
}