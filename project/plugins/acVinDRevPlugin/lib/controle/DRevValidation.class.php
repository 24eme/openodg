<?php

class DRevValidation extends DocumentValidation {

    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';
    const TYPE_ENGAGEMENT = 'engagement';

    public function __construct($document, $options = null) {
        parent::__construct($document, $options);
        $this->noticeVigilance = true;
    }

    public function configure() {
        /*
         * Warning
         */

        $this->addControle(self::TYPE_WARNING, 'dr_surface', 'La surface revendiquée est différente de celle déclarée de votre DR.');
        $this->addControle(self::TYPE_WARNING, 'dr_volume', 'Le volume revendiqué est différent de celui déclaré dans votre DR.');

        //$this->addControle(self::TYPE_WARNING, 'lot_vtsgn_sans_prelevement', 'Vous avez déclaré des lots VT/SGN sans spécifier de période de prélèvement.');
        $this->addControle(self::TYPE_WARNING, 'declaration_lots', 'Vous devez déclarer vos lots.');

        $this->addControle(self::TYPE_WARNING, 'revendication_cepage_sans_lot', 'Vous ne déclarez aucun lot pour un cépage que vous avez revendiqué. Si c\'est un lot qui a été replié en assemblage, ne tenez pas compte de ce point de vigilance.');

        $this->addControle(self::TYPE_WARNING, 'lot_sans_cepage_revendique', 'Vous avez déclaré un lot pour un cépage que vous n\'avez pas revendiqué.');

        /*
         * Error
         */
        $this->addControle(self::TYPE_ERROR, 'revendication_incomplete', 'Vous devez saisir la superficie et le volume pour vos produits revendiqués');

        $this->addControle(self::TYPE_ERROR, 'volume_revendique_usages_inferieur_sur_place', 'Le volume revendiqué ne peut pas être inférieur au volume sur place déduit des usages industriels.');

        $this->addControle(self::TYPE_ERROR, 'volume_revendique_superieur_sur_place', 'Le volume revendiqué ne peut pas être supérieur au volume sur place.');

        $this->addControle(self::TYPE_ERROR, 'prelevement', 'Vous devez saisir une semaine de prélèvement');
        $this->addControle(self::TYPE_ERROR, 'revendication_sans_lot', 'Vous avez revendiqué des produits sans spécifier de lots');

        $this->addControle(self::TYPE_ERROR, 'lot_sans_cepage_revendique', 'Vous avez déclaré un lot pour un cépage que vous n\'avez pas revendiqué.');


        $this->addControle(self::TYPE_ERROR, 'controle_externe_vtsgn', 'Vous devez renseigner une semaine et le nombre total de lots pour le VT/SGN');
        $this->addControle(self::TYPE_ERROR, 'periodes_cuves', 'Votre semaine de prélèvement pour le contrôle externe ne peut pas précéder celle pour la dégustation conseil.');

        /*
         * Engagement
         */
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_DR, 'Joindre une copie de votre Déclaration de Récolte');
        if ($this->document->getEtablissementObject()->hasFamille(EtablissementClient::FAMILLE_NEGOCIANT))
            $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV, 'Joindre une copie de votre SV12');
        elseif ($this->document->getEtablissementObject()->hasFamille(EtablissementClient::FAMILLE_CAVE_COOPERATIVE))
            $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV, 'Joindre une copie de votre SV11');
        else {
            $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV, 'Joindre une copie de votre SV11 ou SV12');
        }
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_PRESSOIR, 'Joindre une copie de votre carnet de pressoir');
    }

    public function controle() {

        $etablissement = $this->document->getEtablissementObject();

        $revendicationProduits = $this->document->declaration->getProduits();
        foreach ($revendicationProduits as $hash => $revendicationProduit) {
            $this->controleWarningDrSurface($revendicationProduit);
            $this->controleWarningDrVolume($revendicationProduit);
            $this->controleErrorRevendicationIncomplete($revendicationProduit);
            $this->controleErrorVolumeRevendiqueIncorrect($revendicationProduit);
            $this->controleEngagementPressoir($revendicationProduit);
        }


        $this->controleWarningRevendicationLot();
        $this->controleErrorPrelevement(DRev::CUVE_ALSACE);
        $this->controleErrorPrelevement(DRev::BOUTEILLE_ALSACE);
        $this->controleErrorPrelevement(DRev::BOUTEILLE_GRDCRU);
        $this->controleErrorPeriodes();

        if ($this->document->mustDeclareCepage()) {
            $this->controleWarningCepageSansLot(DRev::CUVE_ALSACE);
            $this->controleWarningCepageSansLot(DRev::BOUTEILLE_ALSACE);
            $this->controleWarningCepageSansLot(DRev::BOUTEILLE_GRDCRU);
            $this->controleErrorAndWarningLotSansCepage(DRev::CUVE_ALSACE, $this->document->isNonRecoltant());
            $this->controleErrorAndWarningLotSansCepage(DRev::BOUTEILLE_ALSACE, $this->document->isNonRecoltant());
            $this->controleErrorAndWarningLotSansCepage(DRev::BOUTEILLE_GRDCRU, $this->document->isNonRecoltant());
        }

        $this->controleErrorRevendicationSansLot(DRev::CUVE_ALSACE);
        $this->controleErrorRevendicationSansLot(DRev::CUVE_GRDCRU);

        $this->controleEngagementDr();
        $this->controleEngagementSv();
    }

    public function controleErrorAndWarningLotSansCepage($key, $isNegoce = false) {
        $drev = $this->document;
        if ($drev->prelevements->exist($key) && count($drev->prelevements->get($key)->lots) > 0) {
            $prelevement = $drev->prelevements->get($key);
            $hashes_lot = array();
            foreach ($drev->declaration->getProduitsCepage() as $produitCepage) {
                $hashes_lot[$produitCepage->getCepage()->getHash()] = $drev->getConfiguration()->get($produitCepage->getCepage()->getHash())->getHashRelation('lots');
            }
            foreach ($prelevement->lots as $lot) {
                $found = false;
                foreach ($hashes_lot as $hash_cepage => $hash_rev_lot) {
                    if (preg_match("|" . $hash_rev_lot . "|", $lot->hash_produit)) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $text = sprintf("%s - %s", $prelevement->libelle, $prelevement->libelle_produit . ' - ' . $lot->libelle);
                    if ($drev->isNonRecoltant()) {
                        $produit_lot_hash = self::TYPE_ERROR . str_replace('/', '-', $lot->hash_produit);
                        $url = $this->generateUrl('drev_lots', array('id' => $this->document->_id, 'prelevement' => $key)).'?error_produit='.$produit_lot_hash;
                        $this->addPoint(self::TYPE_ERROR, 'lot_sans_cepage_revendique', $text, $url);
                    } else {
                        $produit_lot_hash = self::TYPE_WARNING.'withFlash' . str_replace('/', '-', $lot->hash_produit);
                        $url = $this->generateUrl('drev_lots', array('id' => $this->document->_id, 'prelevement' => $key)).'?error_produit='.$produit_lot_hash;
                        $this->addPoint(self::TYPE_WARNING, 'lot_sans_cepage_revendique', $text, $url);
                    }
                }
            }
        }
    }

    public function controleWarningCepageSansLot($key) {
        $drev = $this->document;
        if ($drev->prelevements->exist($key) && count($drev->prelevements->get($key)->lots) > 0) {
            $prelevement = $drev->prelevements->get($key);
            $hashes_lot = array();
            foreach ($drev->declaration->getProduitsCepage() as $produitCepage) {
                $hashes_lot[$produitCepage->getCepage()->getHash()] = $drev->getConfiguration()->get($produitCepage->getCepage()->getHash())->getHashRelation('lots');
            }
            $lotsHashProduit = array();
            foreach ($hashes_lot as $hash_cepage => $hash_rev_lot) {
                $prelevement_hash_key = preg_replace('/^.*cuve_([A-Z]*).*$/', "$1", $prelevement->lots->getHash());
                $lot_hash_key = preg_replace('/^[0-9A-Za-z\/]*appellation_([A-Z]*)[_0-9A-Za-z\/]*$/', "$1", $hash_rev_lot);
                if ($prelevement_hash_key != $lot_hash_key) {
                    continue;
                }
                foreach ($prelevement->lots as $lot) {
                    $found = false;
                    if (preg_match("|" . $hash_rev_lot . "|", $lot->hash_produit)) {
                        $found = true;
                        break;
                    }
                }
                if (!$found && !in_array($hash_rev_lot, $lotsHashProduit)) {
                    $lotsHashProduit[] = $hash_rev_lot;
                    $produit_lot_hash = self::TYPE_WARNING . str_replace('/', '-', $hash_rev_lot);
                    $url = $this->generateUrl('drev_lots', array('id' => $this->document->_id, 'prelevement' => $key)).'?error_produit='.$produit_lot_hash;

                    $this->addPoint(self::TYPE_WARNING, 'revendication_cepage_sans_lot', sprintf("%s - %s", $drev->get($hash_cepage)->getParent()->getLibelleComplet(), $drev->get($hash_cepage)->getLibelle()), $url);
                }
            }
        }
    }

    protected function controleEngagementDr() {
        if (!$this->document->isNonRecoltant() && !$this->document->hasDR()) {
            $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_DR, '');
        }
    }

    protected function controleEngagementPressoir($produit) {
        if ($produit->volume_revendique !== null && $produit->getAppellation()->getKey() == 'appellation_CREMANT') {
            $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_PRESSOIR, '');
        }
    }

    protected function controleWarningDrSurface($produit) {
        if (!$this->document->hasDR()) {

            return;
        }

        if (
                $produit->superficie_revendique !== null &&
                $produit->detail->superficie_total !== null &&
                $produit->superficie_revendique != $produit->detail->superficie_total
        ) {
            $appellation_hash = str_replace('/', '-', $produit->getHash()) . '-surface';
            $this->addPoint(self::TYPE_WARNING, 'dr_surface', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document, 'appellation' => $appellation_hash)));
        }
    }

    protected function controleWarningDrVolume($produit) {

        if (!$this->document->hasDR()) {

            return;
        }

        if (
                $produit->volume_revendique !== null &&
                $produit->detail->volume_sur_place_revendique !== null &&
                $produit->volume_revendique != $produit->detail->volume_sur_place_revendique
        ) {
            $appellation_hash = str_replace('/', '-', $produit->getHash()) . '-volume';
            $this->addPoint(self::TYPE_WARNING, 'dr_volume', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document, 'appellation' => $appellation_hash)));
        }
    }

    protected function controleWarningRevendicationLot() {
        foreach ($this->document->declaration->getProduitsCepage() as $hash => $produitCepage) {
            if ($produitCepage->volume_revendique) {
                $correspondance = $this->document->getConfiguration()->get($produitCepage->getCepage()->getHash())->getHashRelation('lots');
                $correspondanceLot = str_replace('/', '_', $correspondance);
                $cuve = Drev::CUVE . $this->document->getPrelevementsKeyByHash($correspondance);
                if ($this->document->prelevements->exist($cuve)) {
                    if ($this->document->prelevements->get($cuve)->lots->exist($correspondanceLot)) {
                        if (!$this->document->prelevements->get($cuve)->lots->get($correspondanceLot)->nb_hors_vtsgn) {
                            $this->addPoint(self::TYPE_WARNING, 'declaration_lots', $this->document->prelevements->get($cuve)->libelle_produit . ' ' . $this->document->prelevements->get($cuve)->lots->get($correspondanceLot)->libelle, $this->generateUrl('drev_lots', array('sf_subject' => $this->document->prelevements->get($cuve))));
                        }
                    }
                }
            }
        }
    }

    protected function controleErrorRevendicationIncomplete($produit) {
        if ($this->document->isNonRecoltant()) {

            return;
        }
        if (
                ($produit->superficie_revendique !== null && $produit->volume_revendique === null) ||
                ($produit->superficie_revendique === null && $produit->volume_revendique !== null)
        ) {
            $this->addPoint(self::TYPE_ERROR, 'revendication_incomplete', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        }
    }

    protected function controleEngagementSv() {
        if ($this->document->isNonRecoltant()) {
            $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV, '');
        }
    }

    protected function controleErrorVolumeRevendiqueIncorrect($produit) {
        if (
                $produit->volume_revendique !== null &&
                $produit->detail->volume_sur_place !== null &&
                $produit->detail->usages_industriels_total !== null &&
                ($produit->detail->volume_sur_place - $produit->detail->usages_industriels_total) > $produit->volume_revendique
        ) {
            $appellation_hash = str_replace('/', '-', $produit->getHash()) . '-volume';
            $this->addPoint(self::TYPE_ERROR, 'volume_revendique_usages_inferieur_sur_place', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document, 'appellation' => $appellation_hash)));
        }

        if (
                $produit->volume_revendique !== null &&
                $produit->detail->volume_sur_place !== null &&
                $produit->volume_revendique > $produit->detail->volume_sur_place
        ) {
            $appellation_hash = str_replace('/', '-', $produit->getHash()) . '-volume';
            $this->addPoint(self::TYPE_ERROR, 'volume_revendique_superieur_sur_place', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document, 'appellation' => $appellation_hash)));
        }
    }

    protected function controleErrorPeriodes() {
        if (!$this->document->prelevements->exist(DRev::CUVE_ALSACE) || !$this->document->prelevements->exist(DRev::BOUTEILLE_ALSACE)) {

            return;
        }

        $prelevement = $this->document->prelevements->get(DRev::CUVE_ALSACE);
        $degustation = $this->document->prelevements->get(DRev::BOUTEILLE_ALSACE);

        if ($prelevement->date && $degustation->date && $degustation->date <= $prelevement->date) {
            $this->addPoint(self::TYPE_ERROR, 'periodes_cuves', sprintf("%s - %s", $degustation->libelle, $degustation->libelle_produit), $this->generateUrl('drev_controle_externe', array('sf_subject' => $this->document)) . "?focus=aoc_alsace");
        }
    }

    protected function controleErrorPrelevement($key) {
        if (!$this->document->prelevements->exist($key)) {

            return;
        }

        $prelevement = $this->document->prelevements->get($key);

        if (!$prelevement->date) {
            $this->addPoint(self::TYPE_ERROR, 'prelevement', sprintf("%s - %s", $prelevement->libelle, $prelevement->libelle_produit), $this->generateUrl('drev_degustation_conseil', array('sf_subject' => $this->document)));
        }
    }

    protected function controleErrorRevendicationSansLot($key) {
        if (!$this->document->prelevements->exist($key)) {

            return;
        }

        $prelevement = $this->document->prelevements->get($key);

        if (!$prelevement->hasLots()) {
            $this->addPoint(self::TYPE_ERROR, 'revendication_sans_lot', sprintf("%s - %s", $prelevement->libelle, $prelevement->libelle_produit), $this->generateUrl('drev_lots', $this->document->prelevements->get($key)));
        }
    }

    protected function controleWarningLotVtsgnSansPrelevement() {
        if (!$this->document->addPrelevement(DRev::CUVE_VTSGN) && $this->document->hasLots(true)) {
            $this->addPoint(self::TYPE_WARNING, 'lot_vtsgn_sans_prelevement', '', $this->generateUrl('drev_degustation_conseil', array('sf_subject' => $this->document)));
        }
    }

}
