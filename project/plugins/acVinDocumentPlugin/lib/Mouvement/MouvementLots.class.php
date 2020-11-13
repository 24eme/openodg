<?php

abstract class MouvementLots extends acCouchdbDocumentTree implements InterfaceMouvementLots
{
    public function prelever() {
        return $this->statut = Lot::STATUT_PRELEVE;
    }
    public function liberer() {
        return $this->statut = Lot::STATUT_PRELEVABLE;
    }
}
