<?php

class tirageComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        if(!$this->periode) {
            $this->periode = preg_replace("/-.+/", "", $this->campagne);
        }
        $this->nbDeclaration = TirageClient::getInstance()->getLastNumero($this->etablissement->identifiant, $this->campagne);
        $nextNumero = $this->nbDeclaration + 1;
        $this->nieme = '';
        if ($nextNumero > 1) {
            $this->nieme = $nextNumero."ème";
        }

        $this->tirage = TirageClient::getInstance()->find('TIRAGE-' . $this->etablissement->identifiant . '-' . $this->campagne. sprintf("%02d", $this->nbDeclaration));

        if($this->tirage && $this->tirage->validation){
            $this->tirage = TirageClient::getInstance()->find('TIRAGE-' . $this->etablissement->identifiant . '-' . $this->campagne. sprintf("%02d", $nextNumero));
        }
    }

}
