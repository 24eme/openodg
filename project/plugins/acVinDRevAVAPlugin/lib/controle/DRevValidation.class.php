<?php

class DRevValidation extends DocumentValidation {

    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';
    const TYPE_ENGAGEMENT = 'engagement';

    protected $etablissement = null;

    public function __construct($document, $options = null) {
        $this->etablissement = $document->getEtablissementObject();
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
        $this->addControle(self::TYPE_ERROR, 'declaration_lots_inferieur', 'Vous avez revendiqué des cepages  qui n\'ont pas de lots.');

        $this->addControle(self::TYPE_WARNING, 'revendication_cepage_sans_lot', 'Vous ne déclarez aucun lot pour un cépage que vous avez revendiqué. Si c\'est un lot qui a été replié en assemblage, ne tenez pas compte de ce point de vigilance.');

        $this->addControle(self::TYPE_WARNING, 'lot_sans_cepage_revendique', 'Vous avez déclaré un lot pour un cépage que vous n\'avez pas revendiqué.');

        /*
         * Error
         */
        $this->addControle(self::TYPE_ERROR, 'revendication_incomplete_superficie', 'Vous devez saisir la superficie pour vos produits revendiqués');
        $this->addControle(self::TYPE_ERROR, 'revendication_incomplete_superficie_vinifiee', 'Vous devez saisir la superficie vinifiée pour vos produits revendiqués');
        $this->addControle(self::TYPE_ERROR, 'revendication_incomplete_volume', 'Vous devez saisir le volume pour vos produits revendiqués');

        $this->addControle(self::TYPE_WARNING, 'volume_revendique_usages_inferieur_sur_place', 'Le volume revendiqué ne peut pas être inférieur au volume sur place déduit des usages industriels de votre DR');

        $this->addControle(self::TYPE_WARNING, 'volume_revendique_superieur_sur_place', 'Le volume revendiqué ne peut pas être supérieur au volume sur place de votre DR, à moins que vous ayez utilisé du VCI en complément');

        $this->addControle(self::TYPE_ERROR, 'prelevement', 'Vous devez saisir une semaine de prélèvement');
        $this->addControle(self::TYPE_ERROR, 'revendication_sans_lot', 'Vous avez revendiqué des produits sans spécifier de lots');

        $this->addControle(self::TYPE_ERROR, 'lot_sans_cepage_revendique', 'Vous avez déclaré un lot pour un cépage que vous n\'avez pas revendiqué.');


        $this->addControle(self::TYPE_ERROR, 'controle_externe_vtsgn', 'Vous devez renseigner une semaine et le nombre total de lots pour le VT/SGN');
        $this->addControle(self::TYPE_ERROR, 'periodes_cuves', '13 jours doivent séparer au minimum la semaine de prélèvement du contrôle externe de celle de la dégustation conseil');

        $this->addControle(self::TYPE_ERROR, 'repartition_vci', 'Vous devez répartir la totalité de votre stock VCI');
        $this->addControle(self::TYPE_ERROR, 'vci_rendement_total', "Le stock de vci final dépasse le rendement autorisé : vous devrez impérativement détruire Stock final - Plafond VCI Hls");
        $this->addControle(self::TYPE_ERROR, 'vci_rendement', "Le complément de récolte par du vci dépasse le rendement autorisé");
        $this->addControle(self::TYPE_ERROR, 'vci_complement', "Vous ne complétez pas votre volume malgré votre stock VCI disponible");

        $this->addControle(self::TYPE_ERROR, 'chai_manquant', "Les informations liées à votre chai de vinification sont manquantes");

        /*
         * Engagement
         */
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_DR, 'Joindre une copie de votre Déclaration de Récolte');
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV11, 'Joindre une copie de votre SV11');
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV12, 'Joindre une copie de votre SV12');
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV, 'Joindre une copie de votre SV11 ou SV12');
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_PRESSOIR, 'Une <strong>copie</strong> du Carnet de Pressoir');
    }

    public function controle() {
        $revendicationProduits = $this->document->declaration->getProduits();
        if(!$this->document->isNonRecoltant()) {
            $this->document->populateVCIFromRegistre();
        }
        foreach ($revendicationProduits as $hash => $revendicationProduit) {
            $this->controleWarningDrSurface($revendicationProduit);
            $this->controleErrorVolumeRevendiqueIncorrect($revendicationProduit);
            $this->controleWarningDrVolume($revendicationProduit);
            $this->controleErrorRevendicationIncomplete($revendicationProduit);
            if($revendicationProduit->exist('superficie_revendique_vtsgn')) {
                $this->controleErrorRevendicationIncomplete($revendicationProduit, '_vtsgn', " VTSGN");
            }
            $this->controleEngagementPressoir($revendicationProduit);

            $stockVCIFinal = 0;
            foreach ($revendicationProduit->getProduitsVCI() as $produitVCI) {
                $stockVCIFinal += $produitVCI->getStockFinalCalcule();
            }

            if ($stockVCIFinal != 0) {
                $this->addPoint(self::TYPE_ERROR, 'repartition_vci', sprintf("%s", $revendicationProduit->getLibelleComplet()), $this->generateUrl('drev_revendication_vci', array('sf_subject' => $this->document)));
            }
        }

        $this->controleWarningRevendicationLot();
        $this->controleErrorPrelevement(DRev::CUVE_ALSACE);
        if(!$this->document->isNonConditionneur()) {
            $this->controleErrorPrelevement(DRev::BOUTEILLE_ALSACE);
            $this->controleErrorPrelevement(DRev::BOUTEILLE_GRDCRU);
        }

        $this->controleErrorPeriodes();

        if ($this->document->mustDeclareCepage()) {
            $this->controleWarningCepageSansLot(DRev::CUVE_ALSACE);
            $this->controleWarningCepageSansLot(DRev::CUVE_GRDCRU);
            $this->controleErrorAndWarningLotSansCepage(DRev::CUVE_ALSACE);
            $this->controleErrorAndWarningLotSansCepage(DRev::CUVE_GRDCRU);
        }

        $this->controleErrorRevendicationSansLot(DRev::CUVE_ALSACE);
        $this->controleErrorRevendicationSansLot(DRev::CUVE_GRDCRU);

        $this->controleErrorChaiManquant();

        $this->controleEngagementDr();
        $this->controleEngagementSv();
    }

    public function controleErrorAndWarningLotSansCepage($key) {
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

                    $produit_lot_hash = self::TYPE_WARNING.'withFlash' . str_replace('/', '-', $lot->hash_produit);
                    $url = $this->generateUrl('drev_lots', array('id' => $this->document->_id, 'prelevement' => $key)).'?error_produit='.$produit_lot_hash;
                    $this->addPoint(self::TYPE_WARNING, 'lot_sans_cepage_revendique', $text, $url);
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
        if($this->document->isPapier()) {

            return;
        }

        if (!$this->document->isNonRecoltant() && !$this->document->hasDR()) {
            $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_DR, '');
        }
    }

    protected function controleEngagementPressoir($produit) {
        if($this->document->isPapier()) {

            return;
        }

        if ($produit->getAppellation()->getKey() != 'appellation_CREMANT') {

            return;
        }

        if($produit->exist('cepage_RB') && $produit->get('cepage_RB')->getDetailNode() && $produit->get('cepage_RB')->getDetailNode()->volume_revendique_total > 0) {
            $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_PRESSOIR, '');

            return;
        }

        if ($produit->volume_revendique > 0) {

            $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_PRESSOIR, '');

            return;
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
            $this->addPoint(self::TYPE_WARNING, 'dr_surface', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication_superficies', array('sf_subject' => $this->document, 'appellation' => $appellation_hash)));
        }

        if (
                $produit->canHaveVtsgn() &&
                $produit->superficie_revendique_vtsgn !== null &&
                $produit->detail_vtsgn->superficie_total !== null &&
                $produit->superficie_revendique_vtsgn != $produit->detail_vtsgn->superficie_total
        ) {
            $appellation_hash = str_replace('/', '-', $produit->getHash()) . '-surface';
            $this->addPoint(self::TYPE_WARNING, 'dr_surface', $produit->getLibelleComplet()." VT/SGN", $this->generateUrl('drev_revendication_superficies', array('sf_subject' => $this->document, 'appellation' => $appellation_hash)));
        }
    }

    protected function controleWarningDrVolume($produit) {
        $alreadyWarned = false;

        if (count($this->getVigilances())>0) {
          if ($this->getVigilances()[0]->getCode() == 'volume_revendique_superieur_sur_place'){ $alreadyWarned = true; }
        }

        if (!$this->document->hasDR() || $alreadyWarned == true) {

            return;
        }

        if (
                $produit->volume_revendique !== null &&
                $produit->detail->volume_sur_place_revendique !== null &&
                $produit->volume_revendique != $produit->detail->volume_sur_place_revendique
        ) {
            $appellation_hash = str_replace('/', '-', $produit->getHash()) . '-volume';
            $this->addPoint(self::TYPE_WARNING, 'dr_volume', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication_volumes', array('sf_subject' => $this->document, 'appellation' => $appellation_hash)));
        }

        if (
                $produit->canHaveVtsgn() &&
                $produit->volume_revendique_vtsgn !== null &&
                $produit->detail_vtsgn->volume_sur_place_revendique !== null &&
                $produit->volume_revendique_vtsgn != $produit->detail_vtsgn->volume_sur_place_revendique
        ) {
            $appellation_hash = str_replace('/', '-', $produit->getHash()) . '-volume';
            $this->addPoint(self::TYPE_WARNING, 'dr_volume', $produit->getLibelleComplet()." VT/SGN", $this->generateUrl('drev_revendication_volumes', array('sf_subject' => $this->document, 'appellation' => $appellation_hash)));
        }
    }

    protected function controleWarningRevendicationLot() {
      $nb_total_lots_cepages = [];
        foreach ($this->document->declaration->getProduitsCepage() as $hash => $produitCepage) {

            if ($produitCepage->volume_revendique) {
                $correspondance = $this->document->getConfiguration()->get($produitCepage->getCepage()->getHash())->getHashRelation('lots');
                $correspondanceLot = str_replace('/', '_', $correspondance);
                $cuve = Drev::CUVE . $this->document->getPrelevementsKeyByHash($correspondance);
                if ($this->document->prelevements->exist($cuve)) {
                  if ($this->document->prelevements->get($cuve)->lots->exist($correspondanceLot)) {
                    $nb_total_lots_cepages[$cuve] =$this->document->prelevements->get($cuve)->total_lots;
                    if (!$this->document->prelevements->get($cuve)->lots->get($correspondanceLot)->nb_hors_vtsgn) {
                      $this->addPoint(self::TYPE_WARNING, 'declaration_lots', $this->document->prelevements->get($cuve)->libelle_produit . ' ' . $this->document->prelevements->get($cuve)->lots->get($correspondanceLot)->libelle, $this->generateUrl('drev_lots', array('sf_subject' => $this->document->prelevements->get($cuve))));
                    }
                  }
                }
            }
        }
        foreach ($nb_total_lots_cepages as $key => $value) {
          if($value < $this->document->prelevements->get($key)->getNbLotsMinimum()) {
            $this->addPoint(self::TYPE_ERROR, 'declaration_lots_inferieur', "Dégustation conseil - ".$this->document->prelevements->get($key)->libelle_produit, $this->generateUrl('drev_lots', $this->document->prelevements->get($key)));
            break;
          }
        }

    }

    protected function controleErrorRevendicationIncomplete($produit, $suffix = null, $suffixLibelle = null) {
        if ($this->document->isNonRecoltant()) {

            return;
        }

        if ($produit->get('superficie_revendique'.$suffix) === null && $produit->get('volume_revendique'.$suffix) > 0) {
            $this->addPoint(self::TYPE_ERROR, 'revendication_incomplete_superficie', $produit->getLibelleComplet().$suffixLibelle, $this->generateUrl('drev_revendication_superficies', array('sf_subject' => $this->document)));
        }

        if ($produit->get('superficie_revendique'.$suffix) !== null && $produit->get('superficie_vinifiee'.$suffix) === null) {
            $this->addPoint(self::TYPE_ERROR, 'revendication_incomplete_superficie_vinifiee', $produit->getLibelleComplet().$suffixLibelle, $this->generateUrl('drev_revendication_superficies', array('sf_subject' => $this->document)));
        }

        if ($produit->get('superficie_revendique'.$suffix) !== null && $produit->get('volume_revendique'.$suffix) === null) {
            $this->addPoint(self::TYPE_ERROR, 'revendication_incomplete_volume', $produit->getLibelleComplet().$suffixLibelle, $this->generateUrl('drev_revendication_volumes', array('sf_subject' => $this->document)));
        }
    }

    protected function controleEngagementSv() {
        if($this->document->isPapier()) {

            return;
        }

        if (!$this->document->isNonRecoltant()) {

            return;
        }

        if ($this->etablissement->hasFamille(EtablissementClient::FAMILLE_CAVE_COOPERATIVE)) {
            $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV11, '');

            return;
        }

        if ($this->etablissement->hasFamille(EtablissementClient::FAMILLE_NEGOCIANT)) {
            $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV12, '');

            return;
        }

        $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV, '');
    }

    protected function controleErrorVolumeRevendiqueIncorrect($produit) {


        if (
                $produit->volume_revendique !== null &&
                $produit->detail->volume_sur_place !== null &&
                $produit->detail->usages_industriels_total !== null &&
                round($produit->detail->volume_sur_place - $produit->detail->usages_industriels_total - $produit->detail->vci_total, 2) > $produit->volume_revendique
        ) {
	    $appellation_hash = str_replace('/', '-', $produit->getHash()) . '-volume';
            $this->addPoint(self::TYPE_WARNING, 'volume_revendique_usages_inferieur_sur_place', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document, 'appellation' => $appellation_hash)));
        }

        if (
                $produit->volume_revendique !== null &&
                $produit->detail->volume_sur_place !== null &&
                $produit->volume_revendique > $produit->detail->volume_sur_place
        ) {
            $appellation_hash = str_replace('/', '-', $produit->getHash()) . '-volume';
            $this->addPoint(self::TYPE_WARNING, 'volume_revendique_superieur_sur_place', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document, 'appellation' => $appellation_hash)));
        }

    }

    protected function controleErrorPeriodes() {
        if (!$this->document->prelevements->exist(DRev::CUVE_ALSACE) || !$this->document->prelevements->exist(DRev::BOUTEILLE_ALSACE)) {

            return;
        }

        $prelevement = $this->document->prelevements->get(DRev::CUVE_ALSACE);
        $degustation = $this->document->prelevements->get(DRev::BOUTEILLE_ALSACE);

        $dateDegustationExterneMinimum = new DateTime($prelevement->date);
        $dateDegustationExterneMinimum->modify('+ 13 day');

        if ($prelevement->date && $degustation->date && $degustation->date < $dateDegustationExterneMinimum->format('Y-m-d')) {
            $this->addPoint(self::TYPE_ERROR, 'periodes_cuves', sprintf("%s - %s", $degustation->libelle, $degustation->libelle_produit), $this->generateUrl('drev_controle_externe', array('sf_subject' => $this->document)) . "?focus=aoc_alsace");
        }
    }

    protected function controleErrorPrelevement($key) {
        if (!$this->document->prelevements->exist($key)) {

            return;
        }

        $prelevement = $this->document->prelevements->get($key);

        if (!$prelevement->date && preg_match("/".DRev::CUVE."/", $key)) {
            $this->addPoint(self::TYPE_ERROR, 'prelevement', sprintf("%s - %s", $prelevement->libelle, $prelevement->libelle_produit), $this->generateUrl('drev_degustation_conseil', array('sf_subject' => $this->document)));
        }

        if (!$prelevement->date && preg_match("/".DRev::BOUTEILLE."/", $key)) {
            $this->addPoint(self::TYPE_ERROR, 'prelevement', sprintf("%s - %s", $prelevement->libelle, $prelevement->libelle_produit), $this->generateUrl('drev_controle_externe', array('sf_subject' => $this->document)));
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

    protected function controleErrorChaiManquant() {
        $hasChai = false;
        foreach ($this->document->chais as $values) {
          if ($values->adresse||$values->commune||$values->code_postal) {
            $hasChai = true;
            break;
          }
        }
        if (!$hasChai && (!$this->document->exist('non_vinificateur') || !$this->document->non_vinificateur)) {
            $this->addPoint(self::TYPE_ERROR, 'chai_manquant', "Exploitation", $this->generateUrl('drev_exploitation', array('sf_subject' => $this->document)));
        }
    }

}
