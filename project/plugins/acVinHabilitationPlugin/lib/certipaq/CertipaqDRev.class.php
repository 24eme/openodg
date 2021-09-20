<?php

class CertipaqDRev extends CertipaqService
{
    const ENDPOINT_REVENDICATION = 'declaration/revendication';
    const ENDPOINT_RECUPERATION = 'declaration/revendication/{id_declaration}';

    public function list($params = [])
    {
        return $this->query(self::ENDPOINT_REVENDICATION, 'GET', $params);
    }

    public function create($params = [])
    {
        return $this->query(self::ENDPOINT_REVENDICATION, 'POST', $params);
    }

    public function find($id)
    {
        $endpoint = str_replace('{id_declaration}', $id, self::ENDPOINT_RECUPERATION);
        return $this->query($endpoint);
    }
}
