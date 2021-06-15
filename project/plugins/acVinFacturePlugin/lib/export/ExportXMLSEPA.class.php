<?php

class ExportXMLSEPA {

  private array $factures;
  private $xml;

  public function __construct(array $factures = array(), $auto_save_facture = false) {
    $this->factures = $factures;
    $this->auto_save_facture = $auto_save_facture;
  }

  public function addFacture(Facture $facture) {
    $this->factures[] = $facture;
  }

  public function saveExportedSepa(){
      if(!$this->auto_save_facture){
        return;
      }
      foreach($this->factures as $f){
          $facture = FactureClient::getInstance()->find($f->key[FactureEtablissementView::KEYS_FACTURE_ID]);
          $facture->versement_sepa = 1; //il n'a plus de paiement à mettre dans le xml
          foreach($facture->paiements as $paiement){
            $paiement->execute = true;  //ils ont tous été executés.
            $paiement->commentaire = "prélèvement ajouté au xml";
          }
          $facture->save();
      }
  }

  public function getXml() {

    $sommeMontant = 0;
    $nombreDePrelevement = 0;
    $this->xml = $this->generateHeader();

    foreach($this->factures as $vfacture){
      $facture = FactureClient::getInstance()->find($vfacture->key[FactureEtablissementView::KEYS_FACTURE_ID]);
      $this->generateOneFacture($facture);
    }

    foreach($this->xml->PmtInf as $paiement){
      $sommeMontant += $paiement->DrctDbtTxInf->InstdAmt;
    }

    $this->xml->CstmrDrctDbtInitn->GrpHdr->NbOfTxs = count($this->xml->PmtInf);
    $this->xml->CstmrDrctDbtInitn->GrpHdr->CtrlSum = $sommeMontant;
    return $this->xml->asXML();
  }

  protected function generateHeader() {
    $document = new SimpleXMLElement('<Document/>');

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

    $initgPty->addChild('Nm',Organisme::getInstance()->getNom());

    $idHdr = $initgPty->addChild("Id");
    $orgId = $idHdr->addChild('OrgId');
    $othrHdr = $orgId->addChild("Othr");
    $othrHdr->addChild("Id",Organisme::getInstance()->getIban());
    return $document;
  }

  protected function generateOneFacture($facture) {
    $mandatSepa = MandatSepaClient::getInstance()->findLastBySociete($facture->getIdentifiant());
    for($i=0; $i< $facture->getNbPaiementsAutomatique() ;$i++){ // un seul paiement par ligne de pmt

        if(!$facture->paiementIsExecute($i)){  //verifie que le "execute" du paiement est à false

          $pmtInf = $this->xml->addChild('PmtInf');
          $pmtInf->addChild('PmtInfId', $facture->_id.'-PAIEMENT-'.$i);
          $pmtInf->addChild('PmtMtd', "DD");
          $pmtInf->addChild('NbOfTxs', "1");
          $pmtInf->addChild('CtrlSum', "DD");

          $pmtTpInf = $pmtInf->addChild('PmtTpInf');
          $svcLvl = $pmtTpInf->addChild('SvcLvl');
          $svcLvl->addChild('Cd','SEPA');
          $lclInstrm = $pmtTpInf->addChild('LclInstrum');
          $lclInstrm->addChild('Cd','CORE');
          $pmtTpInf->addChild('SeqTp','RCUR');

          $pmtInf->addChild('ReqdColltnDt',$facture->date_echeance); //Date d'échéance du recouvrement,

          $cdtr = $pmtInf->addChild('Cdtr');
          $cdtr->addChild('Nm',Organisme::getInstance()->getNom()); //nom de l'odg

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
          $othr->addChild("Id",$facture->getSociete()->getMandatSepaIdentifiant());  //id sepa
          $schmeNm = $othr->addChild("SchmeNm");
          $schmeNm->addChild("Prtry","SEPA");

          $drctdbttxinf = $pmtInf->addChild("DrctDbtTxInf");

          $pmtid = $drctdbttxinf->addChild("PmtId");
          $pmtid->addChild("EndToEndId", Organisme::getInstance()->getNom()." Facture"); //intitule pour l'ODG
          $montant = $drctdbttxinf->addChild("InstdAmt",$facture->getMandatSepaMontant($i));  //montant  //bug ? pas le meme montant que dans la facture
          $montant->addAttribute('Ccy', "EUR");

          $drctdbttx = $drctdbttxinf->addChild("DrctDbtTx");
          $mndtRltdInf = $drctdbttx->addChild("MndtRltdInf");
          $mndtRltdInf->addChild("MndtId", $mandatSepa->getNumeroRum());  //identifiant rum pour l'intant j'ai pas cette info
          $mndtRltdInf->addChild("DtOfSgntr",$mandatSepa->getDateFr()); //date de signature du sepa  //il faudra l'enregistrés au moment ou ça passe à 1

          $dbtrAgt = $drctdbttxinf->addChild("DbtrAgt");
          $finInstnId = $dbtrAgt->addChild("FinInstnId");
          $finInstnId->addChild('BIC',$mandatSepa->getBic()); //son bic

          $dbtr = $drctdbttxinf->addChild("Dbtr");
          $dbtr->addChild("Nm",$facture->declarant->raison_sociale); // sa raison Social  //ou $facture->getSociete()->getRaisonSociale()

          $dbtracct = $drctdbttxinf->addChild("DbtrAcct");
          $idDbtracct = $dbtracct->addChild('Id');
          $idDbtracct->addChild('IBAN',$mandatSepa->getIbanFormate()); //son iban

          $rmtinf = $drctdbttxinf->addChild("RmtInf");
          $rmtinf->addChild("Ustrd","Facture"); //libelle bancaire pour lui

        }
    }
  }

  public function getFacturesId(){
      $ids = [];
      foreach($this->factures as $facture){
        $ids[] = $facture->id;
      }
      return $ids;
  }

  public static function getExportXMLSepaForCurrentPrelevements($auto_save = false) {
      $factures = FactureEtablissementView::getInstance()->getPaiementNonExecuteSepa();  //toutes les factures avec non execute à true.
      $sepa = new ExportXMlSEPA($factures, $auto_save);
      return $sepa;
  }

}
