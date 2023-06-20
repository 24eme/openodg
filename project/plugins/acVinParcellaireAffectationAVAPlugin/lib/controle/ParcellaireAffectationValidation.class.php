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
        $this->addControle(self::TYPE_WARNING, 'parcellaire_complantation', 'Attention');
        $this->addControle(self::TYPE_ERROR, 'surface_vide', 'Superficie nulle (0 are)');
        $this->addControle(self::TYPE_WARNING, 'parcelle_doublon', 'Parcelle doublonnée');
        $this->addControle(self::TYPE_ERROR, 'acheteur_repartition', "La répartition des acheteurs n'est pas complète");
        $this->addControle(self::TYPE_ERROR, 'acheteur_repartition_parcelles', "La répartition des acheteurs par parcelles n'est pas complète");
        $this->addControle(self::TYPE_ERROR, 'parcelle_lieudit_obligatoire', "Le lieu-dit de cette parcelle n'a pas été renseigné");
        $this->addControle(self::TYPE_ERROR, 'parcellaire_multiappellation', "Parcelle déclarée plusieurs fois");
        /*
         * Error
         */
//        $this->addControle(self::TYPE_ERROR, 'parcellaire_invalidproduct', "Ce cépage non autorisé");
    }

    public function controle() {
        $coplant = array();
        $uniq_appellation = array();
        $uniqParcelles = array();

        foreach ($this->document->declaration->getProduitsCepageDetails() as $detailk => $detailv) {
            if(!$detailv->isAffectee()) {
                continue;
            }
            $pid = $detailv->getAppellation()->getHash().' '.$detailv->section . ' ' . $detailv->numero_parcelle;
            if (!isset($coplant[$pid])) {
                $coplant[$pid] = array();
            }
            array_push($coplant[$pid], $detailk);

            $appellation_id = $detailv->section . ' ' . $detailv->numero_parcelle.' '.$detailv->superficie;
            if(!isset($uniq_appellation[$appellation_id])) {
                $uniq_appellation[$appellation_id] = array();
            }
            $uniq_appellation[$appellation_id][$detailv->getAppellationLibelle()] = $detailk;

            if (!$detailv->superficie) {
                $this->addPoint(self::TYPE_ERROR, 'surface_vide', 'parcelle n°' . $detailv->section . ' ' . $detailv->numero_parcelle . ' à ' . $detailv->commune . ' déclarée en ' . $detailv->getLibelleComplet(), $this->generateUrl('parcellaire_parcelles', array('id' => $this->document->_id,
                            'appellation' => preg_replace('/appellation_/', '', $detailv->getAppellation()->getKey()),
                            'erreur' => $detailv->getHashForKey())));
            }

            $keyParcelle = $detailv->getCepage()->getHash() . '/' . $detailv->getCommune() . '-' . $detailv->getSection() . '-' . $detailv->getNumeroParcelle();
            if (array_key_exists($keyParcelle, $uniqParcelles) && $detailv->superficie == $uniqParcelles[$keyParcelle]->superficie) {
                $this->addPoint(self::TYPE_WARNING, 'parcelle_doublon', 'parcelle n°' . $detailv->getSection() . ' ' . $detailv->getNumeroParcelle() . ' à ' . $detailv->getCommune() . ' déclarée en ' . $detailv->getLibelleComplet(), $this->generateUrl('parcellaire_parcelles', array('id' => $this->document->_id,
                            'appellation' => preg_replace('/appellation_/', '', $detailv->getAppellation()->getKey()),
                            'erreur' => $detailv->getHashForKey())));
            } else {
                $uniqParcelles[$keyParcelle] = $detailv;
            }
        }
        foreach ($coplant as $pid => $phashes) {
            if (count($phashes) > 1) {
                $detail = $this->document->get($phashes[0]);
                $keyParcelle = $detail->getCepage()->getHash() . '/' . $detail->getCommune() . '-' . $detail->getSection() . '-' . $detail->getNumeroParcelle();
                if (array_key_exists($keyParcelle, $uniqParcelles)) {
                    continue;
                }
                $this->addPoint(self::TYPE_WARNING, 'parcellaire_complantation', '<a href="' . $this->generateUrl('parcellaire_parcelles', array(
                            'id' => $this->document->_id,
                            'appellation' => preg_replace('/appellation_/', '', $detail->getAppellation()->getKey()),
                            'attention' => $detail->getHashForKey())) . "\" class='alert-link' >La parcelle " . $detail->section . ' ' . $detail->numero_parcelle . ' à ' . $detail->commune . " a été déclarée avec plusieurs cépages. </a>"
                        . "&nbsp;S’il ne s’agit pas d’une erreur de saisie de votre part, ne tenez pas compte de ce point de vigilance.", '');
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
            if($detail->getCepage()->getConfig()->hasLieuEditable() && !$detail->lieu) {
                $this->addPoint(self::TYPE_ERROR, 'parcelle_lieudit_obligatoire',
                $detail->section . ' ' . $detail->numero_parcelle . ' à ' . $detail->commune, $this->generateUrl('parcellaire_parcelles', array(
                                        'id' => $this->document->_id,
                                        'appellation' => preg_replace('/appellation_/', '', $detail->getAppellation()->getKey()),
                                        'attention' => $detail->getHashForKey())));
            }

        }

        foreach($this->document->declaration->getProduitsCepageDetails() as $detail) {
            if($detail->active && $detail->hasMultipleAcheteur() && (!$detail->exist('acheteurs'))) {
                $this->addPoint(self::TYPE_ERROR, 'acheteur_repartition_parcelles', 'Terminer la répartition des acheteurs', $this->generateUrl('parcellaire_acheteurs_parcelles', array('id' => $this->document->_id)));
                break;
            }

        }
    }
}
