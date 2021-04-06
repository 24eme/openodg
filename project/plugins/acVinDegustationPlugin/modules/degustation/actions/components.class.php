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

        $app = strtoupper(sfConfig::get('sf_app'));
        $courrierInfos = sfConfig::get('app_facture_emetteur')[$app];

        $cc = $courrierInfos['email'];
        $subject = sprintf("%s - Résultat de dégustation du %s", $courrierInfos['service_facturation'], ucfirst(format_date($this->degustation->date, "P", "fr_FR")));
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
        $app = strtoupper(sfConfig::get('sf_app'));
        $courrierInfos = sfConfig::get('app_facture_emetteur')[$app];

        $this->subject = sprintf("%s - Résultat de dégustation du %s",$courrierInfos['service_facturation'], ucfirst(format_date($this->degustation->date, "P", "fr_FR")));
        $this->email = EtablissementClient::getInstance()->find($this->identifiant)->getEmail();
        $this->cc = $courrierInfos['email'];
    }
}
