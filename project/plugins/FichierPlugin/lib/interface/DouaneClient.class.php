<?php
class DouaneClient extends acCouchdbClient
{
    static public function getDocumentsDouaniers($identifiant = null, $periode = null, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        if (!$identifiant) {
            $etablissements = getEtablissementObject()->getMeAndLiaisonOfType(EtablissementClient::TYPE_LIAISON_METAYER);
        }
        else {
            $etablissements = EtablissementClient::getInstance()->findByIdentifiant($identifiant)->getMeAndLiaisonOfType(EtablissementClient::TYPE_LIAISON_METAYER);
        }
        $fichiers = array();
        foreach($etablissements as $e) {
            if ($e->identifiant == $identifiant) {
                continue;
            }
            $f = new DRev();
            $f = $f->getDocumentDouanierEtablissement(null, $periode, $e->identifiant, $hydrate);
            if ($f) {
                $fichiers[] = $f;
            }
        }
        return $fichiers;
    }
}
