<?php

class ConfigurationClient extends acCouchdbClient {

	protected $configurations = array();

    protected $countries = null;
    protected $campagne_vinicole_manager = null;
    protected $campagne_parcellaire_manager = null;
    protected $campagne_facturation_manager = null;

    const CAMPAGNE_DATE_DEBUT = '%s-08-01';
    const CAMPAGNE_DATE_FIN = '%s-07-31';

    const VALUE_LIBELLE_CERTIFICATION = 0;
    const VALUE_LIBELLE_GENRE = 1;
    const VALUE_LIBELLE_APPELLATION = 2;
    const VALUE_LIBELLE_MENTION = 3;
    const VALUE_LIBELLE_LIEU = 4;
    const VALUE_LIBELLE_COULEUR = 5;
    const VALUE_LIBELLE_CEPAGE = 6;

    const VALUE_CODE_CERTIFICATION = 0;
    const VALUE_CODE_GENRE = 1;
    const VALUE_CODE_APPELLATION = 2;
    const VALUE_CODE_MENTION = 3;
    const VALUE_CODE_LIEU = 4;
    const VALUE_CODE_COULEUR = 5;
    const VALUE_CODE_CEPAGE = 6;

	/**
	*
	* @return CurrentClient
	*/
	public static function getInstance() {

	  	return acCouchdbManager::getClient("CONFIGURATION");
	}


	/**
	*
	* @return Current
	*/
	public static function getCurrent() {

		return self::getInstance()->getConfiguration();
	}

    public static function getConfiguration($date = null) {

        return self::getInstance()->findConfiguration($date);
    }

    public static function getConfigurationByCampagne($campagne) {
        $date = self::getInstance()->getCampagneVinicole()->getDateDebutByCampagne($campagne);

        return self::getInstance()->getConfiguration($date);
    }

    public function findConfiguration($date = null) {
        if(is_null($date)) {
            $date = date('Y-m-d');
        }

        $current = CurrentClient::getCurrent();
        $id = $current->getConfigurationId($date);

        if(array_key_exists($id, $this->configurations)) {

            return $this->configurations[$id];
        }

        $this->configurations[$id] = $this->cacheFindConfigurationForCache($id);

        return $this->configurations[$id];
    }

	public function cacheFindConfigurationForCache($id) {

        return CacheFunction::cache('model', "ConfigurationClientCache::findConfigurationForCache", array($id));
    }

    public function findConfigurationForCache($id) {
        $configuration = $this->find($id);
        $configuration->prepareCache();

        return $configuration;
    }

    public function cacheResetConfiguration() {
        CacheFunction::remove('model');
    }

	public function getCampagneManager($format = CampagneManager::FORMAT_PREMIERE_ANNEE) {

		return new CampagneManager('09-01', $format);
	}

    public function getCampagneParcellaire() {
        if(is_null($this->campagne_parcellaire_manager)) {
            $this->campagne_parcellaire_manager = new CampagneManager('03-01');
        }
        return $this->campagne_parcellaire_manager;
    }

    public function getCampagneVinicole() {
        if(is_null($this->campagne_vinicole_manager)) {

            $this->campagne_vinicole_manager = new CampagneManager('08-01');
        }

        return $this->campagne_vinicole_manager;
    }

    public function getCampagneFacturation() {
        if(is_null($this->campagne_facturation_manager)) {

            $this->campagne_facturation_manager = new CampagneManager('01-01');
        }

        return $this->campagne_facturation_manager;
    }

    public function buildCampagne($date) {

        return $this->getCampagneVinicole()->getCampagneByDate($date);
    }

    public function getDateDebutCampagne($campagne) {

        return $this->getCampagneVinicole()->getDateDebutByCampagne($campagne);
    }

    public function getDateFinCampagne($campagne) {

        return $this->getCampagneVinicole()->getDateFinByCampagne($campagne);
    }

    public function getCurrentCampagne() {

        return $this->getCampagneVinicole()->getCurrent();
    }

    public function getCurrentAnneeRecolte() {

        return $this->getCampagneVinicole()->getCurrentAnneeRecolte();
    }

    public function getPreviousCampagne($campagne) {

        return $this->getCampagneVinicole()->getPrevious($campagne);
    }

    public function getNextCampagne($campagne) {

        return $this->getCampagneVinicole()->getNext($campagne);
    }

    public function isDebutCampagne($periode) {

        return $this->getMois($periode) == 8;
    }

    public function getMois($periode) {

        return preg_replace('/([0-9]{4})([0-9]{2})/', '$2', $periode);
    }

    public function getAnnee($periode) {

        return preg_replace('/([0-9]{4})([0-9]{2})/', '$1', $periode);
    }

    public function getPeriodeLibelle($periode) {
      return $this->getMoisLibelle($periode).' '.$this->getAnnee($periode);
    }

    public function getMoisLibelle($periode) {
        $date = new sfDateFormat('fr_FR');

        if(!preg_match('/([0-9]{4})([0-9]{2})/', $periode, $matches)) {

            return null;
        }

        return $date->format(sprintf('%s-%s-%s', $matches[1], $matches[2], '01'), 'MMMM');
    }

    public function buildPeriode($annee, $mois) {

        return sprintf("%04d%02d", $annee, $mois);
    }

    public function buildPeriodeFromDate($date) {

        return $this->buildPeriode(date('Y', strtotime($date)), date('m', strtotime($date)));
    }

    public function buildDate($periode) {
        $lastDay = date('t',mktime(0, 0, 0, $this->getMois($periode), 1, $this->getAnnee($periode)));
        return sprintf('%4d-%02d-%02d', $this->getAnnee($periode), $this->getMois($periode), $lastDay);
    }

