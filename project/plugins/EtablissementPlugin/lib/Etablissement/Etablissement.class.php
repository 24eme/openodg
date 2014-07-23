<?php
/**
 * Model for Etablissement
 *
 */

class Etablissement extends BaseEtablissement {

    public function constructId() 
    {
        $this->_id = sprintf("%s-%s", EtablissementClient::TYPE_COUCHDB, $this->identifiant);
    }

}