<?php
/**
 * Model for Degustation
 *
 */

class TourneeDegustation extends Degustation
{
    public function __construct()
    {
        parent::__construct();
        $this->add('sous_type', "TOURNEE");
    }

    public function getSousType()
    {
        return ($this->exist('sous_type')) ? null : $this->sous_type;
    }

    public function startFromLots()
    {
        return false;
    }
}
