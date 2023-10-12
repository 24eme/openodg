<?php

class CourrierClient extends acCouchdbClient {

    const TYPE_MODEL = "Courrier";
    const TYPE_COUCHDB = "COURRIER";

	/**
	*
	* @return CurrentClient
	*/
	public static function getInstance() {

	  	return acCouchdbManager::getClient("COURRIER");
	}

    public function createDoc($identifiant, $type, $lot, $date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        $courrier = new Courrier($lot, $type);
        $courrier->initDoc($identifiant, null, $date);
        $courrier->storeDeclarant();
        return $courrier;
    }

    public function getTitre($titre_key) {
        $titres = $this->getTitres();
        if (!isset($titres[$titre_key])) {
            throw new sfException('unknown key '.$titre_key);
        }
        return $titres[$titre_key];
    }

    public function getTitres() {
        return array(
            'COURRIER13' => 'Avis de Manquement Contrôle Vin (C13)',
            'COURRIER14' => 'Avis de Conformité Contrôle Vin (C14)',
            'COURRIER15' => 'Leve de Manquement Après Recours Contrôle Vin (C15)',
            'COURRIER16' => 'Avis de Manquement Suite à Recours (C16)',
            'COURRIER17' => 'Leve de Manquement Contrôle Vin (C17)',
            'COURRIER18' => 'Avis de Manquement Suite à Nouveau Contrôle Vin (C18)',
            'COURRIER19' => 'Avis de Manquement Suite à Recours INAO (C19)',
            'COURRIERFicheControle' => 'Fiche de Manquement Contrôle Produit',
        );
    }

}
