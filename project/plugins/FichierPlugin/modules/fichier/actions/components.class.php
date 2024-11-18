<?php

class fichierComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $libelles = [];
        $this->campagne = $this->periode.'-'.($this->periode + 1);

        $pieces = PieceAllView::getInstance()->getPiecesByEtablissement($this->etablissement->identifiant, true, ConfigurationClient::getInstance()->getCampagneVinicole()->getDateDebutByCampagne($this->campagne), ConfigurationClient::getInstance()->getCampagneVinicole()->getDateFinByCampagne($this->campagne), [strtolower(DRClient::TYPE_COUCHDB)]);
        $this->hasDeclarationsMetayer = false;
        foreach($pieces as $piece) {
            if(explode('-', $piece->id)[1] != $this->etablissement->identifiant) {
                $this->hasDeclarationsMetayer = true;
                break;
            }
        }

        if(class_exists("DRClient") && in_array($this->etablissement->famille, [EtablissementFamilles::FAMILLE_PRODUCTEUR, EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR])) {
            $this->type = DRClient::TYPE_MODEL;
        }
        if(class_exists("SV11Client") && in_array($this->etablissement->famille, [EtablissementFamilles::FAMILLE_COOPERATIVE])) {
            $this->type = SV11Client::TYPE_MODEL;
            $libelles[SV11Client::TYPE_MODEL] = "Déclaration de Production";
        }
        if(class_exists("SV12Client") && in_array($this->etablissement->famille, [EtablissementFamilles::FAMILLE_NEGOCIANT, EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR])) {
            $this->type = SV12Client::TYPE_MODEL;
            $libelles[SV12Client::TYPE_MODEL] = "Déclaration de Production";
        }
        if(class_exists("DRClient")) {
    	    $this->declaration = DRClient::getInstance()->findByArgs($this->etablissement->identifiant, $this->periode);
            $libelles[DRClient::TYPE_MODEL] = "Déclaration de Récolte";
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

        if($this->type) {
            $this->typeLibelle = $libelles[$this->type];
        }
    }

}
