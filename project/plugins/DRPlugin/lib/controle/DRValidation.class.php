<?php

class DRValidation extends DocumentValidation
{
    private $configuration;

    public function __construct($document, $options = null)
    {
        $this->configuration = $options['configuration'] ?? ConfigurationClient::getInstance()->getCurrent();
        parent::__construct($document, $options);
    }

    public function configure()
    {
        $this->addControle(self::TYPE_WARNING, 'rendement_manquant', "Rendement non présent en configuration");
        $this->addControle(self::TYPE_WARNING, 'rendement_ligne_manquante', "Il manque une ligne dans le produit");
        $this->addControle(self::TYPE_WARNING, 'rendement_declaration', "Le rendement n'est pas respecté");
        if (class_exists(ParcellaireManquant::class)  && in_array('parcellaireManquant', sfConfig::get('sf_enabled_modules')) ) {
            $this->addControle(self::TYPE_WARNING, 'pied_mort_present', "Déclaration de pied mort présente");
            $this->addControle(self::TYPE_ERROR, 'pied_mort_manquant', "Il manque la déclaration de pied mort");
        }
        if (class_exists(ParcellaireAffectation::class) && in_array('parcellaireAffectation', sfConfig::get('sf_enabled_modules')) ) {
            $this->addControle(self::TYPE_WARNING, 'parcellaire_manquant', "Il manque la déclaration d'affectation parcellaire");
        }
        $this->addControle(self::TYPE_ERROR, 'non_habilite', "L'apporteur n'est pas habilité.");
        $this->addControle(self::TYPE_ERROR, 'pas_habilitation', "L'apporteur n'a pas d'habilitation.");
    }

    public function controle()
    {
        if (!DRConfiguration::getInstance()->hasValidationDR()) {
            return ;
        }
        $this->document->generateDonnees();
        foreach ($this->document->getProduits() as $produit) {
            $this->controleRendement($produit);
        }

        if (! ($this->document->type == 'DR')) {
            $apporteurs_hash = array();
            foreach ($this->document->getEnhancedDonnees() as $donnee) {
                if (!$donnee->tiers || !$donnee->produit) {
                    continue;
                }
                $apporteurs_hash[$donnee->tiers][$donnee->produit] = $donnee->produit;
            }

            foreach ($apporteurs_hash as $apporteur => $tab_hash) {
                $habilitation = HabilitationClient::getInstance()->findPreviousByIdentifiantAndDate(str_replace('ETABLISSEMENT-', '', $apporteur), $this->document->getDateDocument());
                if (! $habilitation) {
                    $this->addPoint(self::TYPE_ERROR, 'pas_habilitation', EtablissementClient::getInstance()->findByIdentifiant(str_replace('ETABLISSEMENT-', '', $apporteur))->getRaisonSociale());

                    continue;
                }
                foreach ($tab_hash as $hash) {
                    if (! $habilitation->isHabiliteFor($hash, HabilitationClient::ACTIVITE_PRODUCTEUR)) {
                        $this->addPoint(self::TYPE_ERROR, 'non_habilite', EtablissementClient::getInstance()->findByIdentifiant(str_replace('ETABLISSEMENT-', '', $apporteur))->getRaisonSociale(), $this->generateUrl('habilitation_visualisation', array('id' => $habilitation->_id)));
                    }
                }
            }
        }

        $this->controleDocuments();
    }

    public function controleRendement($produit)
    {
        $produit_conf = $this->configuration->declaration->get($produit['hash']);

        if (! $produit_conf->hasRendement()) {
            $this->addPoint(self::TYPE_WARNING, 'rendement_manquant', "Il n'y a pas de rendement pour le produit : ".$produit['libelle']);
            return 0;
        }

        $missing_line = false;
        if (($this->document->type == "DR")) {
            if (array_key_exists('04', $produit['lignes']) === false || array_key_exists('05', $produit['lignes']) === false) {
                $this->addPoint(self::TYPE_WARNING, 'rendement_ligne_manquante', "Il manque une ligne pour le calcul du rendement L5 : <strong>".$produit['libelle']."</strong>");
                $missing_line = true;
            }elseif (round(($produit['lignes']['05']['val'] - $produit['lignes']['16']['val']) / $produit['lignes']['04']['val'], 2) > $produit_conf->getRendementDrL5()) {
                $this->addPoint(
                    self::TYPE_WARNING,
                    'rendement_declaration',
                    "Le rendement L5 du produit <strong>".$produit['libelle']."</strong> est de " . round(($produit['lignes']['05']['val'] - $produit['lignes']['16']['val']) / $produit['lignes']['04']['val'], 2) . " hl/ha, " ."le maximum étant <strong>".$produit_conf->getRendementDrL5()."</strong> hl/ha"
                );
            }
        }

        if (array_key_exists('04', $produit['lignes']) === false || array_key_exists('15', $produit['lignes']) === false) {
            $this->addPoint(self::TYPE_WARNING, 'rendement_ligne_manquante', "Il manque une ligne pour le calcul du rendement L15 : <strong>".$produit['libelle']."</strong>");
            $missing_line = true;
        }

        if ($missing_line) {
            return 0;
        }

        if (round($produit['lignes']['15']['val'] / $produit['lignes']['04']['val'], 2) > $produit_conf->getRendementDrL15()) {
            $this->addPoint(
                self::TYPE_WARNING,
                'rendement_declaration',
                "Le rendement L15 du produit <strong>".$produit['libelle']."</strong> est de " . round($produit['lignes']['15']['val'] / $produit['lignes']['04']['val'], 2) . " hl/ha, " ."le maximum étant <strong>".$produit_conf->getRendementDrL15()."</strong> hl/ha"
            );
        }
    }

    public function controleDocuments()
    {
        if (class_exists(ParcellaireManquant::class) && in_array('parcellaireManquant', sfConfig::get('sf_enabled_modules')) ) {
            if ($this->document->getType() !== DRClient::TYPE_MODEL) {
                return false;
            }

            $PM = ParcellaireManquantClient::getInstance()->find(
                ParcellaireManquantClient::getInstance()->buildId($this->document->getIdentifiant(), $this->document->getPeriode())
            );
            if ($PM === null || $PM->periode !== $this->document->campagne) {
                $this->addPoint(self::TYPE_ERROR, 'pied_mort_manquant', "Il manque la déclaration de pied mort pour cette campagne");
            } else {
                $this->addPoint(self::TYPE_WARNING, 'pied_mort_present', "N'oubliez pas de vérifier que les rendements prennent en compte les informations déclarées");
            }
        }

        if (class_exists(ParcellaireAffectation::class) && in_array('parcellaireAffectation', sfConfig::get('sf_enabled_modules')) ) {
            if ($this->document->getType() !== DRClient::TYPE_MODEL) {
                return false;
            }

            $dap = ParcellaireAffectationClient::getInstance()->find(
                ParcellaireAffectationClient::getInstance()->buildId($this->document->getIdentifiant(), $this->document->getPeriode())
            );

            if ($dap === null || $dap->periode !== $this->document->campagne) {
                $this->addPoint(self::TYPE_WARNING, 'parcellaire_manquant', "Il manque la déclaration d'affectation parcellaire pour cette campagne");
            }
        }
    }
}
