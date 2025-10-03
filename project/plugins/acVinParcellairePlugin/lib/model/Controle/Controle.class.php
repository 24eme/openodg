<?php
class Controle extends BaseControle
{
    protected $parcellaire = null;

    protected function initDocuments()
    {
        $this->declarant_document = new DeclarantDocument($this);
    }

    public function initDoc($identifiant, $date, $type = ControleClient::TYPE_COUCHDB)
    {
        $this->identifiant = $identifiant;
        $this->date = $date;
        $this->campagne = ConfigurationClient::getInstance()->buildCampagne($date);
        $this->set('_id', ControleClient::TYPE_COUCHDB."-".$identifiant."-".str_replace('-', '', $date));
        $this->storeDeclarant();
    }

    protected function doSave()
    {
        return;
    }

    public function getParcellaire()
    {
        if (!$this->parcellaire) {
            $this->parcellaire = ParcellaireClient::getInstance()->getLast($this->identifiant, acCouchdbClient::HYDRATE_JSON);
        }
        return $this->parcellaire;
    }

    public function updateParcelles(array $parcellesIds)
    {
        $this->remove('parcelles');
        $this->add('parcelles');
        if ($parcellesIds) {
            $parcelles = $this->getParcellaire()->getParcelles();
            foreach ($parcellesIds as $pId) {
                if ($parcelles->exist($pId)) {
                    $this->parcelles->add($pId, $parcelles->get($pId));
                }
            }
        }
    }

    public function hasParcelle($parcelleId)
    {
        return $this->parcelles->exist($parcelleId);
    }
}
