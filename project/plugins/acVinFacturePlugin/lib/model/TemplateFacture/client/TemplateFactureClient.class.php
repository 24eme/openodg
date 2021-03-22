<?php

class TemplateFactureClient extends acCouchdbClient {

    const TYPE_MODEL = "TemplateFacture";
    const TYPE_COUCHDB = "TEMPLATE";

    public static function getInstance()
    {

        return acCouchdbManager::getClient("TemplateFacture");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function findByCampagne($campagne){
      //TODO: Transformer les templates des autres instance en TEMPLATE-FACTURE-CAMPAGNE
      $campagne = substr($campagne,0,4);
      if($templateFactureAoc = $this->find("TEMPLATE-FACTURE-AOC-".$campagne)){
        return $templateFactureAoc;
      }

      return $this->find("TEMPLATE-FACTURE-".$campagne);
    }


    public function findAll() {
        return $this->startkey_docid(sprintf("TEMPLATE-FACTURE-%s", ""))
        ->endkey_docid(sprintf("TEMPLATE-FACTURE-%s", "Z"))
                    ->execute();
    }

}
