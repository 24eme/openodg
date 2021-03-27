<?php
class ChgtDenomValidator extends sfValidatorSchema
{

		protected $obj;

		public function __construct($obj, $fields = null, $options = array(), $messages = array())
		{
				$this->obj = $obj;
				parent::__construct($fields, $options, $messages);
		}

		public function configure($options = array(), $messages = array()) {
				parent::configure($options, $messages);
        $this->addMessage('impossible_volume', "Le volume ne peut excéder ".$this->obj->getLotOrigine()->volume." hl");
				$this->addMessage('impossible_numero', "Le numéro du logement ne peut pas être identique à l'original");
    }

    protected function doClean($values) {
			$errorSchema = new sfValidatorErrorSchema($this);
    	$hasError = false;

    	if ($values['changement_type'] == 'CHGT' && !$values['changement_produit_hash']) {
    	    $errorSchema->addError(new sfValidatorError($this, 'required'), 'changement_produit_hash');
    	    $hasError = true;
    	}

    	if ($values['changement_quantite'] == 'PART' && !$values['changement_volume']) {
    	    $errorSchema->addError(new sfValidatorError($this, 'required'), 'changement_volume');
    	    $hasError = true;
    	}

    	if ($values['changement_quantite'] == 'PART' && !$values['changement_numero']) {
    	    $errorSchema->addError(new sfValidatorError($this, 'required'), 'changement_numero');
    	    $hasError = true;
    	}

    	if ($values['changement_quantite'] == 'PART' && $values['changement_numero'] == $this->obj->getLotOrigine()->numero) {
    	    $errorSchema->addError(new sfValidatorError($this, 'impossible_numero'), 'changement_numero');
    	    $hasError = true;
    	}

    	if ($values['changement_quantite'] == 'PART' && $values['changement_volume'] > $this->obj->getLotOrigine()->volume) {
    	    $errorSchema->addError(new sfValidatorError($this, 'impossible_volume'), 'changement_volume');
    	    $hasError = true;
    	}

    	if ($hasError) {
    		throw new sfValidatorErrorSchema($this, $errorSchema);
    	}
      return $values;
    }
}
