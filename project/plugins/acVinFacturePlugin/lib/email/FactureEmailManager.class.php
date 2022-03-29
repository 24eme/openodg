<?php

class FactureEmailManager extends Email
{
    private static $_instance = null;

    public static function getInstance($context = null) {
        if (is_null(self::$_instance)) {
            self::$_instance = new FactureEmailManager($context);
        }
        return self::$_instance;
    }

    protected function getBodyFromPartial($partial, $vars = null) {

        return $this->getAction()->getPartial($partial, $vars);
    }

    public function compose($compte, $campagne = null) {
        $factures = FactureClient::getInstance()->getFacturesByCompte($compte->identifiant, acCouchdbClient::HYDRATE_DOCUMENT);

        $facturesToSend = array();

        foreach($factures as $facture) {
            if($facture->isPayee() || $facture->isAvoir() || $facture->isRedressee()) {
                continue;
            }

            if($campagne && $facture->campagne != $campagne) {

                continue;
            }

            $facturesToSend[$facture->_id] = $facture;
        }

        ksort($facturesToSend);

        if(!count($facturesToSend)) {
            return false;
        }

        if(!$compte->email) {
            return false;
        }

        $from = array(sfConfig::get('app_email_plugin_from_adresse') => sfConfig::get('app_email_plugin_from_name'));
        $to = array($compte->email);
        $replyTo = array(sfConfig::get('app_email_plugin_reply_to_facturation_adresse') => sfConfig::get('app_email_plugin_reply_to_facturation_name'));
        if (!array_shift(array_values($replyTo))) {
            $replyTo = $from;
        }
        $subject = "Cotisations AVA : Factures disponibles sur votre espace";
        $body = $this->getBodyFromPartial('facturation/emailAVA', array('factures' => $facturesToSend));
        $message = Swift_Message::newInstance()
                ->setFrom($from)
                ->setTo($to)
                ->setReplyTo($replyTo)
                ->setSubject($subject)
                ->setBody($body)
                ->setContentType('text/plain');

        return $message;
    }

    public function send($compte, $campagne = null) {
        $message = $this->compose($compte, $campagne);

        if(!$message) {

            return false;
        }

        $resultat = $this->getMailer()->send($message);

        if(!$resultat) {

            return 0;
        }

        return $message;
    }
}
