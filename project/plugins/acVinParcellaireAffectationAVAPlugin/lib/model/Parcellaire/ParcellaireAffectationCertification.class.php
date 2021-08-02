<?php
/**
 * Model for ParcellaireCertification
 *
 */

class ParcellaireAffectationCertification extends BaseParcellaireAffectationCertification {
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