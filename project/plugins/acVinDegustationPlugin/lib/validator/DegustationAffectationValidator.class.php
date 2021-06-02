<?php

class DegustationAffectationValidator extends sfValidatorSchema {

      public function __construct($obj, $fields= null, $options = array(), $messages = array()) {

          $this->obj = $obj;
          parent::__construct($fields, $options, $messages);
      }

  		public function configure($options = array(), $messages = array()) {
          $this->addMessage('impossible_table', "La table n'existe pas ".$table);
      }

      protected function doClean($values) {
  			$errorSchema = new sfValidatorErrorSchema($this);
      	$hasError = false;

        $degustation = DegustationClient::getInstance()->find($values['degustation']);
        $infos = $degustation->getInfosDegustation();
      	if ($values['numero_table'] > $infos['nbTables']) {
      	    $errorSchema->addError(new sfValidatorError($this, 'impossible_table'), 'numero_table');
      	    $hasError = true;
      	}

      	if ($hasError) {
      		throw new sfValidatorErrorSchema($this, $errorSchema);
      	}
        return $values;
      }
  }
