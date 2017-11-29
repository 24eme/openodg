<?php

class DRevVciProduitValidator extends sfValidatorSchema {

    public function configure($options = array(), $messages = array()) {
    }

    protected function doClean($values) {

        $errorSchema = new sfValidatorErrorSchema($this);
        $produits = $values['produits'];
        if(!is_array($produits)) {
            if (count($errorSchema)) {
                throw new sfValidatorErrorSchema($this, $errorSchema);
            }
            return $values;
        }
        if (count($errorSchema)) {
            throw new sfValidatorErrorSchema($this, $errorSchema);
        }
        return $values;
    }

}
