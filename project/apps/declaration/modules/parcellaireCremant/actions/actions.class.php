<?php

class parcellaireCremantActions extends sfActions {

    public function executeCreate(sfWebRequest $request) {

        $etablissement = $this->getRoute()->getEtablissement();
        $this->parcellaireCremant = ParcellaireClient::getInstance()->findOrCreate($etablissement->cvi, ConfigurationClient::getInstance()->getCampagneManager()->getCurrent(),true);
        $this->parcellaireCremant->save();
        return $this->redirect('parcellaire_edit', $this->parcellaireCremant);
    }

}
