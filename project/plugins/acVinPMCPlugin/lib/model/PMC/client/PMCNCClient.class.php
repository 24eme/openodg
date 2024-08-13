<?php

class PMCNCClient extends PMCClient
{
    const TYPE_MODEL = "PMCNC";
    const TYPE_COUCHDB = "PMCNC";

    const SUFFIX = " suite à non conformité";

    public static function getInstance()
    {
        return acCouchdbManager::getClient("PMCNC");
    }

    public function createDoc($identifiant, $periode, $date, $papier = false)
    {
        $doc = new PMCNC();
        $doc->initDoc($identifiant, $periode, $date);

        $doc->storeDeclarant();

        $etablissement = $doc->getEtablissementObject();

        if($papier) {
            $doc->add('papier', 1);
        }

        return $doc;
    }

    public function createPMCNC($lot, $campagne, $papier = false) {
        $pmcNc = PMCNCClient::getInstance()->createDoc($lot->declarant_identifiant, $campagne, date('Y-m-d H:i:s'), $papier);
        $lotDef = PMCLot::freeInstance(new PMCNC());

        foreach($lot->getFields() as $key => $value) {
            if ($lotDef->getDefinition()->exist($key)) {
                continue;
            }

            $lot->remove($key);
        }

        $pmcNc->chais->nom = $lot->getLogementNom();
        $pmcNc->chais->adresse = $lot->getLogementAdresse();
        $pmcNc->chais->code_postal = $lot->getLogementCodePostal();
        $pmcNc->chais->commune = $lot->getLogementCommune();
        if($lot->exist('secteur')) {
            $pmcNc->chais->secteur = $lot->secteur;
        }

        $lot = $pmcNc->lots->add(null, $lot);
        $lot->id_document = $pmcNc->_id;
        $lot->updateDocumentDependances();

        return $pmcNc;
    }

    public function findPMCsByCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
      $allPMC = PMCCNClient::getInstance()->getHistory($identifiant);
      $pmcs = array();
      foreach ($allPMC as $key => $pmc) {
        if($pmc->campagne == $campagne){
          $pmcs[] = $pmc;
        }
      }
      return $pmcs;
    }
}
