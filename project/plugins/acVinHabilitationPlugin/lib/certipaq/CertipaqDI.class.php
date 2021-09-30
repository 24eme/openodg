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

    public function sendNouvelOperateur() {
        $param = array();
        $param['dr_demande_identification_type_id'] = "declaration_identification_nouvel_operateur_request";
        $param['operateur'] = array();
        $param['operateur']['raison_sociae'] = '';
        $param['operateur']['nom_entreprise'] = '';
        $param['operateur']['siree'] = '';
        $param['operateur']['cve'] = '';
        $param['operateur']['adrese'] = '';
        $param['operateur']['complement_adresse'] = '';
        $param['operateur']['code_poste'] = '';
        $param['operateur']['vile'] = '';
        $param['operateur']['paye'] = '';
        $param['operateur']['telephone'] = '';
        $param['operateur']['portable'] = '';
        $param['operateur']['fae'] = '';
        $param['operateur']['emaie'] = '';
        $param['operateur']['observations'] = '';
        $param['adresses'] = array();
        $param['adresses'][0] = array();
        $param['adresses'][0]['dr_adresse_type_id'] = '';
        $param['adresses'][0]['adresse'] = '';
        $param['adresses'][0]['complement_adresse	'] = '';
        $param['adresses'][0]['ville'] = '';
        $param['adresses'][0]['pays	'] = '';
        $param['adresses'][0]['observations'] = '';
        $param['sites'] = array();
        $param['sites'][0] = array();
        $param['sites'][0]['nom_site'] = '';
        $param['sites'][0]['nom_site_complement'] = '';
        $param['sites'][0]['adresse'] = '';
        $param['sites'][0]['complement_adresse'] = '';
        $param['sites'][0]['code_postal'] = '';
        $param['sites'][0]['ville'] = '';
        $param['sites'][0]['pays'] = '';
        $param['sites'][0]['localisation'] = '';
        $param['sites'][0]['telephone'] = '';
        $param['sites'][0]['portable'] = '';
        $param['sites'][0]['fax'] = '';
        $param['sites'][0]['observations'] = '';
        $param['habilitations'] = array();
        $param['habilitations'][0] = array();
        $param['habilitations'][0]['dr_cdc_famille_id'] = '';
        $param['habilitations'][0]['dr_activites_operateurs'] = array();
        $param['habilitations'][0]['dr_activites_operateurs']['dr_activites_operateurs_id'] = '';
        $param['habilitations'][0]['dr_activites_operateurs']['num_habilitation'] = '';
        $param['habilitations'][0]['dr_activites_operateurs']['dr_infos_id'] = '';
        $param['habilitations'][0]['outils_production'] = array();
        $param['habilitations'][0]['outils_production'][0] = array();
        $param['habilitations'][0]['outils_production'][0]['nom_outil'] = '';
        $param['habilitations'][0]['outils_production'][0]['portee_habilitation'] = '';
        $param['habilitations'][0]['outils_production'][0]['commentaire'] = '';
        $param['habilitations'][0]['dr_cdc'] = array();
        $param['habilitations'][0]['dr_cdc'][0] = array();
        $param['habilitations'][0]['dr_cdc'][0]['dr_cdc_id'] = '';
        $param['habilitations'][0]['dr_cdc'][0]['outils_production'] = array();
        $param['habilitations'][0]['date_dossier_complet'] = '';
        $param['personnels'] = array();
        $param['personnels'][0] = array();
        $param['personnels'][0]['dr_civilite_id'] = '';
        $param['personnels'][0]['nom'] = '';
        $param['personnels'][0]['prenom'] = '';
        $param['personnels'][0]['initiales'] = '';
        $param['personnels'][0]['num_ppm'] = '';
        $param['personnels'][0]['adresse'] = '';
        $param['personnels'][0]['complement_adresse'] = '';
        $param['personnels'][0]['code_postal'] = '';
        $param['personnels'][0]['ville'] = '';
        $param['personnels'][0]['pays'] = '';
        $param['personnels'][0]['telephone'] = '';
        $param['personnels'][0]['portable'] = '';
        $param['personnels'][0]['fax'] = '';
        $param['personnels'][0]['email'] = '';
        $param['personnels'][0]['dr_fonction_id'] = '';
        $param['informations_autres'] = '';
    }

    public function sendExtentionHabilitation() {
        $param = array();
        $param["dr_demande_identification_type_id"] = "declaration_identification_extension_habilitation_request";
        $param["operateur_id"] = 0;
        $param['habilitations'] = array();
        $param['habilitations'][0] = array();
        $param['habilitations'][0]['dr_cdc_famille_id'] = '';
        $param['habilitations'][0]['dr_activites_operateurs'] = array();
        $param['habilitations'][0]['dr_activites_operateurs']['dr_activites_operateurs_id'] = '';
        $param['habilitations'][0]['dr_activites_operateurs']['num_habilitation'] = '';
        $param['habilitations'][0]['dr_activites_operateurs']['dr_infos_id'] = '';
        $param['habilitations'][0]['outils_production'] = array();
        $param['habilitations'][0]['outils_production'][0] = array();
        $param['habilitations'][0]['outils_production'][0]['nom_outil'] = '';
        $param['habilitations'][0]['outils_production'][0]['portee_habilitation'] = '';
        $param['habilitations'][0]['outils_production'][0]['commentaire'] = '';
        $param['habilitations'][0]['dr_cdc'] = array();
        $param['habilitations'][0]['dr_cdc'][0] = array();
        $param['habilitations'][0]['dr_cdc'][0]['dr_cdc_id'] = '';
        $param['habilitations'][0]['dr_cdc'][0]['outils_production'] = array();
        $param['habilitations'][0]['date_dossier_complet'] = '';
    }

    public function sendNouveauSite() {
        $param = array();
        $param["dr_demande_identification_type_id"] =  "declaration_identification_nouveau_site_production_request";
        $param["operateur_id"] =  0;
        $param['sites'] = array();
        $param['sites'][0] = array();
        $param['sites'][0]['nom_site'] = '';
        $param['sites'][0]['nom_site_complement'] = '';
        $param['sites'][0]['adresse'] = '';
        $param['sites'][0]['complement_adresse'] = '';
        $param['sites'][0]['code_postal'] = '';
        $param['sites'][0]['ville'] = '';
        $param['sites'][0]['pays'] = '';
        $param['sites'][0]['localisation'] = '';
        $param['sites'][0]['telephone'] = '';
        $param['sites'][0]['portable'] = '';
        $param['sites'][0]['fax'] = '';
        $param['sites'][0]['observations'] = '';
        $param['habilitations'] = array();
        $param['habilitations'][0] = array();
        $param['habilitations'][0]['dr_cdc_famille_id'] = '';
        $param['habilitations'][0]['dr_activites_operateurs'] = array();
        $param['habilitations'][0]['dr_activites_operateurs']['dr_activites_operateurs_id'] = '';
        $param['habilitations'][0]['dr_activites_operateurs']['num_habilitation'] = '';
        $param['habilitations'][0]['dr_activites_operateurs']['dr_infos_id'] = '';
        $param['habilitations'][0]['outils_production'] = array();
        $param['habilitations'][0]['outils_production'][0] = array();
        $param['habilitations'][0]['outils_production'][0]['nom_outil'] = '';
        $param['habilitations'][0]['outils_production'][0]['portee_habilitation'] = '';
        $param['habilitations'][0]['outils_production'][0]['commentaire'] = '';
        $param['habilitations'][0]['dr_cdc'] = array();
        $param['habilitations'][0]['dr_cdc'][0] = array();
        $param['habilitations'][0]['dr_cdc'][0]['dr_cdc_id'] = '';
        $param['habilitations'][0]['dr_cdc'][0]['outils_production'] = array();
        $param['habilitations'][0]['date_dossier_complet'] = '';
        $param["informations_autres"] =  '';
    }

    public function sendModificationIdentite() {
        $param = array();
        $param["dr_demande_identification_type_id"] = "declaration_identification_modification_identite_request";
        $param['operateur']["id"] = 0;
        $param['operateur']["objet_modification"] = "";
        $param['operateur']["raison_sociale"] = "";
        $param['operateur']["nom_entreprise"] = "";
        $param['operateur']["siret"] = '';
        $param["informations_autres"] = '';
    }

    public function sendModificationOutil() {
        $param = array();
        $param["dr_demande_identification_type_id"] = "declaration_identification_modification_outil_request";
        $param["id"] = 0;
        $param["objet_modification"] = "";
        $param["nom_outil"] = "";
        $param["portee_habilitation"] = "";
        $param["date_dossier_complet"] = "2019-08-24";
        $param["informations_autres"] = "";
    }

}