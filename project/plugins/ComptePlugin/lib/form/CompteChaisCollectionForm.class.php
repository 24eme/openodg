<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CompteChaisCollectionForm
 *
 * @author mathurin
 */
class CompteChaisCollectionForm extends acCouchdbForm {

    
    
    public function configure() {
        if (is_null($this->getOption('nbChais'))) {
            throw new InvalidArgumentException('Il doit y avoir une option nbChais.');
        }
        $hasItem = false;
        $key = 0;
       $chais = $this->getDocument()->getChais();
        foreach ($chais as $chai) {
            $this->embedForm($key, new CompteChaiNouveauForm(null, array('chai' => $chai)));
            $hasItem = true;
            $key++;
        }

        $this->embedForm($key, new CompteChaiNouveauForm($this->getDocument()->chais->add()));        
    }

}
