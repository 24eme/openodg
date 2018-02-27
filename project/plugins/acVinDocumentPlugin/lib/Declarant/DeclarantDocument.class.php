<?php

class DeclarantDocument
{
    protected $document;
    protected $etablissement = null;

    public function __construct(acCouchdbDocument $document)
    {
        $this->document = $document;
    }

    public function getIdentifiant()
    {
        return $this->document->identifiant;
    }

    public function getDeclarant()
    {
        return $this->document->declarant;
    }

    public function getEtablissementObject() {

        return $this->document->getEtablissementObject();
    }

    public function storeDeclarant()
    {
        $etablissement = $this->getEtablissementObject();
        if (!$etablissement) {
            throw new sfException(sprintf("L'etablissement %s n'existe pas", $this->getIdentifiant()));
            return;
        }
        $declarant = $this->getDeclarant();

        $declarant->nom = null;
        if ($etablissement->exist("intitule") && $etablissement->get("intitule")) {
            $declarant->nom = $etablissement->intitule . " ";
        }
        $declarant->nom .= $etablissement->nom;
        $declarant->raison_sociale = $etablissement->getRaisonSociale();
        $declarant->cvi = $etablissement->cvi;

        if($etablissement->exist("no_accises") && $declarant->exist("no_accises")) {
            $declarant->no_accises = $etablissement->getNoAccises();
        }
        if($etablissement->exist("siege")) {
            $declarant->adresse = $etablissement->siege->adresse;
            if ($etablissement->siege->exist("adresse_complementaire") && $etablissement->siege->adresse_complementaire) {
                $declarant->adresse .= ' − '.$etablissement->siege->adresse_complementaire;
            }
            $declarant->commune = $etablissement->siege->commune;
            $declarant->code_postal = $etablissement->siege->code_postal;
        }

        if($etablissement->exist("adresse")) {
            $declarant->adresse = $etablissement->adresse;
        }
        if($etablissement->exist("commune")) {
            $declarant->commune = $etablissement->commune;
        }
        if($etablissement->exist("code_postal")) {
            $declarant->code_postal = $etablissement->code_postal;
        }

        if($etablissement->exist("region") && $declarant->exist('region')) {
            $declarant->region = $etablissement->getRegion();
        }

        if ($etablissement->exist("ppm")) {
          if($declarant->getDefinition()->exist('ppm'))
          $declarant->add('ppm', $etablissement->ppm);
        }

        if ($etablissement->exist("siret")) {
            if($declarant->getDefinition()->exist('siret'))
                 $declarant->add('siret', $etablissement->siret);
        }
        if ($etablissement->exist("telephone_bureau")) {
            if(!$declarant->getDefinition()->exist('telephone_bureau')){
                if($declarant->getDefinition()->exist('telephone')){
                    $declarant->add('telephone', $etablissement->telephone_bureau);
                }
            }else{
                $declarant->add('telephone_bureau', $etablissement->telephone_bureau);
            }

        } elseif ($etablissement->exist("telephone")) {
            if(!$declarant->getDefinition()->exist('telephone_bureau')){
                if($declarant->getDefinition()->exist('telephone')){
                    $declarant->add('telephone', $etablissement->telephone_bureau);
                }
            }else{
                $declarant->add('telephone_bureau', $etablissement->telephone_bureau);
            }
        }
        if ($etablissement->exist("telephone_mobile") && $declarant->getDefinition()->exist('telephone_mobile')) {
            $declarant->add('telephone_mobile', $etablissement->telephone_mobile);
        }
        if ($etablissement->exist("email")) {
            if($declarant->getDefinition()->exist('email'))
               $declarant->add('email', $etablissement->email);
        }
        if ($etablissement->exist("fax")) {
             if($declarant->getDefinition()->exist('fax'))
                $declarant->add('fax', $etablissement->fax);
        }
    }
}
