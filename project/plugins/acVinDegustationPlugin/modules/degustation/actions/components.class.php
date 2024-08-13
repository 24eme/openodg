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
                $m = intval($m);
                for($c = $m ; $c <= date('Y') && $c <= $m + 2 ; $c++) {
                    $campagne = sprintf('%d-%d', $m, $m + 1);
                    $this->campagnes[$campagne] = $campagne;
                }
            }
        }
        $this->syntheseLots = LotsClient::getInstance()->getSyntheseLots($this->identifiant, $this->campagnes, $this->region);
        if (isset($this->millesimes)) {
            $todelete = array();
            foreach($this->syntheseLots as $prod => $po) {
                foreach($po as $mil => $o) {
                    if (!in_array($mil, $this->millesimes)) {
                        $todelete["$prod $mil"] = [$prod, $mil];
                    }
                }
            }
            foreach ($todelete as $key => $value) {
                unset($this->syntheseLots[$value[0]][$value[1]]);
            }
        }
    }
}
