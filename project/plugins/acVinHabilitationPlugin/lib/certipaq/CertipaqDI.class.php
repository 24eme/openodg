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
        $obj['cp'] = $compte->getCodePostal();
        $obj['ville'] = $compte->getCommune();
        $obj['pays'] = $compte->getPays();
    }

    private function fillAdresseAndContact(&$obj, $compte) {
        $this->fillAdresse($obj, $compte);
        $obj['telephone'] = ($compte->getTelephoneBureau()) ? $compte->getTelephoneBureau() : $compte->getTelephonePerso();
        $obj['portable'] = $compte->getTelephoneMobile();
        $obj['fax'] = '';
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
        $sites = array();
        $sites[0]['nom_site'] = $habilitation->getEtablissementObject()->getNom();
        $sites[0]['capacite_cuverie'] = null;
        $this->fillAdresseAndContact($sites[0], $habilitation->getEtablissementObject());
        return $sites;
    }

    public function fillHabilitationsFromDemande($demande) {
        $h = $demande->getExtractHistoriqueFromStatut('COMPLET');
        $habilitations = array();
        $certipaq_produit = CertipaqDeroulant::getInstance()->getCertipaqProduitFromConfigurationProduit($demande->getConfig());
        foreach($demande->getActivitesLibelle() as $activite) {
            $habilitation = array();
            $habilitation['dr_cdc_famille_id'] = $certipaq_produit->dr_cdc_famille_id;
            $habilitation['dr_activites_operateurs'] = array();
            $a = CertipaqDeroulant::getInstance()->findActivite($activite);
            $habilitation['dr_activites_operateurs']['dr_activites_operateurs_id'] = $a->id;
            $habilitation['dr_activites_operateurs']['num_habilitation'] = $demande->getKey();
            $habilitation['dr_cdc'] = array();
            $habilitation['dr_cdc'][] = array('dr_cdc_id' => $certipaq_produit->dr_cdc_id);
            $habilitation['date_dossier_complet'] = $h->date;
            $habilitations[] = $habilitation;
        }
        return $habilitations;
    }

    public function fillHabilitations($habilitation) {
        $habilitations = array();
        foreach($habilitation->declaration as $k => $declaration) {
            $certipaq_produit = CertipaqDeroulant::getInstance()->getCertipaqProduitFromConfigurationProduit($declaration->getConfig());
            if ($certipaq_produit) {
                foreach($declaration->activites as $activite_nom => $activite) {
                    if (!$activite->statut) {
                        continue;
                    }
                    $habilitation = array();
                    $a = CertipaqDeroulant::getInstance()->findActivite($activite_nom);
                    $habilitation['dr_activites_operateurs'] = $a;
                    $habilitation['dr_activites_operateurs_id'] = $a->id;
                    $habilitation['dr_cdc_id'] = $certipaq_produit->dr_cdc_id;
                    $habilitation['dr_cdc'] = CertipaqDeroulant::getInstance()->keyid2obj('dr_cdc_id', $certipaq_produit->dr_cdc_id);
                    $habilitation['dr_cdc_famille_id'] = $certipaq_produit->dr_cdc_famille_id;
                    $habilitation['dr_cdc_famille'] = CertipaqDeroulant::getInstance()->keyid2obj('dr_cdc_famille_id', $certipaq_produit->dr_cdc_famille_id);
                    $habilitation['outil_production'] = array();
                    //$habilitation['dr_statut_habilitation_raw'] = $activite;
                    $habilitation['dr_statut_habilitation'] = CertipaqDeroulant::getInstance()->findHabilitation($activite->statut);
                    $habilitation['dr_statut_habilitation_id'] = $habilitation['dr_statut_habilitation']->id;
                    $habilitation['date_decision'] = $activite->date;
                    $habilitation['date_dossier_complet_odg'] = '';
                    $habilitation['outil_production'] = array('');
                    $habilitations[] = $habilitation;
                }
            }
        }
        return $habilitations;
    }

    public function getOperateurFromHabilitation($habilitation) {
        $param = $this->fillOperateur($habilitation);
        $param['sites'] = $this->fillSites($habilitation);
        $param['sites'][0]['habilitations'] = $this->fillHabilitations($habilitation);
        $h = (array) $param['sites'][0]['habilitations'];
        usort($h, "CertipaqDI::orderHabilitations");
        $param['sites'][0]['habilitations'] = $h;
        return $param;
    }

    public function getParamNouvelOperateurFromDemande($demande) {
        $habilitation = $demande->getDocument();
        $param = array();
        $param['dr_demande_identification_type_id'] = "declaration_identification_nouvel_operateur_request";
        $param['operateur'] = $this->fillOperateur($habilitation);
        $param['adresses'] = $this->fillAdresses($habilitation);
        $param['sites'] = $this->fillSites($habilitation);
        $param['habilitations'] = $this->fillHabilitationsFromDemande($demande);
        $param['informations_autres'] = $demande->commentaire;
        return $param;
    }

    public function getParamExtentionHabilitationFromDemande($demande) {
        $param = array();
        $param["dr_demande_identification_type_id"] = "declaration_identification_extension_habilitation_request";
        $param["operateur_id"] = 0;
        $param['habilitations'] = $this->fillHabilitationsFromDemande($demande);
        $param['informations_autres'] = $demande->commentaire;
        return $param;
    }

    public function getParamNouveauSiteFromDemande($demande) {
        $habilitation = $demande->getDocument();
        $param = array();
        $param["dr_demande_identification_type_id"] =  "declaration_identification_nouveau_site_production_request";
        $param["operateur_id"] =  0;
        $param['sites'] = $this->fillSites($habilitation);
        $param['habilitations'] = $this->fillHabilitationsFromDemande($demande);
        $param['informations_autres'] = $demande->commentaire;
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
        $param["nom_outil"] = "";
        $param["portee_habilitation"] = "";
        $h = $demande->getExtractHistoriqueFromStatut('COMPLET');
        $param["date_dossier_complet"] = $h->date;
        $param["objet_modification"] = $demande->commentaire;
        $param["informations_autres"] = $demande->commentaire;
        return $param;
    }

    public static function orderHabilitations($a, $b) {
        $a = (object) $a;
        $b = (object) $b;
        $a_order_libelle = $a->dr_cdc->libelle.$a->dr_activites_operateurs->libelle.$a->dr_statut_habilitation->cle;
        $b_order_libelle = $b->dr_cdc->libelle.$b->dr_activites_operateurs->libelle.$b->dr_statut_habilitation->cle;
        return strcmp($a_order_libelle, $b_order_libelle);
    }

}