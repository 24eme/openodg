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
        $this->addMessage('invalid_volume', '[key]Les volumes revendiqués doivent être remplis');
        $this->addMessage('invalid_superficie', '[key]Les superficies totales doivent être remplies.');
    }

    protected function doClean($values) {

        $errorSchema = new sfValidatorErrorSchema($this);
        $produits = $values['produits'];
        foreach ($produits as $key => $produit) {
            if (is_array($produit) && array_key_exists('superficie_revendique', $produit)) {
                if (!$produit['superficie_revendique'] && $produit['volume_revendique']) {
                    $this->setMessage('invalid_volume', str_replace('[key]', '[['.$key.'][superficie_revendique]]', $this->getMessage('invalid_volume')));
                    $errorSchema->addError(new sfValidatorError($this, 'invalid_superficie'), 'superficie_revendique');
                    throw new sfValidatorErrorSchema($this, $errorSchema);
                    return $values;
                }
                if ($produit['superficie_revendique'] && !$produit['volume_revendique']) {
                    $this->setMessage('invalid_volume', str_replace('[key]', '[['.$key.'][volume_revendique]]', $this->getMessage('invalid_volume')));
                    $errorSchema->addError(new sfValidatorError($this, 'invalid_volume'), 'volume_revendique');
                    throw new sfValidatorErrorSchema($this, $errorSchema);
                    return $values;
                }
            } else {
                if (!$produit['volume_revendique']) {  
                    $this->setMessage('invalid_volume', str_replace('[key]', '[['.$key.'][volume_revendique]]', $this->getMessage('invalid_volume')));
                    $errorSchema->addError(new sfValidatorError($this, 'invalid_volume'),'volume_revendique'); 
                    throw new sfValidatorErrorSchema($this, $errorSchema);
                    return $values;
                }
            }
        }
        if (count($errorSchema)) { 
            throw new sfValidatorErrorSchema($this, $errorSchema);
        }
        return $values;
    }

}
