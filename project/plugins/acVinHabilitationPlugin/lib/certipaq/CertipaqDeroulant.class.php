<?php

class CertipaqDeroulant extends CertipaqService
{
    const ENDPOINT_ACTIVITE_OPERATEUR = 'dr/activites_operateurs';
    const ENDPOINT_TYPE_CONTROLE = 'dr/type_controle';
    const ENDPOINT_HABILITATION = 'dr/statut_habilitation';
    const ENDPOINT_CDC = 'dr/cdc';

    private function query($endpoint)
    {
        $result = $this->httpQuery(
            $this->configuration['apiurl'].$endpoint,
            [
                'http' => $this->getQueryHttpRequest($this->getToken())
            ]
        );

        $result = json_decode($result);

        return $result->results;
    }

    public function getListeActivitesOperateurs()
    {
        return $this->query(self::ENDPOINT_ACTIVITE_OPERATEUR);
    }

    public function getListeTypeControle()
    {
        return $this->query(self::ENDPOINT_TYPE_CONTROLE);
    }

    public function getListeStatutHabilitation()
    {
        return $this->query(self::ENDPOINT_HABILITATION);
    }

    public function getListeCahiersDesCharges()
    {
        return $this->query(self::ENDPOINT_CDC);
    }
}
