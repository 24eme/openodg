<?php
/**
 * Model for Current
 *
 */

class Current extends BaseCurrent {

    public function __construct() {
        parent::__construct();
        $this->set('_id', 'CURRENT');
    }

    public function getPeriode()
    {
    	return date('Y-m');
    }

    public function getConfigurationId($date) {
        foreach($this->configurations as $confDate => $confId) {
            if($date >= $confDate) {

                return $confId;
            }
        }

        throw new sfException(sprintf("Pas de configuration pour cette date %s", $date));
    }

    public function reorderConfigurations() {
        $configurations = $this->configurations->toArray(true, false);

        krsort($configurations);

        $this->remove('configurations');
        $this->add('configurations', $configurations);
    }

    public function save() {
        parent::save();
        CurrentClient::getInstance()->cacheResetConfiguration();
    }

    /* A remplacer */

    public function getCampagne() {
        if(CurrentClient::getInstance()->hasCurrentFromTheFuture()) {

            return CurrentClient::getInstance()->getCurrentFromTheFuture();
        }

        return "2017";
    }

    public function isDSDecembre() {

        return false;
    }

    public function getPeriodeDS() {

        if(CurrentClient::getInstance()->hasCurrentFromTheFuture()) {

            return CurrentClient::getInstance()->getCurrentFromTheFuture()."07";
        }

        return "201707";
    }

    public function getAnneeDS($type_ds = null)
    {
        return substr($this->getPeriodeDS(), 0, 4);
    }

    public function getMonthDS($type_ds = null)
    {
        return substr($this->getPeriodeDSByType($type_ds), 4, 2);
    }

    public function getPeriodeDSByType($type_ds = null){

        return sfContext::getInstance()->getUser()->getPeriodeDS($type_ds);
    }

    /* Fin */

}
