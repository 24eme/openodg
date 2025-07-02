<?php

class ParcellaireAffectationValidation extends DocumentValidation {

    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';
    const TYPE_ENGAGEMENT = 'engagement';

    public function __construct($document, $options = null) {
        parent::__construct($document, $options);
    }

    public function configure() {
        /*
         * Warning
         */
        $this->addControle(self::TYPE_ERROR, 'surface_vide', 'Superficie nulle (0 are)');
        $this->addControle(self::TYPE_WARNING, 'parcelle_doublon', 'Parcelle doublonnée');
        $this->addControle(self::TYPE_WARNING, 'parcelle_inconnue', 'Parcelle inconnue dans le parcellaire');
        $this->addControle(self::TYPE_ERROR, 'acheteur_repartition', "La répartition des acheteurs n'est pas complète");
        $this->addControle(self::TYPE_ERROR, 'acheteur_repartition_parcelles', "La répartition des acheteurs par parcelles n'est pas complète");
        $this->addControle(self::TYPE_ERROR, 'parcellaire_multiappellation', "Parcelle déclarée plusieurs fois");
        $this->addControle(self::TYPE_ERROR, 'jeunes_vignes_vtsgn', 'La date de plantation ne peut être inférieure à 9 ans pour les vins VT/SGN');
        $this->addControle(self::TYPE_ERROR, 'jeunes_vignes_grdcru_communale_lieudit', 'La date de plantation ne peut être inférieure à 3 ans pour les vins');
        $this->addControle(self::TYPE_ERROR, 'jeunes_vignes_cremant', 'La date de plantation ne peut être inférieure à 2 ans pour les vins');

        /*
         * Error
         */
//        $this->addControle(self::TYPE_ERROR, 'parcellaire_invalidproduct', "Ce cépage non autorisé");
    }

    public function controle() {
        $uniq_appellation = array();
        $uniqParcelles = array();

        foreach ($this->document->declaration->getProduitsCepageDetails() as $detailk => $detailv) {
            if(!$detailv->isAffectee()) {
                continue;
            }
            $pid = $detailv->getAppellation()->getHash().' '.$detailv->section . ' ' . $detailv->numero_parcelle;

            $appellation_id = $detailv->section . ' ' . $detailv->numero_parcelle.' '.$detailv->superficie;
            if ($detailv->vtsgn) {
              $appellation_id .= ' VT/SGN';
            }

            if(!isset($uniq_appellation[$appellation_id])) {
                $uniq_appellation[$appellation_id] = array();
            }
            $uniq_appellation[$appellation_id][$detailv->getAppellationLibelle()] = $detailk;

            if (!$detailv->superficie) {
                $this->addPoint(self::TYPE_ERROR, 'surface_vide', 'parcelle n°' . $detailv->section . ' ' . $detailv->numero_parcelle . ' à ' . $detailv->commune . ' déclarée en ' . $detailv->getLibelleComplet(), $this->generateUrl('parcellaire_parcelles', array('id' => $this->document->_id,
                            'appellation' => preg_replace('/appellation_/', '', $detailv->getAppellation()->getKey()),
                            'erreur' => $detailv->getHashForKey())));
            }

            $keyParcelle = $detailv->getCepage()->getHash() . '/' . $detailv->getCommune() . '-' . $detailv->getSection() . '-' . $detailv->getNumeroParcelle().'-'.sprintf("%0.4f", $detailv->superficie);
            if (array_key_exists($keyParcelle, $uniqParcelles)) {
                $this->addPoint(self::TYPE_WARNING, 'parcelle_doublon', 'parcelle n°' . $detailv->getSection() . ' ' . $detailv->getNumeroParcelle() . ' à ' . $detailv->getCommune() . ' déclarée en ' . $detailv->getLibelleComplet(), $this->generateUrl('parcellaire_parcelles', array('id' => $this->document->_id,
                            'appellation' => preg_replace('/appellation_/', '', $detailv->getAppellation()->getKey()),
                            'erreur' => $detailv->getHashForKey())));
            }elseif (!$detailv->getParcelleParcellaire()) {
                $this->addPoint(self::TYPE_WARNING, 'parcelle_inconnue', 'parcelle n°' . $detailv->getSection() . ' ' . $detailv->getNumeroParcelle() . ' à ' . $detailv->getCommune() . ' déclarée en ' . $detailv->getLibelleComplet().' ('.$detailv->getIDU().')', $this->generateUrl('parcellaire_parcelles', array('id' => $this->document->_id,
                            'appellation' => preg_replace('/appellation_/', '', $detailv->getAppellation()->getKey()),
                            'erreur' => $detailv->getHashForKey())));
            } else {
                $uniqParcelles[$keyParcelle] = $keyParcelle;
            }
        }
        foreach ($uniq_appellation as $pid => $phashes) {
            if (count(array_keys($phashes)) > 1) {
                $detail = $this->document->get(array_shift($phashes));
                $this->addPoint(self::TYPE_ERROR, 'parcellaire_multiappellation', '<a href="' . $this->generateUrl('parcellaire_parcelles', array(
                            'id' => $this->document->_id,
                            'appellation' => preg_replace('/appellation_/', '', $detail->getAppellation()->getKey()),
                            'attention' => $detail->getHashForKey())) . "\" class='alert-link' >La parcelle " . $detail->section . ' ' . $detail->numero_parcelle . ' à ' . $detail->commune . " a été déclarée sur plusieurs appellations. </a>"
                            , '');
            }
        }

        $acheteurs = $this->document->getAcheteursByHash();
        $acheteursUsed = array();
        $erreurRepartition = false;
        $hasParcelle = false;
        foreach ($this->document->declaration->getProduitsWithLieuEditable() as $hash => $produit) {
            $lieu_key = $produit->getLieuKeyFromHash($hash);
            if(!$produit->isAffectee($lieu_key)) {
                continue;
            }
            $hasParcelle = true;

            $acheteursParcelle = $produit->getAcheteursByHash($lieu_key);

            if(!count($acheteursParcelle)) {
                $this->addPoint(self::TYPE_ERROR, 'acheteur_repartition', 'Terminer la répartition des acheteurs', $this->generateUrl('parcellaire_acheteurs', array('id' => $this->document->_id)));
                $erreurRepartition = true;
                break;
            }

            foreach($acheteursParcelle as $hash => $acheteurParcelle) {
                $acheteursUsed[$hash] = $acheteurParcelle;
            }
        }

        if($hasParcelle && !$erreurRepartition && count($acheteurs) != count($acheteursUsed)) {
            $this->addPoint(self::TYPE_ERROR, 'acheteur_repartition', 'Terminer la répartition des acheteurs', $this->generateUrl('parcellaire_acheteurs', array('id' => $this->document->_id)));
        }

        foreach($this->document->declaration->getProduitsCepageDetails() as $detail) {
            if($detail->active && $detail->hasMultipleAcheteur() && (!$detail->exist('acheteurs'))) {
                $this->addPoint(self::TYPE_ERROR, 'acheteur_repartition_parcelles', 'Terminer la répartition des acheteurs', $this->generateUrl('parcellaire_acheteurs_parcelles', array('id' => $this->document->_id)));
                break;
            }

        }

        if (ParcellaireConfiguration::getInstance()->hasJeunesVignes()) {
            $this->controleErrorJeunesVignes();
        }
    }

