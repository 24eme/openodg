<?php
class DouaneClient extends acCouchdbClient
{

    public static function getInstance()
    {
      return acCouchdbManager::getClient('DR');
    }

    public function getDocumentsDouaniers($identifiant, $periode, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $etablissements = EtablissementClient::getInstance()->findByIdentifiant($identifiant)->getMeAndLiaisonOfType(EtablissementClient::TYPE_LIAISON_METAYER);
        $fichiers = array();
        foreach($etablissements as $e) {
            $f = new DRev();
            $f = $f->getDocumentDouanierEtablissement(null, $periode, $e->identifiant, $hydrate);
            if ($f) {
                $fichiers[] = $f;
            }
        }
        return $fichiers;
    }
}
