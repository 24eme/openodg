<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if (getenv("NODELETE")) {
    $t = new lime_test(0);
    exit(0);
}
$nbtest = 19;
$t = new lime_test($nbtest);

$t->comment('suppression des différentes sociétés, de leurs établissements et comptes');

$clientcond = false;
foreach (CompteTagsView::getInstance()->listByTags('test', 'test') as $k => $v) {
    if (preg_match('/SOCIETE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
        $soc = SocieteClient::getInstance()->findByIdentifiantSociete($m[1]);
        foreach($soc->getEtablissementsObj() as $k => $etabl) {
            if($etabl->etablissement){
              foreach (HabilitationClient::getInstance()->getHistory($etabl->etablissement->identifiant) as $id => $h) {
                  $h = HabilitationClient::getInstance()->find($id);
                  $h->delete(false);
                  $t->is(HabilitationClient::getInstance()->find($id), null, "Suppression de l'habilitation ".$id);
              }
              foreach(DRevClient::getInstance()->getHistory($etabl->etablissement->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $id => $d) {
                  $d = DRevClient::getInstance()->find($id);
                  $d->delete(false);
                  $t->is(DRevClient::getInstance()->find($id), null, "Suppression de la drev ".$id);
                  $dr = DRClient::getInstance()->find(str_replace("DREV-", "DR-", $id), acCouchdbClient::HYDRATE_JSON);
                  if($dr) { DRClient::getInstance()->deleteDoc($dr); }
                  $sv12 = SV12Client::getInstance()->find(str_replace("DREV-", "SV12-", $id), acCouchdbClient::HYDRATE_JSON);
                  if($sv12) { SV12Client::getInstance()->deleteDoc($sv12); }
                  $sv11 = SV11Client::getInstance()->find(str_replace("DREV-", "SV11-", $id), acCouchdbClient::HYDRATE_JSON);
                  if($sv11) { SV11Client::getInstance()->deleteDoc($sv11); }
              }
              foreach(acCouchdbManager::getClient()
                          ->reduce(false)
                          ->getView('declaration', 'tous')->rows as $row) {
                  if (preg_match('/-'.$etabl->etablissement->identifiant.'-/', $row->id )) {
                      $doc = acCouchdbManager::getClient()->find($row->id);
                      $doc->delete();
                      $t->is(acCouchdbManager::getClient()->find($row->id), null, "Suppression de ".$row->id);
                  }
              }
              foreach(PieceAllView::getInstance()->getAll() as $row) {
                  if (preg_match('/-'.$etabl->etablissement->identifiant.'-/', $row->id )) {
                      $doc = acCouchdbManager::getClient()->find($row->id);
                      $doc->delete();
                      $t->is(acCouchdbManager::getClient()->find($row->id), null, "Suppression de ".$row->id);
                  }
              }
          }
        }
        $soc->delete();
        $t->is(CompteClient::getInstance()->findByIdentifiant($m[1].'01'), null, "Suppression de la sociétés ".$m[1]." provoque la suppression de son compte");
    }
    if (preg_match('/ETABLISSEMENT-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
        $etab = EtablissementClient::getInstance()->findByIdentifiant($m[1]);
        if ($etab) {
            foreach(acCouchdbManager::getClient()
                        ->reduce(false)
                        ->getView('declaration', 'tous')->rows as $row) {
                if ($row->id && preg_match('/-'.$etab->identifiant.'-/', $row->id )) {
                    $doc = acCouchdbManager::getClient()->find($row->id);
                    $doc->delete();
                }
            }
            $etab->delete();
        }
    }
    $c = CompteClient::getInstance()->find($k);
    if ($c) {
        $c->delete();
    }
}
