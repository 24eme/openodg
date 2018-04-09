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

    public function compose($facture) {
        $from = array(sfConfig::get('app_email_plugin_from_adresse') => sfConfig::get('app_email_plugin_from_name'));
        $to = array($facture->getCompte()->email);
        $subject = "Mise à disposition d'une nouvelle facture";
        $body = $this->getBodyFromPartial('facturation/email', array('facture' => $facture));
        $message = Swift_Message::newInstance()
                ->setFrom($from)
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body)
                ->setContentType('text/plain');

        return $message;
    }

    public function send($facture) {

        return $this->getMailer()->send($this->compose($facture));
    }
}
