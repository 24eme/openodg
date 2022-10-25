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
        $obj['pays'] = $compte->getPays();
    }

    private function fillAdresseAndContact(&$obj, $compte) {
        $this->fillAdresse($obj, $compte);
        $obj['telephone'] = ($compte->getTelephoneBureau()) ? $compte->getTelephoneBureau() : $compte->getTelephonePerso();
        $obj['portable'] = $compte->getTelephoneMobile();
        $obj['email'] = $compte->getEmail();
    }

    private function fillOperateur($habilitation, $with_contact = true) {
        $etablissement = $habilitation->getEtablissementObject();
        $operateur = array();
        $operateur['raison_sociale'] = $etablissement->raison_sociale;
        $operateur['nom_entreprise'] = $etablissement->nom;
        $operateur['siret'] = $etablissement->getSiret();
        $operateur['cvi'] = $etablissement->getCvi();
        if ($with_contact) {
            $this->fillAdresseAndContact($operateur, $etablissement);
        }
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

    public function fillSitesHabilite($habilitation) {
        $sites = array();
        $sites[0]['nom_site'] = $habilitation->getEtablissementObject()->getNom();
        $sites[0]['capacite_cuverie'] = null;
        $this->fillAdresseAndContact($sites[0], $habilitation->getEtablissementObject());
        return $sites;
    }

    public function addSitesHabilites(& $sites, $habilitation) {
        $etablissement = $habilitation->getEtablissementObject();
        if ($etablissement->exist('chais')) {
            foreach($habilitation->getChaisHabilite() as $chais) {
                $sites[] = $this->fillSiteFromChais($chais);
            }
        }
        return $sites;
    }

    public function addSitesNonHabilites(& $sites, $habilitation) {
        $etablissement = $habilitation->getEtablissementObject();
        if ($etablissement->exist('chais')) {
            foreach($habilitation->getChaisSansHabilitation() as $chais) {
                $sites[] = $this->fillSiteFromChais($chais);
            }
        }
        return $sites;
    }

    public function fillSiteFromChais($chais) {
        $site = array();
        $site['nom_site'] = $chais->nom;
        $site['adresse'] = $chais->adresse;
        $site['cp'] = $chais->code_postal;
        $site['ville'] = $chais->commune;
        $site['latitude'] = $chais->lat;
        $site['longitude'] = $chais->lon;
        if ($chais->archive) {
            $site['commentaire'] = "Archivé";
        }
        $site['outils_production'] = array();
        foreach($chais->attributs as $ka => $a) {
            $site['outils_production'] = array(
                'nom_outil' => $a
            );
        }
        return $site;
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

    public function fillHabilitations($habilitation, $site = '') {
        $habilitations = array();
        foreach($habilitation->declaration as $k => $declaration) {
            $certipaq_produit = CertipaqDeroulant::getInstance()->getCertipaqProduitFromConfigurationProduit($declaration->getConfig());
            if ($certipaq_produit) {
                foreach($declaration->activites as $activite_nom_site => $activite) {
                    $activite_nom = explode('-', $activite_nom_site)[0];
                    $activite_site = ($activite->exist('site') && $activite->site) ? $activite->site : '';
                    if (!$activite->statut || $site != $activite_site) {
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
                    $habilitation['order'] =  CertipaqDI::getHabilitationOrderString( (object) $habilitation );
                    $habilitations[] = $habilitation;
                }
            }
        }
        $habilitation = (array) $habilitation;
        usort($habilitations, "CertipaqDI::orderHabilitations");
        return $habilitations;
    }

    public function getOperateurFromHabilitation($habilitation) {
        $param = $this->fillOperateur($habilitation);
        $param['sites'] = $this->fillSitesHabilite($habilitation);
        $param['sites'][0]['habilitations'] = $this->fillHabilitations($habilitation);
        $this->addSitesHabilites($param['sites'], $habilitation);
        for($i = 1 ; $i < count($param['sites']) ; $i++) {
            $param['sites'][$i]['habilitations'] = $this->fillHabilitations($habilitation, $param['sites'][$i]['nom_site']);
        }
        $this->addSitesNonHabilites($param['sites'], $habilitation);
        return $param;
    }

    public function getCertipaqParam($type, $demande) {
        switch ($type) {
            case 'declaration_identification_nouvel_operateur_request':
                return $this->getParamNouvelOperateurFromDemande($demande);

            case 'declaration_identification_extension_habilitation_request':
                return $this->getParamExtentionHabilitationFromDemande($demande);

            case 'declaration_identification_nouveau_site_production_request':
                return $this->getParamNouveauSiteFromDemande($demande);

            case 'declaration_identification_modification_identite_request':
                return $this->getParamModificationIdentiteFromDemande($demande);

            case 'declaration_identification_modification_outil_request':
                return $this->getParamModificationOutilFromDemande($demande);

            default:
                throw new sfException("Type certipaq non connu");
        }
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
        $param['sites'] = $this->fillSitesHabilite($habilitation);
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
        return strcmp($a->order, $b->order);
    }
    
    public static function getHabilitationOrderString($hab) {
        return $hab->dr_cdc->libelle.'|'.$hab->dr_activites_operateurs->libelle.'|'.$hab->dr_statut_habilitation->cle;
    }

}