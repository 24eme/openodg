<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if (getenv("NODELETE")) {
    $t = new lime_test(0);
    exit(0);
}
$nbtest = 36;
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
          }
        }
        $soc->delete();
        $t->is(CompteClient::getInstance()->findByIdentifiant($m[1].'01'), null, "Suppression de la sociétés ".$m[1]." provoque la suppression de son compte");
    }
}
