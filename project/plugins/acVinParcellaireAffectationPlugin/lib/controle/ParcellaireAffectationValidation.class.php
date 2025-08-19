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
        $this->addControle(self::TYPE_ERROR, 'sans_habilitation', "Erreur d'habilitation");
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
    }
}
