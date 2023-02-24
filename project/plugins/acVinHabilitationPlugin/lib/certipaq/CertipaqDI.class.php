<?php

class CertipaqDI extends CertipaqDeroulant
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

    private function fillAdresse(&$obj, $compte) {
        $obj['adresse'] = $compte->getAdresse();
        $obj['complement_adresse'] = $compte->getAdresseComplementaire();
        $obj['code_postal'] = $compte->getCodePostal();
        $obj['ville'] = $compte->getCommune();
        $obj['pays'] = $compte->getPaysISO();
    }

    private function fillAdresseAndContact(&$obj, $compte) {
        $this->fillAdresse($obj, $compte);
        $obj['telephone'] = ($compte->getTelephoneBureau()) ? $compte->getTelephoneBureau() : $compte->getTelephonePerso();
        if ($compte->getTelephoneMobile()) {
            $obj['portable'] = $compte->getTelephoneMobile();
        }
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
            if (!$certipaq_produit->dr_cdc_famille_id || !$certipaq_produit->dr_cdc_id) {
                throw new sfException("Information liée au cahier des charges ".$demande->getConfig()->libelle." non disponible depuis l'API");
            }
            $habilitation = array();
            $habilitation['dr_cdc_famille_id'] = $certipaq_produit->dr_cdc_famille_id;
            $habilitation['dr_activites_operateurs'] = array();
            $a = CertipaqDeroulant::getInstance()->findActivite($activite);
            $habilitation['dr_activites_operateurs'][0] = array();
            $habilitation['dr_activites_operateurs'][0]['dr_activites_operateurs_id'] = $a->id;
            $habilitation['dr_activites_operateurs'][0]['num_habilitation'] = $demande->getKey();
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

    public function postRequest($type, $demande) {
        $args = $this->getCertipaqParam($type, $demande);
        $res = $this->queryAndRes2hashid('declaration/identification', 'POST', $args);
        if (!$res || !is_array($res)) {
            throw sfException("Request failed: ".$res);
        }
        $id = array_shift($res);
        $demande->add('oc_demande_id', $id);
        $demande->getDocument()->addHistorique("Demande Certipaq pour \"".$demande->libelle."\"", "Numéro de demande ".$id, null, "Crée");
        $demande->getDocument()->save();
        return $id;
    }

    public function getCertipaqParam($type, $demande, $tobesubmitted = true) {
        switch ($type) {
            case 1:
            case 'Un nouvel opérateur':
            case 'declaration_identification_nouvel_operateur_request':
                return $this->getParamNouvelOperateurFromDemande($demande, $tobesubmitted);

            case 2:
            case "Une reprise d'un opérateur":
            case 'declaration_identification_extension_habilitation_request':
                throw new sfException("« reprise d'un opérateur » pas implémenté");

            case 3:
            case "Une demande de complément ou d'extension d'habilitation":
            case 'declaration_identification_modification_identite_request':
                return $this->getParamExtentionHabilitationFromDemande($demande, $tobesubmitted);

            case 4:
            case "Une demande d'habilitation pour un nouveau site de production":
            case 'declaration_identification_nouveau_site_production_request':
                return $this->getParamNouveauSiteFromDemande($demande, $tobesubmitted);

            case 5:
            case "Une modification de raison sociale et/ou de n° de SIRET et/ou n° CVI":
                return $this->getParamRSSIRETCVIFromDemande($demande, $tobesubmitted);

            case 6:
            case "Une modification d'un outil de production'":
            case 'declaration_identification_modification_outil_request':
                throw new sfException("« modification d'un outil de production » pas implémenté");

            default:
                throw new sfException("Type certipaq non connu");
        }
    }

    public function getParamNouvelOperateurFromDemande($demande, $tobesubmitted = true) {
        $habilitation = $demande->getDocument();
        $param = array();
        $param['dr_demande_identification_type_id'] = 1;
        $param['operateur'] = $this->fillOperateur($habilitation);
        $param['adresses'] = $this->fillAdresses($habilitation);
        $param['sites'] = [];// $this->fillSitesHabilite($habilitation);
        $param['habilitations'] = array($this->fillHabilitationsFromDemande($demande));
        $param['informations_autres'] = $demande->commentaire.' Id OpenOdg:['.$demande->getKey().']';
        return $param;
    }

    public function getParamExtentionHabilitationFromDemande($demande, $tobesubmitted = true) {
        $param = array();
        $param["dr_demande_identification_type_id"] = 3;
        $operateur = CertipaqOperateur::getInstance()->findByEtablissement($demande->getDocument()->getEtablissementObject());
        if (!$operateur) {
            throw new sfException("Opérateur non trouvé dans l'API Certipaq");
        }
        if ($demande->exist('site') || count($operateur->sites) > 1) {
            throw new sfException("multi site non géré");
        }
        $param["operateur_id"] = $operateur->id;
        $param['habilitations'] = array($this->fillHabilitationsFromDemande($demande));
        for($i = 0 ; $i < count($param['habilitations'][0]) ; $i++) {
            if ($tobesubmitted) {
                $param['habilitations'][0][$i]['id'] = $operateur->sites[0]->id;
            }else{
                $param['habilitations'][0][$i]['site_id'] = $operateur->sites[0]->id;
            }
        }
        $param['informations_autres'] = $demande->commentaire.' Id OpenOdg:['.$demande->getKey().']';
        return $param;
    }

    public function getParamNouveauSiteFromDemande($demande, $tobesubmitted = true) {
        $habilitation = $demande->getDocument();
        $param = array();
        $param["dr_demande_identification_type_id"] =  4;
        $operateur = CertipaqOperateur::getInstance()->findByEtablissement($demande->getDocument()->getEtablissementObject());
        if ($demande->exist('site') || count($operateur->sites) > 1) {
            throw new sfException("multi site non géré");
        }
        $param["operateur_id"] = $operateur->id;
        $param['site'] = array_shift($this->fillSitesHabilite($habilitation));
        $param['habilitations'] = $this->fillHabilitationsFromDemande($demande);
        $param['informations_autres'] = $demande->commentaire.' Id OpenOdg:['.$demande->getKey().']';
        return $param;
    }

    public function getParamRSSIRETCVIFromDemande($demande, $tobesubmitted = true) {
        $habilitation = $demande->getDocument();
        $param = array();
        $param["dr_demande_identification_type_id"] =  5;
        $operateur = CertipaqOperateur::getInstance()->findByEtablissement($demande->getDocument()->getEtablissementObject());
        if ($demande->exist('site') || count($operateur->sites) > 1) {
            throw new sfException("multi site non géré");
        }
        $param["operateur"] = $this->fillOperateur($demande->getDocument(), false);
        $param["operateur"]["id"] = $operateur->id;
        if ($demande->commentaire) {
            $param["operateur"]["objet_modification"] = $demande->commentaire;
        }else{
            $param["operateur"]["objet_modification"] = "Modification Raison sociale / SIRET / CVI";
        }
        $param['informations_autres'] = $demande->commentaire.' Id OpenOdg:['.$demande->getKey().']';
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

    public function getDemandesIdentification($type = null, $date_debut = null, $date_fin = null, $etat = null) {
        $param = array();
        if ($type) {
            $param['dr_demande_identification_type_id'] = $type;
        }
        if ($date_debut && $date_fin) {
            $param['date'] = array($date_debut, $date_fin);
        }
        if ($etat) {
            $param['dr_etat_demande_id'] = $etat;
        }
        return $this->queryAndRes2hashid('declaration/identification', 'GET', $param);
    }

    public function getDemandeIdentification($id) {
        return $this->queryAndRes2hashid('declaration/identification/'.$id);
    }

    public function getDocumentForDemandeIdentification($demande) {
        $id = $demande->oc_demande_id;
        return $this->queryAndRes2hashid('declaration/identification/'.$id.'/req_docs');
    }

    public function sendFichierForDemandeIdentification($demande, $fichier, $type_document = 0, $cdc_famille_id = null) {
        $param = array();
        $param['dr_type_documents_id'] = $type_document;
        $type_document = $this->keyid2obj('dr_type_documents_id', $type_document);
        if ($cdc_famille_id) {
            $param['dr_cdc_famille_id'] = $cdc_famille_id;
        }
        $id = $demande->oc_demande_id;
        $ret = $this->query('declaration/identification/'.$id.'/document', 'POST', $param, array('fichier' => $fichier));
        $demande->getDocument()->addHistorique("Fichier ".$type_document->libelle." à Certipaq pour \"".$demande->libelle."\"", $id.' / '.$fichier['file_name'], null, 'Envoyé');
        $demande->getDocument()->save();
        return $ret;
    }

    public function submitDemandeIdentification($demande) {
        $id = $demande->oc_demande_id;
        $ret = $this->queryAndRes2hashid('declaration/identification/'.$id.'/submit', 'POST');
        $demande->getDocument()->addHistorique("Demande Certipaq pour \"".$demande->libelle."\"", $id, null, "Transmise");
        $demande->getDocument()->save();
        return $ret;
    }

}