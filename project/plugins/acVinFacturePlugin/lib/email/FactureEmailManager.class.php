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

    public function compose($compte) {
        $factures = FactureClient::getInstance()->getFacturesByCompte($compte->identifiant, acCouchdbClient::HYDRATE_DOCUMENT);

        $facturesToSend = array();

        foreach($factures as $facture) {
            if($facture->isPayee() || $facture->isAvoir()) {
                continue;
            }

            $facturesToSend[$facture->_id] = $facture;
        }

        $from = array(sfConfig::get('app_email_plugin_from_adresse') => sfConfig::get('app_email_plugin_from_name'));
        $to = array($compte->email);
        $subject = "Cotisations AVA 2018 : factures disponibles sur votre espace";
        $body = $this->getBodyFromPartial('facturation/email', array('factures' => $facturesToSend));
        $message = Swift_Message::newInstance()
                ->setFrom($from)
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body)
                ->setContentType('text/plain');

        return $message;
    }

    public function send($compte) {

        return $this->getMailer()->send($this->compose($compte));
    }
}
