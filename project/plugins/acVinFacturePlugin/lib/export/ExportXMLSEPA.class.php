<?php

class ExportXMLSEPA {

  private $factures;
  private $xml;

  public function __construct(array $factures = array(), $auto_save_facture = false, $not_execute_only = true) {
    $this->factures = $factures;
    $this->auto_save_facture = $auto_save_facture;
    $this->not_execute_only = $not_execute_only;
  }

  public function addFacture(Facture $facture) {
    $this->factures[] = $facture;
  }

  public function saveExportedSepa(){
      if(!$this->auto_save_facture){
        return;
      }
      foreach($this->factures as $facture){
          $facture->versement_sepa = 1; //il n'a plus de paiement à mettre dans le xml
          foreach($facture->paiements as $paiement){
            $paiement->execute = true;  //ils ont tous été executés.
            $paiement->commentaire = "prélèvement ajouté au xml";
          }
          $facture->save();
      }
  }

  protected function generateHeader() {

    $document = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?><Document/>");


    $document->addAttribute("xmlns","urn:iso:std:iso:20022:tech:xsd:pain.008.001.02");
    $document->addAttribute("xmlns:xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
    $document->addAttribute("xsi:xsi:schemaLocation","urn:iso:std:iso:20022:tech:xsd:pain.008.001.02 pain.008.001.02.xsd");

    $cstmrDrctDbtInitn = $document->addChild('CstmrDrctDbtInitn');

    $grpHdr = $cstmrDrctDbtInitn->addChild('GrpHdr');
    $grpHdr->addChild('MsgId',date('Y-m-d-h-i-00'));

    $date = new DateTime();
    $date = $date->format('Y-m-d\TH:i:00\Z');
    $grpHdr->addChild("CreDtTm",$date);

    $grpHdr->addChild("NbOfTxs",0);
    $grpHdr->addChild("CtrlSum", 0);

    $initgPty = $grpHdr->addChild("InitgPty");

    $initgPty->addChild('Nm', substr(iconv('utf-8', 'us-ascii//TRANSLIT', Organisme::getInstance()->getNom()), 0, 70));

    $idHdr = $initgPty->addChild("Id");
    $orgId = $idHdr->addChild('OrgId');
    $othrHdr = $orgId->addChild("Othr");
    $othrHdr->addChild("Id",Organisme::getInstance()->getIban());
    return $document;
  }

  public function getXml() {

    $sommeMontant = 0;
    $nombreDePrelevement = 0;
    $this->xml = $this->generateHeader();

    $tabPmtInf = [];

    foreach($this->factures as $facture){
      foreach($facture->paiements as $paiement){  //parcourir toutes les paiments de chaque factures et mets les factures qui ont un paiement à cette date.
          $tabPmtInf[$paiement->date][$facture->_id] = $facture;
      }
    }

    $this->generatePmtInf($tabPmtInf);

    $nbPrelevement = 0;
    $sommeMontant = 0;
    foreach($this->xml->CstmrDrctDbtInitn->children() as $paiement){
      if ($paiement->getName() != 'PmtInf') {
          continue;
      }
      $nbPrelevement += $paiement->NbOfTxs;
      $sommeMontant += $paiement->CtrlSum;
    }

    $this->xml->CstmrDrctDbtInitn->GrpHdr->NbOfTxs = $nbPrelevement;
    $this->xml->CstmrDrctDbtInitn->GrpHdr->CtrlSum = $sommeMontant;
    return $this->xml->asXML();
  }



  protected function generatePmtInf($tabPmtInf){

    foreach($tabPmtInf as $d => $factures){
      $nbOfTxs = 0;
      $sommetot = 0;

      $pmtInf = $this->xml->CstmrDrctDbtInitn->addChild('PmtInf');
      $pmtInf->addChild('PmtInfId', 'PAIEMENT-'.$d);
      $pmtInf->addChild('PmtMtd', "DD");

      $pmtInf->addChild('NbOfTxs', $nbOfTxs);
      $pmtInf->addChild('CtrlSum', $sommetot);

      $pmtTpInf = $pmtInf->addChild('PmtTpInf');
      $svcLvl = $pmtTpInf->addChild('SvcLvl');
      $svcLvl->addChild('Cd','SEPA');
      $lclInstrm = $pmtTpInf->addChild('LclInstrm');
      $lclInstrm->addChild('Cd','CORE');
      $pmtTpInf->addChild('SeqTp','RCUR');

      $pmtInf->addChild('ReqdColltnDt', $d); //Date d'échéance du recouvrement,

      $cdtr = $pmtInf->addChild('Cdtr');
      $cdtr->addChild('Nm', substr(iconv('utf-8', 'us-ascii//TRANSLIT', Organisme::getInstance()->getNom()), 0, 70)); //nom de l'odg

      $cdtrAcct = $pmtInf->addChild('CdtrAcct');
      $id = $cdtrAcct->addChild('Id');
      $id->addChild('IBAN',Organisme::getInstance()->getIban());

      $cdtrAgt = $pmtInf->addChild('CdtrAgt');
      $finInstnID = $cdtrAgt->addChild('FinInstnId');
      $finInstnID->addChild('BIC',Organisme::getInstance()->getBic());  //BIC odg

      $pmtInf->addChild('ChrgBr','SLEV');

      $cdtrschemeid = $pmtInf->addChild('CdtrSchmeId');
      $idcdtrschemeid = $cdtrschemeid->addChild('Id');
      $prvtid = $idcdtrschemeid->addChild('PrvtId');
      $othr = $prvtid->addChild("Othr");
      $othr->addChild("Id",Organisme::getInstance()->getCreditorId());  //creditorId
      $schmeNm = $othr->addChild("SchmeNm");
      $schmeNm->addChild("Prtry","SEPA");

      foreach($factures as $facture){
        $this->generateOnePaiement($facture,$d,$pmtInf);
      }

      $nbOfTxs = count($pmtInf->DrctDbtTxInf);

      foreach($pmtInf->DrctDbtTxInf as $paiement){
        $sommetot += $paiement->InstdAmt;
      }

      $pmtInf->NbOfTxs = $nbOfTxs;
      $pmtInf->CtrlSum = $sommetot;
    }
  }


  protected function generateOnePaiement($facture,$d,$pmtInf){
    $mandatSepa = MandatSepaClient::getInstance()->findLastBySociete($facture->getIdentifiant());
    foreach($facture->paiements as $paiement){
      if($paiement->date == $d && ($paiement->type_reglement == FactureClient::FACTURE_PAIEMENT_PRELEVEMENT_AUTO) && ($paiement->execute == false || !$this->not_execute_only) ){
        $drctdbttxinf = $pmtInf->addChild("DrctDbtTxInf");
        $pmtid = $drctdbttxinf->addChild("PmtId");
        $pmtid->addChild("EndToEndId", "Facture ".$facture->numero_odg); //intitule pour l'ODG
        $montant = $drctdbttxinf->addChild("InstdAmt",$paiement->montant);  //montant
        $montant->addAttribute('Ccy', "EUR");
        $drctdbttx = $drctdbttxinf->addChild("DrctDbtTx");
        $mndtRltdInf = $drctdbttx->addChild("MndtRltdInf");
        $mndtRltdInf->addChild("MndtId", $mandatSepa->getNumeroRum());  //
        $mndtRltdInf->addChild("DtOfSgntr",$mandatSepa->date); //date de signature du sepa
        $dbtrAgt = $drctdbttxinf->addChild("DbtrAgt");
        $finInstnId = $dbtrAgt->addChild("FinInstnId");
        $finInstnId->addChild('BIC',$mandatSepa->getBic()); //son bic
        $dbtr = $drctdbttxinf->addChild("Dbtr");
        $dbtr->addChild("Nm", substr(iconv('utf-8', 'us-ascii//TRANSLIT', $facture->declarant->raison_sociale), 0, 70)); // sa raison Social  //ou $facture->getSociete()->getRaisonSociale()
        $dbtracct = $drctdbttxinf->addChild("DbtrAcct");
        $idDbtracct = $dbtracct->addChild('Id');
        $idDbtracct->addChild('IBAN',$mandatSepa->getIban()); //son iban
        $rmtinf = $drctdbttxinf->addChild("RmtInf");
        $rmtinf->addChild("Ustrd","Facture ".$facture->numero_odg); //libelle bancaire pour lui
      }
    }
  }

  public function getFacturesId(){
      $ids = [];
      foreach($this->factures as $facture){
        $ids[] = $facture->_id;
      }
      return $ids;
  }

  public static function getExportXMLSepaForCurrentPrelevements($auto_save = false) {
      $factures = array();
      foreach (FactureEtablissementView::getInstance()->getPaiementNonExecuteSepa() as $vf) {
          $factures[] = FactureClient::getInstance()->find($vf->key[FactureEtablissementView::KEYS_FACTURE_ID]);
      }
      $sepa = new ExportXMLSEPA($factures, $auto_save);
      return $sepa;
  }

  public static function getExportXMLSepaFromFactureIds($ids, $auto_save = false) {
      $factures = array();
      foreach($ids as $id) {
          $factures[] = FactureClient::getInstance()->find($id);
      }
      $sepa = new ExportXMLSEPA($factures, $auto_save, false);
      return $sepa;
  }

}
