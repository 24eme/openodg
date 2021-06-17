<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParcellaireAffectationAppellationProduitsValidator
 *
 * @author mathurin
 */
class ParcellaireAffectationAcheteursValidator extends sfValidatorSchema {

    public function configure($options = array(), $messages = array()) {
        $this->setMessage('required', 'Vous devez sélectionner une destination pour chacun des produits');
        $this->addMessage('required_acheteur', "Vous n'avez pas déclaré de produit pour la destination : %acheteur%");
        $this->addOption('acheteurs', array());
    }

    protected function doClean($values) {

        $errorSchema = new sfValidatorErrorSchema($this);

        $acheteurs = array();

        foreach($values as $key => $value) {
            if($key == '_revision') {
                continue;
            }

            if(count($value) == 0) {

                throw new sfValidatorErrorSchema($this, array(new sfValidatorError($this, 'required')));
            }

            foreach($value as $hash_acheteur) {
                $acheteurs[$hash_acheteur] = true;
            }
        }

        foreach($this->getOption('acheteurs') as $hash_acheteur => $libelle) {
            if(!array_key_exists($hash_acheteur, $acheteurs)) {
                throw new sfValidatorErrorSchema($this, array(new sfValidatorError($this, 'required_acheteur', array("acheteur" => $libelle))));
            }
        }

        return $values;
    }

}