    public function getPeriodeDebut($campagne) {

        return date('Ym', strtotime(ConfigurationClient::getInstance()->getDateDebutCampagne($campagne)));
    }

    public function getPeriodeFin($campagne) {

        return date('Ym', strtotime(ConfigurationClient::getInstance()->getDateFinCampagne($campagne)));
    }

    public function buildCampagneFromPeriode($periode) {
        return $this->buildCampagne($this->buildDate($periode));
    }

	public function buildCampagneFromYearOrCampagne($year) {
		$year = preg_replace('/-\d+/', '', $year);
		return $this->buildCampagne($year."-10-01");
	}

    public function getPeriodeSuivante($periode) {
        $nextMonth = $this->getMois($periode) + 1;
        $nextYear = $this->getAnnee($periode);

        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        return $this->buildPeriode($nextYear, $nextMonth);
    }

      public function getPeriodePrecedente($periode) {
        $previousMonth = $this->getMois($periode) - 1;
        $previousYear = $this->getAnnee($periode);

        if ($previousMonth < 1) {
            $previousMonth = 12;
            $previousYear--;
        }

        return $this->buildPeriode($previousYear, $previousMonth);
    }

    public function getCurrentPeriode() {

        return date('Ym');
    }

    public function getCountryList() {
        if(is_null($this->countries)) {
            $destinationChoicesWidget = new sfWidgetFormI18nChoiceCountry(array('culture' => 'fr', 'add_empty' => true));
            $this->countries = $destinationChoicesWidget->getChoices();
            $this->countries['inconnu'] = 'Inconnu';
            $this->countries['UE'] = 'Union Européenne';
            $this->countries['HORS_UE'] = 'Hors Union Européenne';
            $this->countries['CW'] = 'Curaçao';
        }

        return $this->countries;
    }

    public function getCountry($code) {
        $countries = $this->getCountryList();

        return $countries[$code];
    }

    public function findCountryByCode($code) {
        $code = strtoupper($code);

        if(!array_key_exists($code, $this->getCountryList())) {

            return null;
        }

        return $code;

    }

    public function findCountryByLibelle($libelle) {
        $libelleSlugified = KeyInflector::slugify($libelle);
        foreach($this->getCountryList() as $code => $name) {
            if(KeyInflector::slugify($name) == $libelleSlugified) {

                return $code;
            }
        }

        return null;
    }

    public function findCountry($code_or_libelle) {
        $code = $this->findCountryByCode($code_or_libelle);

        if($code) {

            return $code;
        }

        return $this->findCountryByLibelle($code_or_libelle);
    }

    public function formatLibelles($libelles, $format = "%g% %a% %m% %l% %co% %ce%") {
        $format_index = array('%c%' => self::VALUE_LIBELLE_CERTIFICATION,
                          '%g%' => self::VALUE_LIBELLE_GENRE,
                          '%a%' => self::VALUE_LIBELLE_APPELLATION,
                          '%m%' => self::VALUE_LIBELLE_MENTION,
                          '%l%' => self::VALUE_LIBELLE_LIEU,
                          '%co%' => self::VALUE_LIBELLE_COULEUR,
                          '%ce%' => self::VALUE_LIBELLE_CEPAGE);

        $libelle = $format;

        foreach($format_index as $key => $item) {
          if (isset($libelles[$item])) {
            $libelle = str_replace($key, $libelles[$item], $libelle);
          } else {
            $libelle = str_replace($key, "", $libelle);
          }
        }

        $libelle = preg_replace('/ +/', ' ', $libelle);

        return $libelle;
    }

    public function formatCodes($codes, $format = "%g%%a%%m%%l%%co%%ce%") {
        $format_index = array('%c%' => self::VALUE_CODE_CERTIFICATION,
                              '%g%' => self::VALUE_CODE_GENRE,
                              '%a%' => self::VALUE_CODE_APPELLATION,
                              '%m%' => self::VALUE_CODE_MENTION,
                              '%l%' => self::VALUE_CODE_LIEU,
                              '%co%' => self::VALUE_CODE_COULEUR,
                              '%ce%' => self::VALUE_CODE_CEPAGE);

        $code = $format;

        foreach($format_index as $key => $item) {
            if (isset($codes[$item])) {
                $code = str_replace($key, $codes[$item], $code);
            } else {
                $code = str_replace($key, "", $code);
            }
        }
    }

    public function formatLabelsLibelle($labels, $format = "%la%", $separator = ", ") {

        return str_replace("%la%", implode($separator, $labels), $format);
    }

    public function fork($fork_doc_id, $configuration = null) {
        if(is_null($configuration)) {
            $configuration = self::getCurrent();
        }

        $fork = clone $configuration;
        $fork->_id = $fork_doc_id;
        $fork->declaration->compressDroits();

        return $fork;
    }

		public function convertHashProduitForDRM($hashProduit){
			$oldHashProduit = preg_replace(
			'/(declaration)\/(certification)s\/([A-Z_]*)\/(genre)s\/([A-Z]*)\/(appellation)s\/([A-Z]*)\/(mention)s\/([A-Z]*)\/(lieu)x\/([A-Z]*)\/(couleur)s\/([A-Z]*)\/(cepage)s\/([A-Z]*)/'
			,'\1/\2/\4/\6_\7/\8/\10/\12/\14_\15',$hashProduit);

			return $oldHashProduit;
		}

    public function isOpenODG() {
        return ! $this->isGiilda();
    }

    public function isGiilda() {
        return class_exists("DRMConfiguration");
    }

}

class ConfigurationClientCache {
	public static function findConfigurationForCache($id) {
        $configuration = ConfigurationClient::getInstance()->find($id);
        $configuration->prepareCache();

        return $configuration;
    }
}
