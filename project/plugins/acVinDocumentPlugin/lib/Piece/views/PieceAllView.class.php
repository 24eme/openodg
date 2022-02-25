<?php

class PieceAllView extends acCouchdbView
{

	const KEYS_VISIBILITE = 0;
    const KEYS_IDENTIFIANT = 1;
    const KEYS_DATE_DEPOT = 2;
    const KEYS_CATEGORIE = 3;
    const KEYS_LIBELLE = 4;
    const KEYS_MIME = 5;
    const KEYS_SOURCE = 6;

    const VALUES_KEY = 0;
    const VALUES_FICHIERS = 1;

    public static function getInstance() {
        return acCouchdbManager::getView('piece', 'all');
    }

 	public function getAll() {
        return $this->client->getView($this->design, $this->view)->rows;
 	}

    public function getPiecesByEtablissement($etablissement, $allVisibilite = false, $startdate = null, $enddate = null, $categories = null) {
    	$start = array($etablissement);
    	$end = array($etablissement);
		if($startdate) {
			$start[] = $startdate;
		}
		if($enddate) {
			$end[] = $enddate;
		}

    	$end[] = array();
    	$visibles = array_reverse($this->client
    			->startkey(array_merge(array(1), $start))
    			->endkey(array_merge(array(1), $end))
    			->reduce(false)
    			->getView($this->design, $this->view)->rows);
    	$nonVisibles = array();
    	if ($allVisibilite) {
    		$nonVisibles = array_reverse($this->client
    				->startkey(array_merge(array(0), $start))
    				->endkey(array_merge(array(0), $end))
    				->reduce(false)
    				->getView($this->design, $this->view)->rows);
    	}
			$pieces = $this->cleanVersions(array_merge($nonVisibles, $visibles));

			if($categories) {
				foreach($pieces as $key => $piece){
					if(in_array($piece->key[self::KEYS_CATEGORIE], $categories)) {
						continue;
					}

					unset($pieces[$key]);
				}
			}

			return $pieces;
    }

		private function cleanVersions($items) {
			$cleaned = array();
			$saved = array();
			foreach($items as $item){
                $key = $item->id;
				if (preg_match('/(.*)-(M|R)([0-9]+)$/', $key, $m)) {
					if (isset($saved[$m[1]]) && $m[3] < $saved[$m[1]]) {
						continue;
					}
					$saved[$m[1]] = $m[3];
					$key = $m[1];
                } elseif(isset($saved[$key])) {
                    continue;
				}

                $cleaned[] = $item;
			}
			return $cleaned;
		}

    public function getStartISODateForView() {
    	return '1900-01-01';
    }

    public function getEndISODateForView() {
    	return '9999-99-99';
    }
}
