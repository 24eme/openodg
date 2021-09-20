<?php

class CertipaqDeroulant extends CertipaqService
{
    const ENDPOINT_ACTIVITE_OPERATEUR = 'dr/activites_operateurs';
    const ENDPOINT_TYPE_CONTROLE = 'dr/type_controle';
    const ENDPOINT_HABILITATION = 'dr/statut_habilitation';
    const ENDPOINT_CDC = 'dr/cdc';
    const ENDPOINT_CDC_FAMILLE = 'dr/cdc_famille';
    const ENDPOINT_DR_INFO = 'dr/infos';

    private function res2hashid($res) {
        $objs = array();
        foreach($res as $o) {
            $objs[$o->id] = $o;
        }
        return $objs;
    }

    public function getListeActivitesOperateurs()
    {
        $res = $this->queryWithCache(self::ENDPOINT_ACTIVITE_OPERATEUR);
        return $this->res2hashid($res);
    }

    public function getListeTypeControle()
    {
        $res = $this->queryWithCache(self::ENDPOINT_TYPE_CONTROLE);
        return $this->res2hashid($res);
    }

    public function getListeHabilitation()
    {
        $res = $this->queryWithCache(self::ENDPOINT_HABILITATION);
        return $this->res2hashid($res);
    }

    public function getListeCahiersDesCharges()
    {
        $res = $this->queryWithCache(self::ENDPOINT_CDC);
        return $this->res2hashid($res);
    }

    public function getListeFamilleCahiersDesCharges()
    {
        $res = $this->queryWithCache(self::ENDPOINT_CDC_FAMILLE);
        return $this->res2hashid($res);
    }

    public function getListeDRInfo()
    {
        $res = $this->queryWithCache(self::ENDPOINT_DR_INFO);
        return $this->res2hashid($res);
    }

    public function keyid2obj($k, $id) {
        if ($k == 'dr_statut_habilitation_id') {
            $h = $this->getListeHabilitation();
            return $h[$id];
        }
        if ($k == 'dr_cdc_id') {
            $c = $this->getListeCahiersDesCharges();
            return $c[$id];
        }
        if ($k == 'dr_activites_operateurs_id') {
            $a = $this->getListeActivitesOperateurs();
            return $a[$id];
        }
        if ($k == 'dr_cdc_famille_id') {
            $f = $this->getListeFamilleCahiersDesCharges();
            return $f[$id];
        }
        if ($k == 'dr_infos_id') {
            $i = $this->getListeDRInfo();
            return $i[$id];
        }
    }
}
