<?php

class Email {

    private static $_instance = null;
    protected $_context;

    public function __construct($context = null) {
        $this->_context = ($context) ? $context : sfContext::getInstance();
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Date'));
    }

    public static function getInstance($context = null) {
        if (is_null(self::$_instance)) {
            self::$_instance = new Email($context);
        }
        return self::$_instance;
    }

    public function sendDRevValidation($drev) {
        $nbSent = 0;
        $messages = $this->getMessagesDRevValidation($drev);
        foreach($messages as $message) {
            $this->getMailer()->send($message);
            $nbSent++;
        }
        return $nbSent;
    }

    public function getMessagesDRevValidation($drev) {
        if($drev->isPapier()) {

            return array();
        }

        if(!$drev->validation) {
            return array();
        }

        if(class_exists("DrevConfiguration") && !$drev->validation_odg && DrevConfiguration::getInstance()->hasNotifPourApprobation()) {

            return Email::getInstance()->getMessageDRevValidationNotificationSyndicat($drev);
        }

        if(!$drev->validation_odg) {

            return Email::getInstance()->getMessageDRevValidationDeclarant($drev);
        }

        return Email::getInstance()->getMessageDRevConfirmee($drev);
    }

    public function getMessageDRevValidationDeclarant($drev) {
        if(class_exists("DrevConfiguration") && !DrevConfiguration::getInstance()->isSendMailToOperateur()) {

            return array();
        }
        if (!$drev->declarant->email) {

            return array();
        }
        $to = array($drev->declarant->email);
        $subject = 'Validation de votre Déclaration de Revendication';
        $body = $this->getBodyFromPartial('send_drev_validation', array('drev' => $drev));
        $message = $this->newMailInstance()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body);

