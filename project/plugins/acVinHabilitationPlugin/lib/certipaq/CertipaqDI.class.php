<?php

class CertipaqDI extends CertipaqService
{
    public function getAll() {
        $param = array();
        $param['date'] = array('2000-01-01', date('Y-m-d'));
        return $this->query('declaration/identification', 'GET', $params);
    }

    public function findByOperateurId($operateur_certipaq_id) {
        $param = array();
        $param['operateur_id'] = $operateur_certipaq_id;
        return $this->query('declaration/identification', 'GET', $params);
    }


    public function getDemandeIdentificationType() {
        return array(
            "declaration_identification_nouvel_operateur_request" => "Nouvel opérateur",
            "declaration_identification_extension_habilitation_request" => "Extention d'habilitation",
            "declaration_identification_nouveau_site_production_request" => "Nouveau site de production",
            "declaration_identification_modification_identite_request" => "Modification d'identité",
            "declaration_identification_modification_outil_request" => "Modification d'outil",
        );
    }

    private function fillAdresse(&$obj, $compte) {
        $obj['adresse'] = $compte->getAdresse();
        $obj['complement_adresse'] = $compte->getAdresseComplementaire();
        $obj['code_postal'] = $compte->getCodePostal();
        $obj['ville'] = $compte->getCommune();
        $obj['pays'] = ($compte->getPays() == 'France') ? 'FR' : $compte->getPays();
    }

    private function fillAdresseAndContact(&$obj, $compte) {
        $this->fillAdresse($obj, $compte);
        $obj['telephone'] = $compte->getTelephoneBureau();
        $obj['portable'] = $compte->getTelephoneMobile();
        $obj['email'] = $compte->getEmail();
    }
    private function fillOperateur($habilitation) {
        $etablissement = $habilitation->getEtablissementObject();
        $operateur = array();
        $operateur['raison_sociale'] = $etablissement->raison_sociale;
        $operateur['nom_entreprise'] = $etablissement->nom;
        $operateur['siret'] = $etablissement->getSiret();
        $operateur['cvi'] = $etablissement->getCvi();
        $this->fillAdresseAndContact($operateur, $etablissement);
        return $operateur;
    }

    private function fillAdresses($habilitation) {
        $adresses = array();
        $adresses[0] = array();
        $adresses[0]['dr_adresse_type_id'] = 4; //Siège social
        $this->fillAdresse($adresses[0], $habilitation->getSociete());
        $adresses[1] = array();
        $adresses[1]['dr_adresse_type_id'] = 2; //Facturation
        $this->fillAdresse($adresses[1], $habilitation->getSociete());
        $adresses[2] = array();
        $adresses[2]['dr_adresse_type_id'] = 3; //Prelevement
        $this->fillAdresse($adresses[2], $habilitation->getEtablissementObject());
        return $adresses;
    }

    public function fillSites($habilitation) {
        $sites[0] = array();
        $sites[0]['nom_site'] = $habilitation->getEtablissementObject()->getNom();
        $this->fillAdresseAndContact($sites[0], $habilitation->getEtablissementObject());
    }

    public function fillHabilitations($demande) {
        $h = $demande->getExtractHistoriqueFromStatut('COMPLET');
        $habilitations = array();
        $habilitation = array();
        $certipaq_produit = CertipaqDeroulant::getInstance()->getCertipaqProduitFromConfigurationProduit($demande->getConfig());
        foreach($demande->getActivitesLibelle() as $activite) {
            $habilitation['dr_cdc_famille_id'] = $certipaq_produit->dr_cdc_famille_id;
            $habilitation['dr_activites_operateurs'] = array();
            $habilitation['dr_activites_operateurs']['dr_activites_operateurs_id'] = CertipaqDeroulant::getInstance()->findActivite($activite);
            $habilitation['dr_activites_operateurs']['num_habilitation'] = $demande->getKey();
            $habilitation['dr_cdc'] = array();
            $habilitation['dr_cdc'][0] = array();
            $habilitation['dr_cdc'][0]['dr_cdc_id'] = $certipaq_produit->id;
            $habilitation['date_dossier_complet'] = $h->date;
            $habilitations[] = $habilitation;
        }
        return $habilitations;
    }

    public function getParamNouvelOperateurFromDemande($demande) {
        $habilitation = $demande->getDocument();
        $param = array();
        $param['dr_demande_identification_type_id'] = "declaration_identification_nouvel_operateur_request";
        $param['operateur'] = $this->fillOperateur($habilitation);
        $param['adresses'] = $this->fillAdresses($habilitation);
        $param['sites'] = $this->fillSites($habilitation);
        //$param['habilitations'] = $this->fillHabilitations($demande);
        //$param['informations_autres'] = $demande->commentaire;
        return $param;
    }

    public function getParamExtentionHabilitationFromDemande($demande) {
        $param = array();
        $param["dr_demande_identification_type_id"] = "declaration_identification_extension_habilitation_request";
        $param["operateur_id"] = 0;
        //$param['habilitations'] = $this->fillHabilitations($demande);
        //$param['informations_autres'] = $demande->commentaire;
        return $param;
    }

    public function getParamNouveauSiteFromDemande($demande) {
        $habilitation = $demande->getDocument();
        $param = array();
        $param["dr_demande_identification_type_id"] =  "declaration_identification_nouveau_site_production_request";
        $param["operateur_id"] =  0;
        $param['sites'] = $this->fillSites($habilitation);
        //$param['habilitations'] = $this->fillHabilitations($demande);
        //$param['informations_autres'] = $demande->commentaire;
        return $param;
    }

    public function getParamModificationIdentiteFromDemande($demande) {
        $etablissement = $demande->getDocument()->getEtablissementObject();
        $param = array();
        $param["dr_demande_identification_type_id"] = "declaration_identification_modification_identite_request";
        $param['operateur']["id"] = 0;
        $param['operateur']["objet_modification"] = "Modification de l'identité";
        $param['operateur']["raison_sociale"] = $etablissement->raison_sociale;
        $param['operateur']["nom_entreprise"] = $etablissement->nom;
        $param['operateur']["siret"] = $etablissement->getSiret();
        $param['informations_autres'] = $demande->commentaire;
        return $param;
    }

    public function getParamModificationOutilFromDemande($demande) {
        $param = array();
        $param["dr_demande_identification_type_id"] = "declaration_identification_modification_outil_request";
        $param["id"] = 0;
        $param["objet_modification"] = $demande->commentaire;
        $param["nom_outil"] = "";
        $param["portee_habilitation"] = "";
        $param["date_dossier_complet"] = $h->date;
        $param["informations_autres"] = $demande->commentaire;
        return $param;
    }

}