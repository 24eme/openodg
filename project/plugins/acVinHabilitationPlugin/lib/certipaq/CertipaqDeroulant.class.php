<?php

class CertipaqDeroulant extends CertipaqService
{
    public const ACTIVITE_PRODUCTEUR = "Producteur de raisins";
    public const ACTIVITE_VINIFICATEUR = "Vinificateur";
    public const ACTIVITE_VENTE_VRAC = "Vente de vin en vrac";

    private function res2hashid($res) {
        $objs = array();
        foreach($res as $o) {
            $objs[$o->id] = $o;
        }
        return $objs;
    }

    private function queryAndRes2hashid($endpoint) {
        $res = $this->queryWithCache($endpoint);
        return $this->res2hashid($res);
    }

    public function getListeActivitesOperateurs()
    {
        return $this->queryAndRes2hashid('dr/activites_operateurs');
    }

    public function getListeTypeControle()
    {
        return $this->queryAndRes2hashid('dr/type_controle');
    }

    public function getListeHabilitation()
    {
        return $this->queryAndRes2hashid('dr/statut_habilitation');
    }

    public function getListeCahiersDesCharges()
    {
        return $this->queryAndRes2hashid('dr/cdc');
    }

    public function getListeFamilleCahiersDesCharges()
    {
        return $this->queryAndRes2hashid('dr/cdc_famille');
    }

    public function getListeDRInfo()
    {
        return $this->queryAndRes2hashid('dr/infos');
    }

    public function getListeDREtatDemande()
    {
        return $this->queryAndRes2hashid('dr/etat_demande');
    }

    public function getListeProduitsCahiersDesCharges() {
        return $this->queryAndRes2hashid('dr/cdc_produit');
    }

    public function keyid2obj($k, $id, $obj = null) {
        $hash = array();
        switch ($k) {
            case 'dr_statut_habilitation_id':
                $hash = $this->getListeHabilitation();
                break;
            case 'dr_cdc_id':
                $hash = $this->getListeCahiersDesCharges();
                break;
            case 'dr_activites_operateurs_id':
                $hash = $this->getListeActivitesOperateurs();
                break;
            case 'dr_cdc_famille_id':
                $hash = $this->getListeFamilleCahiersDesCharges();
                break;
            case 'dr_etat_demande_id':
                $hash = $this->getListeDREtatDemande();
                break;
            case 'dr_infos_id':
                $hash = $this->getListeDRInfo();
                break;
            case 'dr_cdc_produit_id':
                $hash = $this->getListeProduitsCahiersDesCharges();
                break;
            case 'operateur_id':
                $o = CertipaqOperateur::getInstance()->find($id);
                $hash[$id] = $o;
                break;
            case 'operateurs_sites_id':
            case 'entrepot_operateurs_sites_id':
                if ($obj) {
                    $hash[$id] = CertipaqOperateur::getInstance()->getSiteFromIdAndOperateur($id, $obj);
                }
        }
        if (isset($hash[$id])) {
            return $hash[$id];
        }
        return null;
    }

    public function getCertipaqProduitFromConfigurationProduit($conf) {
        $produits = $this->getListeProduitsCahiersDesCharges();
        foreach($produits as $p) {
            if ($p->libelle == $conf->getLibelleComplet()) {
                return $p;
            }
        }
        foreach($produits as $p) {
            $c = $this->getConfigurationProduitFromProduitId($p->id);
            if ($c->getLibelleComplet() == $conf->getLibelleComplet()) {
                return $p;
            }
        }
        return null;
    }

    public function getConfigurationProduitFromProduitId($pid) {
        $produits = $this->getListeProduitsCahiersDesCharges();
        if (!isset($produits[$pid]) || !$produits[$pid]) {
            return null;
        }
        return ConfigurationClient::getCurrent()->identifyProductByLibelle($produits[$pid]->libelle);
    }
}
