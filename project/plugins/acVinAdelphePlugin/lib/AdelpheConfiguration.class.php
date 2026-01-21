<?php

class AdelpheConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function load() {
        $this->configuration = sfConfig::get('adelphe_configuration_adelphe', []);
    }

    public function __construct() {
        if(!sfConfig::has('adelphe_configuration_adelphe')) {
    			throw new sfException("La configuration pour adelphe n'a pas été défini pour cette application");
    		}
        $this->load();
    }

    public function getFonctionCalculSeuil() {
      if (isset($this->configuration['fonction_calcul_seuil']) && $this->configuration['fonction_calcul_seuil']) {
        return $this->configuration['fonction_calcul_seuil'];
      }
      throw new sfException('no fonction_calcul_seuil');
    }

    public function getTauxForfaitaireBib() {
      if (isset($this->configuration['taux_forfaitaire_bib']) && $this->configuration['taux_forfaitaire_bib']) {
        return $this->configuration['taux_forfaitaire_bib'];
      }
      throw new sfException('no taux_forfaitaire_bib');
    }

    public function getPrixUnitaireBib() {
        if (isset($this->configuration['prix_unitaire_bib']) && $this->configuration['prix_unitaire_bib']) {
            return $this->configuration['prix_unitaire_bib'];
        }
        throw new sfException('no prix_unitaire_bib');
    }

    public function getPrixUnitaireBib3l() {
        if (isset($this->configuration['prix_unitaire_bib_3l']) && $this->configuration['prix_unitaire_bib_3l']) {
            return $this->configuration['prix_unitaire_bib_3l'];
        }
        throw new sfException('no prix_unitaire_bib_3l');
    }

    public function getPrixUnitaireBib5l() {
        if (isset($this->configuration['prix_unitaire_bib_5l']) && $this->configuration['prix_unitaire_bib_5l']) {
            return $this->configuration['prix_unitaire_bib_5l'];
        }
        throw new sfException('no prix_unitaire_bib_5l');
    }

    public function getPrixUnitaireBib10l() {
        if (isset($this->configuration['prix_unitaire_bib_10l']) && $this->configuration['prix_unitaire_bib_10l']) {
            return $this->configuration['prix_unitaire_bib_10l'];
        }
        throw new sfException('no prix_unitaire_bib_10l');
    }

    public function getPartBib3l() {
        if (isset($this->configuration['part_bib_3l']) && $this->configuration['part_bib_3l']) {
            return $this->configuration['part_bib_3l'];
        }
        throw new sfException('no part_bib_3l');
    }

    public function getPartBib5l() {
        if (isset($this->configuration['part_bib_5l']) && $this->configuration['part_bib_5l']) {
            return $this->configuration['part_bib_5l'];
        }
        throw new sfException('no part_bib_5l');
    }

    public function getPartBib10l() {
        if (isset($this->configuration['part_bib_10l']) && $this->configuration['part_bib_10l']) {
            return $this->configuration['part_bib_10l'];
        }
        throw new sfException('no part_bib_10l');
    }

    public function getPartBouteilleAllegee() {
        if (isset($this->configuration['part_bouteille_allegee']) && $this->configuration['part_bouteille_allegee']) {
            return $this->configuration['part_bouteille_allegee'];
        }
        throw new sfException('no part_bouteille_allegee');
    }

    public function getPartBouteilleNormale() {
        if (isset($this->configuration['part_bouteille_normale']) && $this->configuration['part_bouteille_normale']) {
            return $this->configuration['part_bouteille_normale'];
        }
        throw new sfException('no part_bouteille_normale');
    }

    public function getPartCarton() {
        if (isset($this->configuration['part_carton']) && $this->configuration['part_carton']) {
            return $this->configuration['part_carton'];
        }
        throw new sfException('no part_carton');
    }

    public function getPrixUnitaireBouteille() {
        if (isset($this->configuration['prix_unitaire_bouteille']) && $this->configuration['prix_unitaire_bouteille']) {
            return $this->configuration['prix_unitaire_bouteille'];
        }
        throw new sfException('no prix_unitaire_bouteille');
    }

    public function getPrixUnitaireBouteilleNormale() {
        if (isset($this->configuration['prix_unitaire_bouteille_normale']) && $this->configuration['prix_unitaire_bouteille_normale']) {
            return $this->configuration['prix_unitaire_bouteille_normale'];
        }
        throw new sfException('no prix_unitaire_bouteille_normale');
    }

    public function getPrixUnitaireBouteilleAllegee() {
        if (isset($this->configuration['prix_unitaire_bouteille_allegee']) && $this->configuration['prix_unitaire_bouteille_allegee']) {
            return $this->configuration['prix_unitaire_bouteille_allegee'];
        }
        throw new sfException('no prix_unitaire_bouteille_allegee');
    }

    public function getPrixUnitaireCarton() {
        if (isset($this->configuration['prix_unitaire_carton']) && $this->configuration['prix_unitaire_carton']) {
            return $this->configuration['prix_unitaire_carton'];
        }
        throw new sfException('no prix_unitaire_carton');
    }

    public function getUrlAdelphe() {
        if (isset($this->configuration['url_adelphe']) && $this->configuration['url_adelphe']) {
            return $this->configuration['url_adelphe'];
        }
        throw new sfException('no url_adelphe');
    }

    public function getVolumesConditionnesCsv($annee = null) {
      $filename = null;
      if (isset($this->configuration['volumes_conditionnes_csv']) && $this->configuration['volumes_conditionnes_csv']) {
          $filename = $this->configuration['volumes_conditionnes_csv'];
      }
      if ($annee && strpos($filename, '%ANNEE%') !== false) {
        $filename = str_replace('%ANNEE%', $annee, $filename);
      }
      return sfConfig::get('sf_root_dir').$filename;
    }
}
