<?php

class degustationComponents extends sfComponents {

    public function executeMailTo(sfWebRequest $request)
    {
        // degustation
        // identifiant
        // lots

        $lotsConformes = [];
        $lotsNonConformes = [];

        foreach ($this->lots as $lot) {
            switch ($lot->statut) {
                case Lot::STATUT_CONFORME:
                    $lotsConformes[] = $lot;
                    break;
                case Lot::STATUT_NONCONFORME:
                    $lotsNonConformes[] = $lot;
                    break;
            }
        }

        $email = EtablissementClient::getInstance()->find($this->identifiant)->getEmail();
        $cc = implode(sfConfig::get('app_email_plugin_to_notification'),";");
        $subject = sprintf("Résultat de dégustation du %s", ucfirst(format_date($this->degustation->date, "P", "fr_FR")));
        $body = rawurlencode(strip_tags(get_partial('degustation/notificationEmail', [
            'degustation' => $this->degustation,
            'identifiant' => $this->identifiant,
            'lotsConformes' => $lotsConformes,
            'lotsNonConformes' => $lotsNonConformes
        ])));

        $this->mailto = "mailto:$email?cc=$cc&subject=$subject&body=$body";
    }

    public function executePreviewMailPopup(sfWebRequest $request)
    {
        $this->lotsConformes = [];
        $this->lotsNonConformes = [];

        foreach ($this->lots[$this->identifiant] as $lot) {
            switch ($lot->statut) {
                case Lot::STATUT_CONFORME:
                    $this->lotsConformes[] = $lot;
                    break;
                case Lot::STATUT_NONCONFORME:
                    $this->lotsNonConformes[] = $lot;
                    break;
            }
        }

        $this->subject = sprintf("Résultat de dégustation du %s", ucfirst(format_date($this->degustation->date, "P", "fr_FR")));
        $this->email = EtablissementClient::getInstance()->find($this->identifiant)->getEmail();
        $this->cc = implode(sfConfig::get('app_email_plugin_to_notification'),";");
    }
}
