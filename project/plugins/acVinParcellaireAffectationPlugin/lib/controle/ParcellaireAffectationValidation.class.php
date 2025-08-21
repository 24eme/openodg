<?php

class ParcellaireAffectationValidation extends DocumentValidation {

    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';
    const TYPE_ENGAGEMENT = 'engagement';

    public function __construct($document, $options = null) {
        parent::__construct($document, $options);
    }

    public function configure() {
        $this->addControle(self::TYPE_WARNING, 'superficie_douane_depassee', "La superficie affectée est supérieure à celle de votre déclaration douanière");
        $this->addControle(self::TYPE_WARNING, 'sans_appellation_syndicat', "Produits gérés par le syndicat");
        $this->addControle(self::TYPE_WARNING, 'denomination_cvi', "Erreur CVI");
        $this->addControle(self::TYPE_WARNING, 'probleme_densite', "Ecart Pieds");
        $this->addControle(self::TYPE_WARNING, 'cepage_non_autorise', "Cépage non autorisé");
        $this->addControle(self::TYPE_WARNING, 'probleme_parcellaire', "Non conformité parcellaire");
        $this->addControle(self::TYPE_ERROR, 'sans_habilitation', "Erreur d'habilitation");
        $this->addControle(self::TYPE_ERROR, 'erreur_potentiel_production', "Potentiel de production non respecté");
    }

    public function controle() {
        $appellationGeree = false;
        $appellations = ConfigurationClient::getCurrent()->getProduits();
        foreach ($this->document->getParcelles() as $parcelle) {
            if ($parcelle->getConfig() && $parcelle->getConfig()->getAppellation()->getKey() != Configuration::DEFAULT_KEY) {
                $appellationGeree = true;
            }

            if (! $parcelle->affectee) {
                continue;
            }

            if ($parcelle->getSuperficieParcellaire() < $parcelle->superficie) {
                $this->addPoint(self::TYPE_WARNING, 'superficie_douane_depassee', "La parcelle <strong>$parcelle->section / $parcelle->numero_parcelle</strong> ($parcelle->superficie ha) dépasse celle de votre parcellaire (".$parcelle->getSuperficieParcellaire()." ha)");
            }
        }

        if ($appellationGeree === false) {
            $this->addPoint(self::TYPE_WARNING, 'sans_appellation_syndicat', "Aucune parcelle n'a de produit géré par le syndicat");
        }

        if (!$this->document->getHabilitation()) {
            $this->addPoint(self::TYPE_ERROR, 'sans_habilitation', "pas d'habilitation trouvée pour cette campagne");
        } elseif (!in_array(HabilitationClient::ACTIVITE_PRODUCTEUR, $this->document->getHabilitation()->getActivitesHabilites())) {
            $this->addPoint(self::TYPE_ERROR, 'sans_habilitation', "Pas d'activité producteur trouvée");
        } else {
            if (isset($this->document->getDestinataires()[$this->document->getEtablissementObject()->_id])) {
                if (!in_array(HabilitationClient::ACTIVITE_VINIFICATEUR, $this->document->getHabilitation()->getActivitesHabilites())) {
                    $this->addPoint(self::TYPE_ERROR, 'sans_habilitation', "Parcelles affectée en cave particulière mais pas d'habilitation en vinification");
                }
            }
        }

        if ($this->document->hasProblemProduitCVI()) {
            $this->addPoint(self::TYPE_WARNING, 'denomination_cvi', "Les parcelles mises en valeur pourrait rencontrer des problèmes de dénomination déclarée au CVI.");
        }
        if ($this->document->hasProblemEcartPieds()) {
            $this->addPoint(self::TYPE_WARNING, 'probleme_densite', "Les parcelles dont la superficie en mise en valeur pourrait rencontrer des problèmes de densité d'après l'analyse du CVI.");
        }
        if ($this->document->hasProblemCepageAutorise()) {
            $this->addPoint(self::TYPE_WARNING, 'cepage_non_autorise', "Les parcelles dont le cépage est mis en valeur pourrait rencontrer des problèmes de conformité avec le cahier des charges.");
        }
        if ($this->document->hasProblemParcellaire()) {
            $this->addPoint(self::TYPE_WARNING, 'probleme_parcellaire', "Les parcelles dont l'identifiant est mis en valeur pourrait rencontrer de conformité avec votre parcellaire CVI.");

        }
        foreach ($this->document->getProblemPortentiel() as $produit => $limit ) {
            $this->addPoint(self::TYPE_ERROR, 'erreur_potentiel_production', "Le potentiel de production n'est pas respecté pour " . $produit . ". Au vu de la sélection de vos parcelles, vous ne pouvez pas produire sur plus de " . $limit . " ha.");
        }

    }
}
