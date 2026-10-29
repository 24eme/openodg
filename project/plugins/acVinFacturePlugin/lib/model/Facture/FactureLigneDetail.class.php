<?php

class FactureLigneDetail extends BaseFactureLigneDetail {

    public function getLibelleComplet() {

        return $this->getParent()->getParent()->libelle." ".$this->libelle;
    }
}
