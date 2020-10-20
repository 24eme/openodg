<?php

class chgtDenomActions extends sfActions {


    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_DREV, $etablissement);

        $this->lots = array();
        foreach (MouvementLotView::getInstance()->getByDeclarantIdentifiant($this->etablissement->identifiant)->rows as $item) {
            $key = Lot::generateMvtKey($item->value);
            if (preg_replace('/-.*/', '', $item->value->id_document) == ChgtDenomClient::ORIGINE_LOT) {
              $this->lots[$key] = $item->value;
            }
        }
    }

    protected function secureEtablissement($droits, $etablissement) {
        if (!EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized($droits)) {

            return $this->forwardSecure();
        }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

}
