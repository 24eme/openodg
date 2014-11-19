<?php
class DRevControleExterneValidator extends sfValidatorSchema 
{
    
    public function configure($options = array(), $messages = array()) {
        $this->setMessage('required', "Le nombre de lots est obligatoire.");
    }

    protected function doClean($values) {
        if (isset($values[DRev::BOUTEILLE_VTSGN]['date']) && $values[DRev::BOUTEILLE_VTSGN]['date']) {
            if (!isset($values[DRev::BOUTEILLE_VTSGN]['total_lots']) || !$values[DRev::BOUTEILLE_VTSGN]['total_lots']) {
                
                $errorSchema =  new sfValidatorErrorSchema($this);
                $error =  new sfValidatorError($this, 'required');
                $errorSchema->addError($error, 'total_lots');

                throw new sfValidatorErrorSchema($errorSchema->getValidator(), array(DRev::BOUTEILLE_VTSGN => $errorSchema));
            }
        }
        
        return $values;
    }
}