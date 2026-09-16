<?php

class CertipaqDRev extends CertipaqService
{
    public function list($params = [])
    {
        return $this->query('declaration/revendication', 'GET', $params);
    }

    public function findbyOperateurIdAndMillesime($operateur_certipaq_id, $millesime) {
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

    public function createUneLigne($declarant, $produit_conf, $data) {
        $operateur = CertipaqOperateur::getInstance()->findByEtablissement($declarant);
        if (!$operateur) {
            throw new sfException('Opérateur non reconnu pour '.$declarant->cvi." / ".$declarant->siret);
        }
        $produit = CertipaqDeroulant::getInstance()->getCertipaqProduitFromConfigurationProduit($produit_conf);
        if (!$produit) {
            throw new sfException('Produit non reconnu pour '.$produit_conf->getLibelleComplet());
        }
        $habilitation = CertipaqOperateur::getInstance()->getHabilitationFromOperateurProduitAndActivite($operateur, $produit, CertipaqDeroulant::ACTIVITE_PRODUCTEUR);

        if (!isset($data['millesime']) || !isset($data['volume']) || !isset($data['superficie'])) {
            throw new sfException("millesime, volume et superficie manquand dans l'argument $data");
        }

        $params = array();
        $params['operateur_id'] = $operateur->id;
        $params['operateurs_sites_id'] = $habilitation->site_id;
        $params['dr_cdc_famille_id'] = $habilitation->dr_cdc_famille_id;
        $params['dr_cdc_id'] = $habilitation->dr_cdc->id;
        $params['dr_cdc_produit_id'] = $produit->id;
        $params['millesime'] = sprintf("%d", $data['millesime']);
        $params['volume_hl'] = floatval($data['volume']);
        $params['surface_ha'] = floatval($data['superficie']);
        if (isset($data['observations'])) {
            $params['observations'] = $data['observations'];
        }
        if (isset($data['volume_complementaire_individuel_hl'])) {
            $params['volume_complementaire_individuel_hl'] = $data['volume_complementaire_individuel_hl'];
        }
        if (isset($data['logement'])){
            $params['logement'] = $data['logement'];
        }
        if (isset($data['autre_site_stockage'])){
            $params['autre_site_stockage'] = $data['autre_site_stockage'];
        }
        if (isset($data['cepages'])){
            throw new sfException('pas implémenté');
        }
        $params['dr_cdc_produit_id'] = $habilitation->dr_cdc_produit_id;
        $params['entrepot_operateurs_sites_id'] = $habilitation->site_id;

        return $this->query('declaration/revendication', 'POST', $params);
    }

    public function createDRev($drev) {
        $res = [];
        foreach($drev->getProduits() as $prod) {
            $res[] = $this->createDRevLigne($prod);
        }
        return $res;
    }

    protected function createDRevLigne($drev_produit) {
        $data = [];
        $data['volume'] = $drev_produit->volume_revendique_total;
        $data['superficie'] = $drev_produit->superficie_revendique;
        $data['millesime'] = $drev_produit->getDocument()->periode;
        if ($drev_produit->denomination_complementaire) {
            $data['observations'] = $drev_produit->denomination_complementaire;
        }
        if ($drev_produit->volume_revendique_issu_vci) {
            //Dont VCI
            $data['volume_complementaire_individuel_hl'] = $drev_produit->volume_revendique_issu_vci;
        }
        return $this->createUneLigne($drev_produit->getDocument()->declarant, $drev_produit->getConfig(), $data);
    }
}
