<?php

class CertipaqDeroulant extends CertipaqService
{
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

    public function getListeProduitsCahiersDesCharges() {
        return $this->queryAndRes2hashid('dr/cdc_produit');
    }

    public function keyid2obj($k, $id) {
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
            case 'dr_infos_id':
                $hash = $this->getListeDRInfo();
                break;
            case 'dr_cdc_produit_id':
                $hash = $this->getListeProduitsCahiersDesCharges();
                break;
        }
        if (isset($hash[$id])) {
            return $hash[$id];
        }
        return null;
    }
}
