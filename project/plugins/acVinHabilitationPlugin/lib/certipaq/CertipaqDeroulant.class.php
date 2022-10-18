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
        /*
        (  [id] => 211 , [libelle] => ODG ),
        (  [id] => 355 , [libelle] => Conditionneur ),
        (  [id] => 461 , [libelle] => Vinificateur ),
        (  [id] => 462 , [libelle] => Producteur de raisins ),
        (  [id] => 498 , [libelle] => Vente de vin à la tireuse ),
        (  [id] => 528 , [libelle] => Vente de vin en vrac ),
        */
        return $this->queryAndRes2hashid('dr/activites_operateurs');
    }

    public function findActivite($activite_libelle) {
        $activites = $this->getListeActivitesOperateurs();
        foreach (explode(' ', $activite_libelle) as $mot) {
            if (strpos(strtoupper($mot), 'TIREUSE') !== false) {
                $mot = 'TIREUSE';
            }
            $to_delete = array();
            foreach($activites as $id => $a) {
                if (strpos(strtoupper($a->libelle), strtoupper($mot)) === false) {
                    $to_delete[] = $id;
                }
            }
            foreach($to_delete as $id) {
                unset($activites[$id]);
            }
        }
        if (count($activites) == 1) {
            return array_values($activites)[0];
        }
        return null;
    }

    public function findHabilitation($habilitation_libelle) {
        $habs = $this->getListeHabilitation();
        foreach($habs as $id => $h) {
            if (
                    strpos(strtoupper($h->libelle), strtoupper($habilitation_libelle)) === false
                  &&
                    strpos(strtoupper($h->cle), strtoupper($habilitation_libelle)) === false
                ) {
                $to_delete[] = $id;
            }
        }
        foreach($to_delete as $id) {
            unset($habs[$id]);
        }
        if (count($habs) == 1) {
            return array_values($habs)[0];
        }
        return null;
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
        /*
        Array
        (    [21] => stdClass Object (
                [id] => 21
                [dr_cdc_id] => 677
                [dr_couleur_id] => 1
                [libelle] => Crozes Hermitage Rouge
                [dr_cdc_produit_cepage] => Array()
            )
            ///
        */
        $produits = $this->queryAndRes2hashid('dr/cdc_produit');
        foreach($produits as $k => $v) {
            $v->dr_cdc_famille_id = $this->getCdcFamilleIdFromCdcId($v->dr_cdc_id);
        }
        return $produits;
    }

    public function getCdcFamilleIdFromCdcId($id) {
        if (!isset($this->cacheFamille)) {
            $this->cacheFamille = array();
        }
        if (isset($this->cacheFamille[$id])) {
            return $this->cacheFamille[$id];
        }
        $ops = CertipaqOperateur::getInstance()->recherche(array('dr_cdc' => array($id)));
        $op = CertipaqOperateur::getInstance()->find($ops[0]->id);
        foreach($op->sites as $site) {
            foreach($site->habilitations as $h) {
                if ($h->dr_cdc_id == $id) {
                    $this->cacheFamille[$id] = $h->dr_cdc_famille_id;
                    return $h->dr_cdc_famille_id;
                }
            }
        }
        return null;
    }

    public function getListeTypesAdresses() {
        /*
          [1] => ([id] => 1 , [libelle] => Courrier),
          [2] => ([id] => 2 , [libelle] => Facturation),
          [3] => ([id] => 3 , [libelle] => Prélèvement),
          [4] => ([id] => 4 , [libelle] => Siège social),
        */
        return $this->queryAndRes2hashid('dr/adresse_type');
    }

    public function keyid2obj($k, $id, $obj = null) {
        $hash = array();
        if (!$id) {
            return null;
        }
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
                $o = CertipaqOperateur::getInstance()->find($id, true);
                $hash[$id] = $o;
                break;
            case 'operateurs_sites_id':
            case 'entrepot_operateurs_sites_id':
                if ($obj) {
                    $hash[$id] = CertipaqOperateur::getInstance()->getSiteFromIdAndOperateur($id, $obj);
                }
                break;
            case 'dr_adresse_type_id':
                $hash = $this->getListeTypesAdresses();
                break;
        }
        if (isset($hash[$id])) {
            return $hash[$id];
        }
        return null;
    }

    public function getParamWithObjFromIds($param) {
        if (!$param || (!is_array($param) && !($param instanceof stdClass))) {
            return $param;
        }
        $newparam = $param;
        foreach ($param as $k => $v) {
            if (strpos($k, '_id') !== false) {
                $name = str_replace('_id', '', $k);
                $o = $this->keyid2obj($k, $v);
                if ($o) {
                    $newparam[$name] = $o;
                }
                continue;
            }
            $newparam[$k] = $this->getParamWithObjFromIds($param[$k]);
        }
        return $newparam;
    }

    public function getCertipaqProduitFromConfigurationProduit($conf) {
        $produits = $this->getListeProduitsCahiersDesCharges();
        $certipaq_produits = array();
        foreach($produits as $p) {
            if ($p->libelle == $conf->getLibelleComplet()) {
                return $p;
            }
            if (strpos($p->libelle, $conf->getLibelleComplet()) === 0) {
                $certipaq_produits[] = $p;
            }
        }
        foreach($produits as $p) {
            $c = $this->getConfigurationProduitFromProduitId($p->id);
            if ($c->getLibelleComplet() == $conf->getLibelleComplet()) {
                return $p;
            }
            if (strpos($c->getLibelleComplet(), $conf->getLibelleComplet()) === 0) {
                $certipaq_produits[] = $p;
            }
        }
        return array_pop($certipaq_produits);
    }

    public function getConfigurationProduitFromProduitId($pid) {
        $produits = $this->getListeProduitsCahiersDesCharges();
        if (!isset($produits[$pid]) || !$produits[$pid]) {
            return null;
        }
        return ConfigurationClient::getCurrent()->identifyProductByLibelle($produits[$pid]->libelle);
    }
}
