<?php

class ControleManquementsForm extends acCouchdbForm
{
    public function configure()
    {
        $listeManquements = $this->getDocument()->getManquements();
        if (! $listeManquements) {return;}
        foreach ($listeManquements as $rtmId => $manquement) {
            $this->embedForm($rtmId, new ControleManquementForm($manquement));
        }
        $this->widgetSchema->setNameFormat('controleManquements[%s]');
    }

    public function save()
    {
        $values = $this->getValues();
        $controle = $this->getDocument();
        $listeManquements = $controle->getListeManquements();

        foreach ($values as $key => $manquementInfos) {
            if ($key == '_revision') {continue;}
            if (! $controle->manquements->exist($key)) {
                $controle->manquements->add($key, $listeManquements[$key]);
            } else {
                if ($manquementInfos['manquement_checkbox'] == null) {
                    $controle->manquements->$key->actif = false;
                } else {
                    $controle->manquements->$key->actif = true;
                }
                $controle->manquements->$key->observations = $manquementInfos['observations'];
            }
        }
        $controle->save();
    }
}
