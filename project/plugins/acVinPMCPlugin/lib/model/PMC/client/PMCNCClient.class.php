<?php

class PMCNCClient extends PMCClient
{
    const TYPE_MODEL = "PMCNC";
    const TYPE_COUCHDB = "PMCNC";

    public function create($lot, $papier = false) {
        $pmcNc = PMCClient::getInstance()->createDoc($lot->declarant_identifiant, $lot->campagne, date('YmdHis'), $papier);
        $lotDef = PMCLot::freeInstance(new PMC());

        foreach($lot->getFields() as $key => $value) {
            if ($lotDef->getDefinition()->exist($key)) {
                continue;
            }

            $lot->remove($key);
        }

        $lot = $pmcNc->lots->add(null, $lot);
        $lot->id_document = $pmcNc->_id;
        $lot->updateDocumentDependances();

        return $pmcNc;
    }

    public function findPMCsByCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
      $allPMC = PMCClient::getInstance()->getHistory($identifiant);
      $pmcs = array();
      foreach ($allPMC as $key => $pmc) {
        if($pmc->campagne == $campagne){
          $pmcs[] = $pmc;
        }
      }
      return $pmcs;
    }
}