        return array($message);
    }

    public function getMessageDRevValidationNotificationSyndicat($drev) {

    $body = $this->getBodyFromPartial('send_drev_validation_odg', array('drev' => $drev));
    $subject = 'Validation de la Déclaration de Revendication de ' . $drev->declarant->raison_sociale;
    return $this->newMailInstance()
        ->setTo(array(Organisme::getInstance()->getEmail()))
        ->setSubject($subject)
        ->setBody($body);

}

    public function getMessageDRevConfirmee($drev) {
        if(class_exists("DrevConfiguration") && !DrevConfiguration::getInstance()->isSendMailToOperateur()) {

            return array();
        }
        if (!$drev->declarant->email) {

            return array();
        }

        $pdf = new ExportDRevPdf($drev);
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->generate();
        $pdfAttachment = new Swift_Attachment($pdf->output(), $pdf->getFileName(), 'application/pdf');

        $to = array($drev->declarant->email);
        $subject = 'Validation de votre Déclaration de Revendication';
        $body = $this->getBodyFromPartial('send_drev_confirmee', array('drev' => $drev));
        $message = $this->newMailInstance()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body)
                ->attach($pdfAttachment);

        return array($message);
    }

    public function getMessageDrevPapierConfirmee($drev) {
        if(class_exists("DrevConfiguration") && !DrevConfiguration::getInstance()->isSendMailToOperateur()) {

            return array();
        }
        if (!$drev->declarant->email) {

            return array();
        }

        $to = array($drev->declarant->email);
        $subject = 'Réception de votre Déclaration de Revendication';
        $body = $this->getBodyFromPartial('send_drev_confirmee_papier', array('drev' => $drev));
        $message = $this->newMailInstance()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body);

        return array($message);
    }

    public function sendDRevRappelDocuments($drev) {
        if (!$drev->declarant->email) {

            return;
        }

        if ($drev->hasCompleteDocuments()) {

            return;
        }

        $partial = 'send_drev_rappel_documents';
        $subject = "Rappel - Documents à envoyer pour votre déclaration de Revendication";

        if ($drev->exist('documents_rappels') && count($drev->documents_rappels->toArray(true, false)) > 0) {
            $partial = 'send_drev_rappel_documents_second';
            $subject = "2ème Rappel - Documents à envoyer pour la validation définitive de votre déclaration de Revendication";
        }

        $to = array($drev->declarant->email);
        $body = $this->getBodyFromPartial($partial, array('drev' => $drev));
        $message = $this->newMailInstance()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body);

        return $this->getMailer()->send($message);
    }

    public function sendDRevMarcValidation($drevmarc) {
        if (!$drevmarc->declarant->email) {

            return;
        }

        $to = array($drevmarc->declarant->email);
        $subject = "Validation de votre Déclaration de Revendication Marc d'Alsace Gewurztraminer";
        $body = $this->getBodyFromPartial('send_drevmarc_validation', array('drevmarc' => $drevmarc));
        $message = $this->newMailInstance()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body);

        return $this->getMailer()->send($message);
    }

    public function sendDRevMarcConfirmee($drevmarc) {
        if (!$drevmarc->declarant->email) {

            return;
        }

        $pdf = new ExportDRevMarcPDF($drevmarc);
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->generate();
        $pdfAttachment = new Swift_Attachment($pdf->output(), $pdf->getFileName(), 'application/pdf');

        $to = array($drevmarc->declarant->email);
        $subject = "Validation définitive de votre Déclaration de Revendication Marc d'Alsace Gewurztraminer";
        $body = $this->getBodyFromPartial('send_drevmarc_confirmee', array('drevmarc' => $drevmarc));
        $message = $this->newMailInstance()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body)
                ->attach($pdfAttachment);

        return $this->getMailer()->send($message);
    }

    public function sendTravauxMarcValidation($travauxmarc) {
        if (!$travauxmarc->declarant->email) {

            return;
        }

        $to = array($travauxmarc->declarant->email);
        $subject = "Validation de votre déclaration d'ouverture des travaux de distillation";
        $body = $this->getBodyFromPartial('send_travauxmarc_validation', array('travauxmarc' => $travauxmarc));
        $message = $this->newMailInstance()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body);

        return $this->getMailer()->send($message);
    }

    public function sendTravauxMarcConfirmee($travauxmarc) {
        if (!$travauxmarc->declarant->email) {

            return;
        }

        $pdf = new ExportTravauxMarcPDF($travauxmarc);
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->generate();
        $pdfAttachment = new Swift_Attachment($pdf->output(), $pdf->getFileName(), 'application/pdf');

        $to = array($travauxmarc->declarant->email);
        $subject = "Validation définitive de votre déclaration d'ouverture des travaux de distillation";
        $body = $this->getBodyFromPartial('send_travauxmarc_confirmee', array('travauxmarc' => $travauxmarc));
        $message = $this->newMailInstance()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body)
                ->attach($pdfAttachment);

        return $this->getMailer()->send($message);
    }

    public function sendParcellaireValidation($parcellaire) {
        if (!$parcellaire->declarant->email) {

            return;
        }

        $csv = new ExportParcellaireAffectationCSV($parcellaire);
        $csvAttachment = new Swift_Attachment(utf8_decode($csv->export()), $csv->getFileName(true, $parcellaire->declarant->nom), 'text/csv');

        $pdf = new ExportParcellaireAffectationPDF($parcellaire);
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->generate();
        $pdfAttachment = new Swift_Attachment($pdf->output(), $pdf->getFileName(), 'application/pdf');

        $to = array($parcellaire->declarant->email);
        $titre = ($parcellaire->isIntentionCremant())? 'intention de production' : 'affectation parcellaire';
        $complement = '';
        if($parcellaire->isParcellaireCremant()) {
        	if($parcellaire->isIntentionCremant()) {
        		$complement = ' AOC Crémant d\'Alsace';
        	} else {
        		$complement = ' Crémant';
        	}
        }
        $subject = sprintf("Validation de votre déclaration d'$titre%s", $complement);
        $body = $this->getBodyFromPartial('send_parcellaire_validation', array('parcellaire' => $parcellaire));
        $message = $this->newMailInstance()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body)
                ->attach($pdfAttachment)
                ->attach($csvAttachment);

        return $this->getMailer()->send($message);
    }

    public function sendParcellaireAcheteur($parcellaire, $acheteur) {
        if (!$acheteur->email || $acheteur->email_envoye || !$parcellaire->autorisation_acheteur) {

            return false;
        }

        $csv = new ExportParcellaireAffectationCSV($parcellaire);
        $csvAttachment = new Swift_Attachment(utf8_decode($csv->export($acheteur->cvi)), $csv->getFileName(false, $acheteur->nom), 'text/csv');

        $pdf = new ExportParcellaireAffectationPDF($parcellaire);
        $pdf->setCviFilter($acheteur->cvi, $acheteur->nom);
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->generate();
        $pdfAttachment = new Swift_Attachment($pdf->output(), $pdf->getFileName(), 'application/pdf');

        $to = array($acheteur->email);
        $titre = ($parcellaire->isIntentionCremant())? 'intention de production' : 'affectation parcellaire';
        $complement = '';
        if($parcellaire->isParcellaireCremant()) {
        	if($parcellaire->isIntentionCremant()) {
        		$complement = ' AOC Crémant d\'Alsace';
        	} else {
        		$complement = ' Crémant';
        	}
        }
        $subject = sprintf("Déclaration d'$titre%s de %s", $complement, $parcellaire->declarant->nom);
        $body = $this->getBodyFromPartial('send_parcellaire_acheteur', array('parcellaire' => $parcellaire));
        $message = $this->newMailInstance()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body)
                ->attach($csvAttachment)
                ->attach($pdfAttachment);

        return $this->getMailer()->send($message);
    }

    public function sendDegustationOperateursMails($degustation) {
        foreach ($degustation->operateurs as $key => $operateur) {
            $to = $operateur->email;
            $subject = "Avis de passage en vue d'une dégustation conseil ODG-AVA le " . Date::francizeDate($operateur->date_prelevement);
            $body = $this->getBodyFromPartial('send_degustation_operateur', array('operateur' => $operateur));

            if (!$operateur->email) {
                $to = array(Organisme::getInstance()->getEmail());
                $subject = "[$operateur->raison_sociale : EMAIL NON ENVOYE] " . $subject;
                $body = sprintf("/!\ L'email n'a pas pu être envoyé pour cet opérateur car il ne possède pas d'adresse email/!\\n\n%s (%s)\n\nFiche contact : %s\n\n----------------------------------\n\n%s", $operateur->raison_sociale, $operateur->cvi, $this->getAction()->generateUrl("compte_visualisation", array("id" => "COMPTE-E" . $operateur->getKey()), true), $body);
            }

            $message = $this->newMailInstance()
                    ->setTo($to)
                    ->setSubject($subject)
                    ->setBody($body);
            $this->getMailer()->send($message);
        }

        return true;
    }

    public function sendDegustationDegustateursMails($degustation) {
        foreach ($degustation->degustateurs as $types_degustateur => $comptes) {
            foreach ($comptes as $id_compte => $degustateur_node) {
                $to = $degustateur_node->email;
                $subject = "L'AVA vous invite à une dégustation conseil le " . Date::francizeDate($degustation->date) . ' à ' . $degustation->heure;
                $body = $this->getBodyFromPartial('send_degustation_degustateur', array('degustation' => $degustation));

                if (!$degustateur_node->email) {
                    $to = array(Organisme::getInstance()->getEmail());
                    $subject = "[$degustateur_node->nom : EMAIL NON ENVOYE] " . $subject;
                    $body = sprintf("/!\ L'email n'a pas pu être envoyé pour ce dégustateur car il ne possède pas d'adresse email/!\\n\n%s\n\nfiche contact : %s\n\n----------------------------------\n\n%s", $degustateur_node->nom, $this->getAction()->generateUrl("compte_visualisation", array("id" => $degustateur_node->getKey()), true), $body);
                }

                $message = $this->newMailInstance()
                        ->setTo($to)
                        ->setSubject($subject)
                        ->setBody($body);

                $this->getMailer()->send($message);
            }
        }

        return true;
    }

    public function sendDegustationNoteCourrier($courrier) {
        foreach ($courrier->prelevements as $prelevement) {
            if (!is_null($prelevement->courrier_envoye)) {
                continue;
            }

            if (!$courrier->operateur->email) {
                continue;
            }

            $subject = "Rapport de note de l'AVA suite à la dégustation conseil du " . Date::francizeDate($courrier->operateur->date_degustation);
            $body = $this->getBodyFromPartial('send_degustation_note_degustateur', array('degustation' => $courrier->operateur));
            $to = $courrier->operateur->email;

            $message = $this->newMailInstance()
                    ->setTo($to)
                    ->setSubject($subject)
                    ->setBody($body);

            $pdf = new ExportDegustationPDF($courrier->operateur, $prelevement);
            $pdf->setPartialFunction(array($this, 'getPartial'));
            $pdf->generate();
            $pdfAttachment = new Swift_Attachment($pdf->output(), $pdf->getFileName(), 'application/pdf');
            $message->attach($pdfAttachment);

            $message;
            if ($this->getMailer()->send($message)) {
                $prelevement->courrier_envoye = date('Y-m-d');
            }
            $courrier->operateur->save();
        }
        return true;
    }

    public function sendPriseDeRendezvousMails(Rendezvous $rendezvous) {
        $to = sfConfig::get('app_email_plugin_to_notification');
        $subject = "Nouvelle prise de rendez-vous pour " . $rendezvous->raison_sociale . " le " . $rendezvous->getDateHeureFr();

        $body = $this->getBodyFromPartial('send_notification_prise_rendezvous', array('rendezvous' => $rendezvous));

        $message = $this->newMailInstance()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body);

        $message;
        $this->getMailer()->send($message);
    }

    public function sendConstatApprouveMail(Constats $constats, $constatNode) {
        $to = $constats->email;
        $subject = "Constat VT/SGN du " . ucfirst(format_date($constatNode->date_signature, "P", "fr_FR"));

        $pdf = new ExportConstatPDF($constats,$constatNode->getKey());
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->generate();
        $pdfAttachment = new Swift_Attachment($pdf->output(), $pdf->getFileName(), 'application/pdf');

        $body = $this->getBodyFromPartial('send_constat_approuve', array('constats' => $constats, 'constat' => $constatNode));
        $message = $this->newMailInstance()
                ->setTo($to)
                ->setBcc(sfConfig::get('app_email_plugin_to_notification'))
                ->setSubject($subject)
                ->setBody($body);

        $message->attach($pdfAttachment);

        $message;
        $this->getMailer()->send($message);
    }

        public function sendTirageValidation($tirage) {
        if (!$tirage->declarant->email) {

            return;
        }

        $pdf = new ExportTiragePDF($tirage);
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->generate();
        $pdfAttachment = new Swift_Attachment($pdf->output(), $pdf->getFileName(), 'application/pdf');

        $to = array($tirage->declarant->email);
        $subject = "Validation de votre déclaration de tirage de Crémant d'Alsace";
        $body = $this->getBodyFromPartial('send_tirage_validation', array('tirage' => $tirage));
        $message = $this->newMailInstance()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body)
                ->attach($pdfAttachment);
        return $this->getMailer()->send($message);
    }

    public function sendTirageConfirmee($tirage) {
        if (!$tirage->declarant->email) {

            return;
        }

        $pdf = new ExportTiragePDF($tirage);
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->generate();
        $pdfAttachment = new Swift_Attachment($pdf->output(), $pdf->getFileName(), 'application/pdf');

        $to = array($tirage->declarant->email);
        $subject = "Validation définitive de votre Déclaration de Tirage de Crémant d'Alsace";
        $body = $this->getBodyFromPartial('send_tirage_confirmee', array('tirage' => $tirage));
        $message = $this->newMailInstance()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body)
                ->attach($pdfAttachment);

        return $this->getMailer()->send($message);
    }

    public function sendNotificationModificationsExploitation($etablissement, $updatedValues) {
        $to = sfConfig::get('app_email_plugin_to_notification');

        $subject = "Modification des informations d'exploitation";
        $body = $this->getBodyFromPartial('send_notification_modifications_exploitation', array('etablissement' => $etablissement, 'updatedValues' => $updatedValues));
        $message = $this->newMailInstance()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body);

        return $this->getMailer()->send($message);
    }


    public function sendConfirmationDegustateursMails($degustation) {
        foreach ($degustation->degustateurs as $college_key => $collegeComptes) {
            foreach ($collegeComptes as $id_compte => $degustateur) {
              $this->sendConfirmationDegustateurMail($degustation, $id_compte, $college_key);
            }
        }
        return true;
    }

    public function sendConfirmationDegustateurMail($degustation, $id_compte, $college_key) {
      $compte = CompteClient::getInstance()->find($id_compte);

      $to = $compte->email;
      $subject = Organisme::getInstance(null, 'degustation')->getNom()." - Convocation pour une dégustation le " . ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm");

      $body = $this->getBodyFromPartial('send_convocation_degustateur', array('degustation' => $degustation, 'identifiant' => $id_compte, 'college' => $college_key));

      if (!$compte->email) {
          $to = Organisme::getInstance(null, 'degustation')->getEmail();
          $subject = "[$compte->nom : EMAIL NON ENVOYE] " . $subject;
          $body = sprintf("/!\ L'email n'a pas pu être envoyé pour ce dégustateur car il ne possède pas d'adresse email/!\\n\n%s\n\nfiche contact : %s\n\n----------------------------------\n\n%s", $compte->getLibelleWithAdresse(), $this->getAction()->generateUrl('compte_visualisation', array('identifiant' => $compte->identifiant), true), $body);
      } else {
        $degustation->setDateEmailConvocationDegustateur(date('Y-m-d'), $id_compte, $college_key);
        $degustation->save(false);
      }

      $message = $this->newInstance('degustation')
              ->setTo($to)
              ->setSubject($subject)
              ->setBody($body);

      return $this->getMailer()->send($message);
    }

    public function sendActionDegustateurAuthMail($degustation, $degustateur, $action) {
        $to = Organisme::getInstance(null, 'degustation')->getEmail();

        $degust_nom = explode(' —', $degustateur->libelle)[0];
        $action_libelle = ($action) ? "présence" : "absence";

        $subject = "Dégustation " .$degustation->getDateFormat('d/m/Y')." : $action_libelle de $degust_nom";

        $body  = "Bonjour,\n\nDes infos ont été reçues concernant la présence de ".$degustateur->libelle." : \n\n";
        $body .= "Dégustation: ".$degustation->getDateFormat('d/m/Y')." à ".$degustation->getDateFormat('G:i')."\n";
        $body .= "Présence: ";
        $body .= ($action) ? "OUI" : 'NON';
        $body .= "\n\n";

        $message = $this->newMailInstance()
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body);

        return $this->getMailer()->send($message);
    }

    public function sendAdelpheValidation($adelphe) {
      if (!$adelphe->declarant->email) {
          return false;
      }
      $from = array(sfConfig::get('app_email_plugin_from_adresse') => sfConfig::get('app_email_plugin_from_name'));
      $to = array($adelphe->declarant->email);
      $subject = 'Validation de votre Déclaration Adelphe';
      $body = $this->getBodyFromPartial('send_adelphe_validation', array('adelphe' => $adelphe));
      $message = $this->newMailInstance()
              ->setFrom($from)
              ->setTo($to)
              ->setSubject($subject)
              ->setBody($body)
              ->setContentType('text/plain');
      return $this->getMailer()->send($message);
    }

    public function getMessageFacture($facture) {
        $email = null;
        if(!class_exists("SocieteClient")) {
            $email = $facture->getCompte()->email;
        } else {
            $email = $facture->getSociete()->getEmailCompta();
        }

        if(!$email) {
            return;
        }

        $message = $this->newMailInstance()
         ->setTo($email)
         ->setSubject(GenerationFactureMail::getSujet($facture->getNumeroOdg()))
         ->setBody($this->getPartial("facturation/email", array('id' => $facture->_id)));

         return $message;
    }

    protected function getMailer() {
        return $this->_context->getMailer();
    }

    protected function getBodyFromPartial($partial, $vars = null) {

        return $this->getAction()->getPartial('Email/' . $partial, $vars);
    }

    protected function getAction() {

        return $this->_context->getController()->getAction('Email', 'main');
    }

    public function getPartial($templateName, $vars = null) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $vars = null !== $vars ? $vars : $this->varHolder->getAll();

        return get_partial($templateName, $vars);
    }

    public function getFrom() {

        return array(sfConfig::get('app_email_plugin_from_adresse') => sfConfig::get('app_email_plugin_from_name'));
    }

    public function newMailInstance($organisme_type = null) {

        $email = Swift_Message::newInstance()
                        ->setFrom($this->getFrom())
                        ->setContentType('text/plain');

        $reply_to = []
        if (sfConfig::get('app_email_plugin_reply_to_adresse') {
            $reply_to[sfConfig::get('app_email_plugin_reply_to_adresse'] = sfConfig::get('app_email_plugin_reply_to_name'));
        }
        if ( ! count($reply_to) || $organisme_type) {
            if ($organisme_type) {
                if ($organisme_type == 'facturation') {
                   $reply_to[Organisme::getInstance()->getEmailFacturation()] = Organisme::getInstance()->getNomFacturation();
                } else {
                    $reply_to[Organisme::getInstance(null, $organisme_type)->getEmail()] = Organisme::getInstance(null, $organisme_type)->getNom();
                }
            } else {
                $reply_to[Organisme::getInstance()->getEmail()] = Organisme::getInstance()->getNom();
            }
        }
        if ( ! count($reply_to) ) {
            $reply_to[sfConfig::get('app_email_plugin_from_adresse'] = sfConfig::get('app_email_plugin_from_name'));
        }

        if ($reply_to){
            $email = $emails->setReplyTo($reply_to);
        }
        return $email;
    }
}
