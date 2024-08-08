<?php
class DouaneClient extends acCouchdbClient
{

    public static function getInstance()
    {
      return acCouchdbManager::getClient('DR');
    }

    public function getDocumentsDouaniers($etablissement, $periode, $ext =  null, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $etablissements = $etablissement->getMeAndLiaisonOfType(EtablissementClient::TYPE_LIAISON_METAYER);
        $fichiers = array();
        foreach($etablissements as $e) {
            $f = new DRev();
            $f = $f->getDocumentDouanierEtablissement($ext, $periode, $e, $hydrate);
            if ($f) {
                $fichiers[] = $f;
            }
        }
        return $fichiers;
    }
}
