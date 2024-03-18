<?php
class DouaneClient extends acCouchdbClient
{
    public function getDocumentsDouaniers($identifiant, $periode) {
        $etablissements = EtablissementClient::getInstance()->findByIdentifiant($identifiant)->getMeAndLiaisonOfType(EtablissementClient::TYPE_LIAISON_METAYER);
        $fichiers = array();
        foreach($etablissements as $e) {
            if ($e->identifiant == $identifiant) {
                continue;
            }
            $f = new DRev();
            $f = $f->getDocumentDouanierEtablissement(null, $periode, $e->identifiant);
            if ($f) {
                $fichiers[] = $f;
            }
        }
        return $fichiers;
    }
}
