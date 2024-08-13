<?php

class fichierComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        if(class_exists("DRClient") && in_array($this->etablissement->famille, [EtablissementFamilles::FAMILLE_PRODUCTEUR, EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR])) {
            $this->type = DRClient::TYPE_MODEL;
        }
        if(class_exists("SV11Client") && in_array($this->etablissement->famille, [EtablissementFamilles::FAMILLE_COOPERATIVE])) {
            $this->type = SV11Client::TYPE_MODEL;
        }
        if(class_exists("SV12Client") && in_array($this->etablissement->famille, [EtablissementFamilles::FAMILLE_NEGOCIANT, EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR])) {
            $this->type = SV12Client::TYPE_MODEL;
        }
        if(class_exists("DRClient")) {
    	    $this->declaration = DRClient::getInstance()->findByArgs($this->etablissement->identifiant, $this->periode);
        }
        if(!$this->declaration && class_exists("SV11Client")) {
    	    $this->declaration = SV11Client::getInstance()->findByArgs($this->etablissement->identifiant, $this->periode);
        }
        if(!$this->declaration && !$this->sv && class_exists("SV12Client")) {
    	    $this->declaration = SV12Client::getInstance()->findByArgs($this->etablissement->identifiant, $this->periode);
        }

        if($this->declaration) {
            $this->type = $this->declaration->type;
        }

        $libelles = [DRClient::TYPE_MODEL => "Déclaration de Récolte", SV11Client::TYPE_MODEL => "Déclaration de Production", SV12Client::TYPE_MODEL => "Déclaration de Production"];

        if($this->type) {
            $this->typeLibelle = $libelles[$this->type];
        }
    }

}
