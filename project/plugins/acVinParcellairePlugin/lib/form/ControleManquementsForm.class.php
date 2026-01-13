<?php

class ControleManquementsForm extends acCouchdbForm
{
    public function configure()
    {
        $listeManquements = $this->getDocument()->getListeManquements();
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
            if (! $manquementInfos['manquement_checkbox']) {continue;}

            if (! $controle->manquements->exist($key)) {
                $controle->manquements->add($key, $listeManquements[$key]);
            } else {
                $controle->manquements->$key->observations = $manquementInfos['observations'];
            }
        }
        $controle->save();
    }
}