    protected function controleErrorJeunesVignes() {

        $dateDebutCampagne = new DateTime(ConfigurationClient::getInstance()->getCampagneManager()->getDateDebutByCampagne($this->document->getCampagne()));
        $jeunesVignesVtsgn = ParcellaireConfiguration::getInstance()->getAnneeJeunesVignesVtsgn();
        $jeunesVignesGrdCruCommuLieuDit = ParcellaireConfiguration::getInstance()->getAnneeJeunesVignesGrdCruCommunalLieuDit();
        $jeunesVignesCremant = ParcellaireConfiguration::getInstance()->getAnneeJeunesVignesCremant();

        foreach ($this->document->declaration->getProduitsCepageDetails() as $produitDetailKey => $produitDetailValue ) {
            $annee_plantation = substr($produitDetailValue->campagne_plantation, 5, 4);
            $date_plantation = DateTimeImmutable::createFromFormat('Y-m-d', $annee_plantation . '-07-31');

            if ($produitDetailValue->vtsgn && $date_plantation && ($date_plantation->diff($dateDebutCampagne)->y <= $jeunesVignesVtsgn)) {
                $this->addPoint(self::TYPE_ERROR, 'jeunes_vignes_vtsgn', '<a href="' . $this->generateUrl('parcellaire_parcelles', array(
                    'id' => $this->document->_id,
                    'appellation' => preg_replace('/appellation_/', '', ParcellaireAffectationClient::APPELLATION_VTSGN),
                    'attention' => $produitDetailValue->getHashForKey())) . "\" class='alert-link' >Parcelle " . $produitDetailValue->section . ' ' . $produitDetailValue->numero_parcelle . ' à ' . $produitDetailValue->commune . " </a>"
                    , '');
            }

            if (strpos($produitDetailValue->produitHash, 'GRDCRU') || strpos($produitDetailValue->produitHash, 'LIEUDIT') || strpos($produitDetailValue->produitHash, 'COMMUNALE')) {
                if ($date_plantation && ($date_plantation->diff($dateDebutCampagne)->y <= $jeunesVignesGrdCruCommuLieuDit)) {
                    $this->addPoint(self::TYPE_ERROR, 'jeunes_vignes_grdcru_communale_lieudit', '<a href="' . $this->generateUrl('parcellaire_parcelles', array(
                        'id' => $this->document->_id,
                        'appellation' => preg_replace('/appellation_/', '', $produitDetailValue->getAppellation()->getKey()),
                        'attention' => $produitDetailValue->getHashForKey())) . "\" class='alert-link' > " . preg_replace('/appellation_/', '', $produitDetailValue->getAppellation()->getKey()) . " - Parcelle  " . $produitDetailValue->section . ' ' . $produitDetailValue->numero_parcelle . ' à ' . $produitDetailValue->commune . " </a>"
                        , '');
                }
            }

            if (strpos($produitDetailValue->produitHash, 'CREMANT') && $date_plantation && ($date_plantation->diff($dateDebutCampagne)->y <= $jeunesVignesCremant)) {
                    $this->addPoint(self::TYPE_ERROR, 'jeunes_vignes_cremant', '<a href="' . $this->generateUrl('parcellaire_parcelles', array(
                        'id' => $this->document->_id,
                        'appellation' => preg_replace('/appellation_/', '', $produitDetailValue->getAppellation()->getKey()),
                        'attention' => $produitDetailValue->getHashForKey())) . "\" class='alert-link' > " . preg_replace('/appellation_/', '', $produitDetailValue->getAppellation()->getKey()) . " - Parcelle " . $produitDetailValue->section . ' ' . $produitDetailValue->numero_parcelle . ' à ' . $produitDetailValue->commune . " </a>"
                        , '');
            }
        }
    }
}
