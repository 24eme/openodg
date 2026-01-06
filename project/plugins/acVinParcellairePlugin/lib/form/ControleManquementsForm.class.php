<?php

class ControleManquementsForm extends acCouchdbForm
{
    public function configure()
    {
        $manquementsListe = $this->getDocument()->getListeManquements();
        foreach ($manquementsListe as $rtmId => $manquement) {
            $this->embedForm($rtmId, new ControleManquementForm($manquement));
        }
    }
}
