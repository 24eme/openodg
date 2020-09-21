<?php

abstract class MouvementLots extends acCouchdbDocumentTree implements InterfaceMouvementLots
{
    public function prelever() {
        return $this->preleve = ($this->prelevable);
    }
    public function liberer() {
        return $this->preleve = 0;
    }
}
