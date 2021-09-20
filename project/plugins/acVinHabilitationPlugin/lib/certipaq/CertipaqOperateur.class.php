<?php

class CertipaqOperateur extends CertipaqService
{
    const ENDPOINT_RECHERCHE = 'operateur';
    const ENDPOINT_RECUPERATION = 'operateur/{id_operateur}';

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

        return $this->query(self::ENDPOINT_RECHERCHE, 'GET', $params);
    }

    public function recuperation($id)
    {
        $endpoint = str_replace('{id_operateur}', $id, self::ENDPOINT_RECUPERATION);
        return $this->query($endpoint);
    }
}
