<?php

class CertipaqOperateur extends CertipaqService
{
    /**
     * @param Array $params Il faut renseigner au moins : raison_sociale et
     * code_postal, ou siret ou cvi ou dr_cdc_famille ou dr_cdc ou
     * dr_activites_operateurs ou date_decision ou dr_statut_habilitation.
     *
     */
    public function recherche($params = [])
    {
        if (empty($params)) {
            throw new Exception("CertipaqOperateur Error : this function needs params");
        }

        if ((isset($params['raison_sociale']) && empty($params['code_postal']))
            || isset($params['code_postal']) && empty($params['raison_sociale']))
        {
            throw new Exception("CertipaqOperateur Error : la raison sociale et le cp doivent être renseignés ensemble");
        }
        return $this->query('operateur', 'GET', $params);
    }

    public function find($id, $withCache = false) {
        return $this->recuperation($id, $withCache);
     }

    public function recuperation($operateur_certipaq_id, $withCache = false)
    {
        $endpoint = str_replace('{id_operateur}', $operateur_certipaq_id, 'operateur/{id_operateur}');
        if ($withCache) {
            $res = $this->queryWithCache($endpoint);
        }else{
            $res = $this->query($endpoint);
        }
        foreach ($res->sites as $site_id => $value) {
            $outils_production = array();
            foreach($value->outils_production as $obj) {
                $outils_production[$obj->id] = $obj;
            }
            foreach($value->habilitations as $hab_id => $hab) {
                if ($hab->outil_production_id) {
                    $hab->outil_production = $outils_production[$hab->outil_production_id];
                }
                if ($hab->dr_statut_habilitation_id) {
                    $hab->dr_statut_habilitation = CertipaqDeroulant::getInstance()->keyid2obj('dr_statut_habilitation_id', $hab->dr_statut_habilitation_id);
                }
                if ($hab->dr_activites_operateurs_id) {
                    $hab->dr_activites_operateurs = CertipaqDeroulant::getInstance()->keyid2obj('dr_activites_operateurs_id', $hab->dr_activites_operateurs_id);
                    foreach ($hab->dr_activites_operateurs->dr_activites_operateurs_infos as $daoid => $opinfo) {
                        $opinfo->dr_infos = CertipaqDeroulant::getInstance()->keyid2obj('dr_infos_id', $opinfo->dr_infos_id);
                    }
                }
                if ($hab->dr_cdc_famille_id) {
                    $hab->dr_cdc_famille = CertipaqDeroulant::getInstance()->keyid2obj('dr_cdc_famille_id', $hab->dr_cdc_famille_id);
                }
                if ($hab->dr_cdc_id) {
                    $hab->dr_cdc = CertipaqDeroulant::getInstance()->keyid2obj('dr_cdc_id', $hab->dr_cdc_id);
                }
            }
        }
        return $res;
    }

    public function getHabilitationFromOperateurProduitAndActivite($certipaq_operateur, $certipaq_produit, $activite) {
        $habilitation = null;
        foreach($certipaq_operateur->sites as $s) {
            foreach($s->habilitations as $h) {
                if ($h->dr_cdc_id = $certipaq_produit->dr_cdc_id && $h->dr_activites_operateurs->libelle == $activite) {
                    $habilitation = $h;
                }
            }
        }
        if ($habilitation) {
            $habilitation->dr_cdc_produit_id = $certipaq_produit->id;
            $habilitation->dr_cdc_produit = $certipaq_produit;
        }
        return $habilitation;
    }

    public function getSiteFromIdAndOperateur($site_id, $certipaq_operateur) {
        foreach($certipaq_operateur->sites as $s) {
            if($s->id == $site_id) {
                $res = clone $s;
                unset($res->habilitations);
                return $res;
            }
        }
        return null;
    }

    public function getAll() {
        $cdcs = CertipaqDeroulant::getInstance()->getListeCahiersDesCharges();
        $ids = array();
        foreach ($cdcs as $c) {
            $ids[] = $c->id;
        }
        return $this->recherche(array('dr_cdc' => $ids));
    }

    public function findByCviOrSiret($siret_ou_cvi) {
        $siret_ou_cvi = str_replace(' ', '', $siret_ou_cvi);
        $res = $this->recherche(array('cvi' => $siret_ou_cvi));
        if (!$res || !count($res)) {
            $res = $this->recherche(array('siret' => $siret_ou_cvi));
        }
        if (!$res || !isset($res[0]) || count($res) > 1) {
            return null;
        }
        return $res[0];
    }

    public function findByEtablissement($etablissement) {
        $op = null;
        if ($etablissement->cvi) {
            $op = $this->findByCviOrSiret($etablissement->cvi);
        }
        if (!$op && $etablissement->siret) {
            $op = $this->findByCviOrSiret($etablissement->siret);
        }
        if ($op) {
            $op = $this->recuperation($op->id);
        }
        return $op;
    }
}
