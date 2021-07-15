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
            if(isset($cvis[$cvi])) {
                continue;
            }

            $etablissement = EtablissementClient::getInstance()->findByCvi($cvi, true, acCouchdbClient::HYDRATE_JSON);
            if(!$etablissement) {
				$cvis[$cvi] = false;
                continue;
            }
			
            $cvis[$cvi] = $etablissement;
        }

        $etablissements = array();
        foreach($cvis as $cvi => $etablissement) {
			if(!$etablissement) {
				continue;
			}
            $etablissements[$etablissement->_id] = $etablissement->raison_sociale." - ".$etablissement->cvi;
        }

        return $etablissements;
    }
}
