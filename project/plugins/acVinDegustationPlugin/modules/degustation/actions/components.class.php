<?php

class degustationComponents extends sfComponents {

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

        $this->subject = sprintf("%s - Résultat de dégustation du %s",Organisme::getInstance(null, 'degustation')->getNom(), ucfirst(format_date($this->degustation->date, "P", "fr_FR")));
        $this->email = EtablissementClient::getInstance()->find($this->identifiant)->getEmail();
        $this->cc = Organisme::getInstance(null, 'degustation')->getEmail();
    }

    public function executeSyntheseCommercialise(sfWebRequest $request) {
        if (isset($this->millesimes)) {
            $this->campagnes = array();
            foreach($this->millesimes as $m) {
                $this->campagnes[] = sprintf('%d-%d', $m, $m + 1);
            }
        }
        $this->syntheseLots = LotsClient::getInstance()->getSyntheseLots($this->identifiant, $this->campagnes, $this->region);
    }
}
