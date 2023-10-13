<?php

class CourrierClient extends acCouchdbClient {

    const TYPE_MODEL = "Courrier";
    const TYPE_COUCHDB = "COURRIER";

    const COURRIER_NC_Passage1 = '01_NC_Passage1';
    const COURRIER_AVIS13 = '10_Avis_13';
    const COURRIER_AVIS14 = '10_Avis_14';
    const COURRIER_AVIS15 = '10_Avis_15';
    const COURRIER_AVIS16 = '10_Avis_16';
    const COURRIER_AVIS17 = '10_Avis_17';
    const COURRIER_AVIS18 = '10_Avis_18';
    const COURRIER_AVIS19 = '10_Avis_19';
    const COURRIER_FICHE_CONTROLE = '20_Fiche_Controle';

    public static $courrier_titre = array(
        self::COURRIER_NC_Passage1 => 'Résultat de lot non conforme',
        self::COURRIER_AVIS13 => 'Avis de Manquement Contrôle Vin (C13)',
        self::COURRIER_AVIS14 => 'Avis de Conformité Contrôle Vin (C14)',
        self::COURRIER_AVIS15 => 'Leve de Manquement Après Recours Contrôle Vin (C15)',
        self::COURRIER_AVIS16 => 'Avis de Manquement Suite à Recours (C16)',
        self::COURRIER_AVIS17 => 'Leve de Manquement Contrôle Vin (C17)',
        self::COURRIER_AVIS18 => 'Avis de Manquement Suite à Nouveau Contrôle Vin (C18)',
        self::COURRIER_AVIS19 => 'Avis de Manquement Suite à Recours INAO (C19)',
    );

    public static $courrier_templates_pages = array(
            self::COURRIER_NC_Passage1 => ['degustationNonConformitePDF_page1', 'degustationNonConformitePDF_page2'],
            self::COURRIER_AVIS13 => ['courrierAvisC13ManquementControleVinPDF'],
            self::COURRIER_AVIS14 => [''],
            self::COURRIER_AVIS15 => [''],
            self::COURRIER_AVIS16 => [''],
            self::COURRIER_AVIS17 => [''],
            self::COURRIER_AVIS18 => [''],
            self::COURRIER_AVIS19 => [''],
    );

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

    public function getTitres() {
        return self::$courrier_titre;
    }

    public function getTitre($courrier_key) {
        if (!isset(self::$courrier_titre[$courrier_key])) {
            throw new sfException('unknown key '.$courrier_key);
        }
        return self::$courrier_titre[$courrier_key];
    }

    public function getNbPages($courrier_key) {
        if (!isset(self::$courrier_templates_pages[$courrier_key])) {
            throw new sfException('unknown key '.$courrier_key);
        }
        return count(self::$courrier_templates_pages[$courrier_key]);
    }

    public function getPDFTemplateNameForPageId($courrier_key, $i) {
        if ($this->getNbPages($courrier_key) < $i) {
            throw new sfException('wrong page id '.$id.' for '.$courrier_key);
        }
        return self::$courrier_templates_pages[$courrier_key][$i];
    }

}
