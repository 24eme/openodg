<?php
class FactureEditionLigneDetailValidator extends sfValidatorSchema 
{
    
    public function configure($options = array(), $messages = array()) {
    
    }

    protected function doClean($values) {
        if(empty($values['quantite']) && empty($values['libelle']) && empty($values['prix_unitaire'])) {
            
            return $values;
        }

        $errors = array();

        if(empty($values['quantite'])) {
            $errors['quantite'] = new sfValidatorError($this, 'required');
        }

        if(empty($values['libelle'])) {
            $errors['libelle'] = new sfValidatorError($this, 'required');
        }

        if(empty($values['prix_unitaire'])) {
            $errors['prix_unitaire'] = new sfValidatorError($this, 'required');
        }

        if(count($errors)) {

            throw new sfValidatorErrorSchema($this, $errors);
        }

        return $values;
    }
}