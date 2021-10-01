<?php

class drevmarcComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        if(!$this->periode) {
            $this->periode = $this->campagne;
        }
        $this->date_ouverture_drevmarc = sfConfig::get('app_date_ouverture_drevmarc');
        $this->drevmarc_non_ouverte = false;
        if (null !== $this->date_ouverture_drevmarc) {
            if (str_replace('-', '', $this->date_ouverture_drevmarc) > date('Ymd')) {
                $this->drevmarc_non_ouverte = true;
            }
        }
        $this->drevmarc = DRevMarcClient::getInstance()->find('DREVMARC-' . $this->etablissement->identifiant . '-' . $this->periode);
    }

}
