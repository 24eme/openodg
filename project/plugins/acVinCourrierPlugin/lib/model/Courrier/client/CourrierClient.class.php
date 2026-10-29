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
    const COURRIER_AVIS_PRELEVEMENT = '10_Avis_Prelevement_OIVC';
    const COURRIER_FICHE_CONTROLE = '20_Fiche_Controle';
    const COURRIER_IMPORT = '99_Import';

    public static $courrier_titre = array(
        self::COURRIER_NC_Passage1 => 'Résultat de lot non conforme',
        self::COURRIER_AVIS13 => 'Avis de Manquement Contrôle Vin (C13)',
        self::COURRIER_AVIS14 => 'Avis de Conformité Contrôle Vin (C14)',
        self::COURRIER_AVIS15 => 'Leve de Manquement Après Recours Contrôle Vin (C15)',
        self::COURRIER_AVIS16 => 'Avis de Manquement Suite à Recours (C16)',
        self::COURRIER_AVIS17 => 'Leve de Manquement Contrôle Vin (C17)',
        self::COURRIER_AVIS18 => 'Avis de Manquement Suite à Nouveau Contrôle Vin (C18)',
        self::COURRIER_AVIS19 => 'Avis de Manquement Suite à Recours INAO (C19)',
        self::COURRIER_AVIS_PRELEVEMENT => 'Avis de prélèvement par l\'OIVC',
    );

    public static $courrier_templates_pages = array(
            self::COURRIER_NC_Passage1 => ['courrierAvisC13ManquementControleVinPDF', 'degustationRapportInspection', 'degustationNonConformitePDF_page2'],
            self::COURRIER_AVIS13 => ['courrierAvisC13ManquementControleVinPDF', 'degustationRapportInspection', 'degustationNonConformitePDF_page2'],
            self::COURRIER_AVIS14 => ['courrierAvisC14ConformiteControleVinPDF', 'degustationRapportInspection'],
            self::COURRIER_AVIS15 => ['courrierLeveeC15ManquementControleVinPDF', 'degustationRapportInspection'],
            self::COURRIER_AVIS16 => ['courrierAvisC16ManquementSuiteRecoursPDF', 'degustationRapportInspection', 'degustationNonConformitePDF_page2'],
            self::COURRIER_AVIS17 => ['courrierLeveeC17ManquementControleVinPDF', 'degustationRapportInspection', 'degustationNonConformitePDF_page2'],
            self::COURRIER_AVIS18 => ['courrierAvisC18ManquementSuiteNouveauControleVinPDF', 'degustationRapportInspection', 'degustationNonConformitePDF_page2'],
            self::COURRIER_AVIS19 => ['courrierAvisC19ManquementSuiteRecoursPDF', 'degustationRapportInspection'],
            self::COURRIER_AVIS_PRELEVEMENT => ['courrierAvisDePrelevementPDF']
    );

    public static $courrier_page_extra = array(
            'degustationRapportInspection' => array(
                'representant_nom',
                'representant_fonction',
                'agent_nom',
                'analytique_date',
                'analytique_conforme',
                'analytique_conforme',
                'analytique_libelle',
                'analytique_libelle',
                'analytique_code',
                'analytique_code',
                'analytique_niveau',
                'organoleptique_code',
                'organoleptique_code',
                'organoleptique_niveau',
            ),
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
            $date = date('Y-m-d h:m:s');
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
        if (!isset(self::$courrier_templates_pages[$courrier_key]) || !isset(self::$courrier_templates_pages[$courrier_key][$i])) {
            return null;
        }
        return self::$courrier_templates_pages[$courrier_key][$i];
    }
    public function getExtraFields($courrier_key) {
        $fields = array();
        for($i = 0 ; $i < $this->getNbPages($courrier_key) ; $i++) {
            $page = $this->getPDFTemplateNameForPageId($courrier_key, $i);
            if (isset(self::$courrier_page_extra[$page])) {
                $fields = array_merge($fields, self::$courrier_page_extra[$page]);
            }
        }
        return $fields;
    }

}
