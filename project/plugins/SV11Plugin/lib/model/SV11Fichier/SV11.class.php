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

    public function hasApporteurs($include_non_reconnu = false) {
        return count($this->getApporteurs($include_non_reconnu));
    }

    public function getApporteurs($include_non_reconnu = false, $hydrate = acCouchdbClient::HYDRATE_JSON): array {
        $cvis = array();
        foreach($this->getCsv() as $data) {
            $cvi = $data[DouaneCsvFile::CSV_TIERS_CVI];
            $cvi = str_replace('"', '', $cvi);
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
                if ($include_non_reconnu) {
                    $etablissements[$cvi] = "apporteur non connu - $cvi";
                }
				continue;
			}
            $etablissements[$etablissement->_id] = $etablissement->raison_sociale." - ".$etablissement->cvi;
        }

        return $etablissements;
    }

    public function isValideeOdg() {
        if (DRConfiguration::getInstance()->hasValidationDR()) {
            return $this->exist('validation_odg') && ($this->validation_odg);
        }
        return false;
    }
}
