<?php

class ChgtDenomClient extends acCouchdbClient implements FacturableClient {

    const TYPE_MODEL = "ChgtDenom";
    const TYPE_COUCHDB = "CHGTDENOM";
    const ORIGINE_LOT = "DREV";

    const FORMAT_DATE = 'Y-m-d\THis';


    public static function getInstance() {
        return acCouchdbManager::getClient("ChgtDenom");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);
        if($doc && $doc->type != self::TYPE_MODEL) {
            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }
        return $doc;
    }

    public function getHistory($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $campagne_from = "0000-00-00T000000";
        $campagne_to = "9999-99-99T999999";
        return $this->startkey(sprintf("CHGTDENOM-%s-%s", $identifiant, $campagne_from))
                    ->endkey(sprintf("CHGTDENOM-%s-%s", $identifiant, $campagne_to))
                    ->execute($hydrate);
    }

    public function getLast($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
        return $this->findPreviousByIdentifiantAndDate($identifiant, "9999-99-99T999999");
    }

    public function findPreviousByIdentifiantAndDate($identifiant, $date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $h = $this->getHistory($identifiant, $date, $hydrate);
        if (!count($h)) {
        return NULL;
        }
        $h = $h->getDocs();
        end($h);
        $doc = $h[key($h)];
        return $doc;
    }

    public function createDoc($identifiant, $date = null, $papier = false) {
        $chgtdenom = new ChgtDenom();
        if ($date && preg_match('/^\d{4}$/', $date)) {
          $date = str_replace(date('Y').'-', $date.'-', date(self::FORMAT_DATE));
        } else {
          $date = ($date && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $date))? $date : date(self::FORMAT_DATE);
        }
        $chgtdenom->initDoc($identifiant, $date);
        if($papier) {
            $chgtdenom->add('papier', 1);
        }
        $chgtdenom->storeDeclarant();
        return $chgtdenom;
    }

    public function findFacturable($identifiant, $campagne) {
      $chgtsdenom = $this->getHistory($identifiant);
      $chgtsdenomFacturants = array();
      foreach ($chgtsdenom as $chgtdenom) {
        if($chgtdenom && !$chgtdenom->validation_odg) {
          continue;
        }
        $chgtsdenomFacturants[] = $chgtdenom;
      }

      return $chgtsdenomFacturants;
    }

}
