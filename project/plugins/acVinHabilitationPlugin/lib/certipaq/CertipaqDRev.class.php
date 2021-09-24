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
}
