<?php
/**
 * Model for SV11
 *
 */

class SV11 extends BaseSV11 {

	public function constructId() {
		$this->set('_id', 'SV11-' . $this->identifiant . '-' . $this->campagne);
	}

	public function getConfiguration() {

		return ConfigurationClient::getConfiguration($this->campagne.'-12-10');
	}

    public function getApporteurs($hydrate = acCouchdbClient::HYDRATE_JSON) {
        $cvis = array();
        foreach($this->getCsv() as $data) {
            $cvi = $data[DouaneCsvFile::CSV_TIERS_CVI];
            if(!$cvi) {
                continue;
            }
            $etablissement = EtablissementClient::getInstance()->findByCvi($cvi, $hydrate);

            if(!$etablissement) {
                continue;
            }

            $cvis[$etablissement->_id] = $etablissement;
        }

        return $cvis;
    }
}
