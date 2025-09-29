<?php
class Controle extends BaseControle
{
    protected function initDocuments()
    {
        return;
    }

    public function initDoc($identifiant, $date, $type = ControleClient::TYPE_COUCHDB)
    {
        $this->identifiant = $identifiant;
        $this->date = $date;
        $this->campagne = ConfigurationClient::getInstance()->buildCampagne($date);
        $this->set('_id', ControleClient::TYPE_COUCHDB."-".$identifiant."-".str_replace('-', '', $date));
        $this->storeDeclarant();
    }
}
