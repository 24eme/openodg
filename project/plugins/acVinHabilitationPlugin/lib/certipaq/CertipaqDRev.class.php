<?php

class CertipaqDRev extends CertipaqService
{
    public function list($params = [])
    {
        return $this->query('declaration/revendication', 'GET', $params);
    }

    public function find($id)
    {
        $endpoint = str_replace('{id_declaration}', $id, self::ENDPOINT_RECUPERATION);
        return $this->query($endpoint);
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

        return $this->query('declaration/revendication', 'POST', $params);
    }
}
