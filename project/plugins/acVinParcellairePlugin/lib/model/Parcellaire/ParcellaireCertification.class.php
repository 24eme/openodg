<?php
/**
 * Model for ParcellaireCertification
 *
 */

class ParcellaireCertification extends BaseParcellaireCertification {
    public function getChildrenNode() 
    {
        return $this->getGenres();
    }

    public function getGenres()
    {
        return $this->filter('^genre');
    }
    public function getLibelleComplet() {
        return "";
    }
}