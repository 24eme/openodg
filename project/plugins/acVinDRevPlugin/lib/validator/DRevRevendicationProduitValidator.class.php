<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DRevRevendicationProduitValidator
 *
 * @author mathurin
 */
class DRevRevendicationProduitValidator extends sfValidatorSchema {

    public function configure($options = array(), $messages = array()) {
        $this->addMessage('invalid_volume', 'Le volume .');
        $this->addMessage('invalid_superficie', 'La superficie.');
    }

    protected function doClean($values) {

        $errorSchema = new sfValidatorErrorSchema($this);
        foreach ($values as $key => $produit) {
            if (is_array($produit) && array_key_exists('superficie_revendique', $produit)) {
                if (!$produit['superficie_revendique'] && $produit['volume_revendique']) {
                    $errorSchema->addError(new sfValidatorError($this, 'invalid_superficie'), 'superficie_revendique');
                }
                if ($produit['superficie_revendique'] && !$produit['volume_revendique']) {
                    $errorSchema->addError(new sfValidatorError($this, 'invalid_volume'), 'volume_revendique');
                }
            } else {
                if (!$produit['volume_revendique']) {
                    $errorSchema->addError(new sfValidatorError($this, 'invalid_volume'), 'volume_revendique');
                }
            }
        }
        if (count($errorSchema)) {
            throw new sfValidatorErrorSchema($this, $errorSchema);
        }
        return $values;
    }

}
