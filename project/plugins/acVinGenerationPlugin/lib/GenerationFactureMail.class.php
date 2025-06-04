<?php

class GenerationFactureMail extends GenerationAbstract {

    private $date_envoi = null;

    public function generateMailForADocumentId($id) {
        $facture = FactureClient::getInstance()->find($id);

        return Email::getInstance()->getMessageFacture($facture);
    }

    public static function getSujet($numero) {

        return "Facture n°$numero - ".Organisme::getInstance()->getNom();
    }

    public static function getActionLibelle() {

        return "Envoyer les factures par mail";
    }

    public static function getActionDescription() {

        return "Sujet : ".self::getSujet("XXXXXXX")."\n\n".Email::getInstance()->getPartial("facturation/email", array('id' => 'FACTURE-XXXXXX-XXXXXXXXXX'));
    }

    public function getMailer() {

        return sfContext::getInstance()->getMailer();
    }

    public function getLogPath() {

        return sfConfig::get('sf_web_dir')."/generation/".$this->getLogFilname();
    }

    public function getPublishFile() {

        return urlencode("/generation/".$this->getLogFilname());
    }

    public function getLogFilname() {

        if (!$this->date_envoi) {
            $this->date_envoi = date('YmdHis');
        }

        return $this->generation->date_emission."-facture-envoi-mails-".$this->date_envoi.".csv";
    }

    public function getLogs() {

        return $this->logs;
    }

    public function addLog($factureId, $statut, $commentaire, $date = null) {
        $header = false;
        if(!file_exists($this->getLogPath())) {
            $header = true;
        }

        $fp = fopen($this->getLogPath(), 'a');

        if($header) {
            fputcsv($fp, array("Date", "Numéro de facture", "Identifiant Opérateur", "Raison sociale", "Email", "Statut", "Commentaire", "Facture ID", "Lien de téléchargement"));
        }

        fputcsv($fp, $this->getLog($factureId, $statut, $commentaire, $date));

        fclose($fp);
    }

    public function getLog($factureId, $statut, $commentaire, $date = null) {
        if(!$date) {
            $date = date("Y-m-d H:i:s");
        }

        $facture = FactureClient::getInstance()->find($factureId);
	$email = null;
        if(!class_exists("SocieteClient")) {
            $email = $facture->getCompte()->email;
        } else {
            $email = $facture->getSociete()->getEmailCompta();
        }

        return array($date, $facture->getNumeroOdg(), $facture->identifiant, $facture->declarant->raison_sociale, $email, $statut, $commentaire, $facture->_id, ProjectConfiguration::getAppRouting()->generate('piece_public_view', array('doc_id' => $facture->_id, 'auth' => UrlSecurity::generateAuthKey($facture->_id)), true));
    }

    public function generate() {
        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENCOURS);
        $this->generation->save();

        $factureAEnvoyer = array();
        $factureDejaEnvoye = $this->generation->documents->toArray();
        $sleepMaxBatch = 5;
        $sleepSecond = 2;
        $i = 0;
        foreach($this->generation->getMasterGeneration()->documents as $factureId) {
            if(in_array($factureId, $factureDejaEnvoye)) {
                continue;
            }
            $mail = $this->generateMailForADocumentId($factureId);

            if(!$mail) {
                $this->addLog($factureId, "PAS_DE_MAIL", "generateMailForADocumentId n'a pas retourné de mail");
                continue;
            }

            $sended = $this->getMailer()->send($mail);

            if(!$sended) {
                $this->addLog($factureId, "ERREUR", "L'envoi de mail a retourné une erreur");
                continue;
            }

            $this->addLog($factureId, "ENVOYÉ", "mail envoyé avec succes");

            if(!in_array($factureId, $factureDejaEnvoye)) {
                $this->generation->documents->add(null, $factureId);
            }
            $this->generation->save();
            $i++;
            if($i > $sleepMaxBatch) {
                sleep($sleepSecond);
                $i = 0;
            }
        }

        if(!$this->generation->exist('fichiers/'.$this->getPublishFile())) {
            $this->generation->add('fichiers')->add($this->getPublishFile(), "Logs d'envoi de mails");
        }

        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_GENERE);
        $this->generation->save();
    }
}
