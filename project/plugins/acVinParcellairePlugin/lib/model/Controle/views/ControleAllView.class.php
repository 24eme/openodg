<?php

class ControleAllView extends acCouchdbView
{
    public static function getInstance()
    {
        return acCouchdbManager::getView('controle', 'all', 'Controle');
    }

    public function findByStatut($statut)
    {
        return acCouchdbManager::getClient()
            ->startkey([$statut])
            ->endkey([$statut, []])
            ->getView($this->design, $this->view)->rows;
    }

    public function findByDateAndAgent($date, $agent)
    {
        return acCouchdbManager::getClient()
            ->startkey([null, $date, $agent])
            ->endkey([null, $date, $agent, []])
            ->getView($this->design, $this->view)
            ->rows;
    }
}
