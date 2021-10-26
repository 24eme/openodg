<?php

class CertipaqDRev extends CertipaqService
{
    public function list($params = [])
    {
        return $this->query('declaration/revendication', 'GET', $params);
    }

    public function findbyOperateurAndMillesime($operateur_certipaq_id, $millesime) {
        $param = array();
        $param['operateur_id'] = $operateur_certipaq_id;
        $param['millesime'] = array("$millesime");
        $res = $this->list($param);
        $drevs = array();
        foreach ($res as $d) {
            $drevs[] = $this->keys2obj($d);
        }
        return $drevs;
    }

    private function keys2obj($line) {
        $line->operateur = CertipaqDeroulant::getInstance()->keyid2obj('operateur_id', $line->operateur_id);
        $line->dr_cdc_famille = CertipaqDeroulant::getInstance()->keyid2obj('dr_cdc_famille_id', $line->dr_cdc_famille_id);
        $line->dr_cdc = CertipaqDeroulant::getInstance()->keyid2obj('dr_cdc_id', $line->dr_cdc_id);
        $line->dr_cdc_produit = CertipaqDeroulant::getInstance()->keyid2obj('dr_cdc_produit_id', $line->dr_cdc_produit_id);
        $line->dr_etat_demande = CertipaqDeroulant::getInstance()->keyid2obj('dr_etat_demande_id', $line->dr_etat_demande_id);
        $line->operateurs_sites = CertipaqDeroulant::getInstance()->keyid2obj('operateurs_sites_id', $line->operateurs_sites_id, $line->operateur);
        $line->entrepot_operateurs_sites = CertipaqDeroulant::getInstance()->keyid2obj('operateurs_sites_id', $line->entrepot_operateurs_sites_id, $line->operateur);
        return $line;
    }


    public function find($id)
    {
        $endpoint = 'declaration/revendication/{id_declaration}';
        $endpoint = str_replace('{id_declaration}', $id, $endpoint);
        $line = $this->query($endpoint);
        return $this->keys2obj($line);
    }

    public function createUneLigne($etablissement, $produit_conf, $millesime, $superficie, $volume) {
        $operateur = CertipaqOperateur::getInstance()->findByEtablissement($etablissement);
        if (!$operateur) {
            throw new sfException('Opérateur non reconnu pour '.$etablissement->cvi." / ".$etablissement->siret);
        }
        $produit = CertipaqDeroulant::getInstance()->getCertipaqProduitFromConfigurationProduit($produit_conf);
        if (!$produit) {
            throw new sfException('Produit non reconnu pour '.$produit_conf->getLibelleComplet());
        }
        $habilitation = CertipaqOperateur::getInstance()->getHabilitationFromOperateurProduitAndActivite($operateur, $produit, CertipaqDeroulant::ACTIVITE_PRODUCTEUR);

        $params = array();
        $params['operateur_id'] = $operateur->id;
        $params['operateurs_sites_id'] = $habilitation->site_id;
        $params['dr_cdc_famille_id'] = $habilitation->dr_cdc_famille_id;
        $params['dr_cdc_id'] = $habilitation->dr_cdc->id;
        $params['millesime'] = "$millesime";
        $params['volume_hl'] = floatval($volume);
        $params['surface_ha'] = floatval($superficie);
        $params['dr_cdc_produit_id'] = $habilitation->dr_cdc_produit_id;
        $params['entrepot_operateurs_sites_id'] = $habilitation->site_id;

        return $this->query('declaration/revendication', 'POST', $params);
    }
}
