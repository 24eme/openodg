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

            $rows = EtablissementFindByCviView::getInstance()->findByCvi($cvi);
            if(!count($rows)) {
                continue;
            }

            $cvis[$cvi] = $rows[0];
        }

        $etablissements = array();
        foreach($cvis as $cvi => $row) {
            $etablissements[$row->id] = $row->value[1]." - ".$row->key[0];
        }

        return $etablissements;
    }
}
