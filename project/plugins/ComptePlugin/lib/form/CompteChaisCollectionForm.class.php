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
class CompteChaisCollectionForm extends acCouchdbForm
{
  public function configure()
  {      
    for ($i=0; $i < $this->getOption('nbChai', 1) ; $i++) {
    	$this->embedForm ($i, new CompteChaiNouveauForm($this->getDocument()->chais->add()));
    }
  }
}