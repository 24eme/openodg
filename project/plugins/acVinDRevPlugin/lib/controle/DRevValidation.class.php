<?php
class DRevValidation extends DeclarationLotsValidation
{
    protected $etablissement = null;
    protected $produit_revendication_rendement = array();
    protected $vip2c = null;

    public function __construct($document, $options = null)
    {
        $this->etablissement = $document->getEtablissementObject();
        $this->vip2c = VIP2C::gatherInformations($document, $document->getPeriode());
        parent::__construct($document, $options);
        $this->noticeVigilance = true;
    }

    public function configure()
    {
        /*
         * Warning
         */
        $this->addControle(self::TYPE_ERROR, 'declaration_multi_cvi', "est lié à plusieurs opérateurs");
        $this->addControle(self::TYPE_WARNING, 'declaration_volume_l15', 'Vous ne revendiquez pas le même volume que celui qui figure sur votre déclaration douanière en L15 (peut-être dû au complèment de VCI ou un achat)');
        $this->addControle(self::TYPE_WARNING, 'declaration_neant', "Vous n'avez déclaré aucun produit");
        $this->addControle(self::TYPE_WARNING, 'declaration_produits_incoherence', "Vous ne déclarez pas tous les produits de votre déclaration douanière");
        $this->addControle(self::TYPE_WARNING, 'declaration_surface_bailleur', "Vous n'avez pas reparti votre part de surface avec le bailleur");
        $this->addControle(self::TYPE_WARNING, 'vci_complement', "Vous ne complétez pas tout votre volume malgré votre stock VCI disponible");
        $this->addControle(self::TYPE_WARNING, 'declaration_volume_l15_dr_zero', "Le volume récolté de la DR est absent ou à zéro");
        $this->addControle(self::TYPE_WARNING, 'declaration_elevage', "Vous avez déclaré un lot en élevage. Vous devez prendre contact avec votre ODG lorsque vous souhaiterez le commercialiser.");

        /*
         * Error
         */
        $this->addControle(self::TYPE_ERROR, 'revendication_incomplete_volume', "Le volume revendiqué n'a pas été saisi");
        $this->addControle(self::TYPE_WARNING, 'revendication_incomplete_volume_warn', "Le volume revendiqué n'a pas été saisi");
        $this->addControle(self::TYPE_WARNING, 'revendication_apporteur_total_warn', "Aucun volume de récolte en cave particulière a été détecté");
        $this->addControle(self::TYPE_ERROR, 'revendication_incomplete_superficie', "La superficie revendiquée n'a pas été saisie");
        $this->addControle(self::TYPE_ERROR, 'revendication_rendement', "Le rendement sur le volume revendiqué n'est pas respecté");
        $this->addControle(self::TYPE_WARNING, 'revendication_rendement_warn', "Le rendement sur le volume revendiqué n'est pas respecté (peut être lié à un achat de vendange ou l'intégration de VCI stocké chez un négociant)");
        $this->addControle(self::TYPE_WARNING, 'revendication_rendement_conseille', "Le rendement sur le volume revendiqué dépasse le rendement légal il vous faut disposer d'une dérogation pour être autorisé à revendiquer ce rendement");
        $this->addControle(self::TYPE_ERROR, 'vci_stock_utilise', "Le stock de vci n'a pas été correctement reparti");
        $this->addControle(self::TYPE_ERROR, 'vci_rendement', "Le volume revendiqué en VCI dépasse le rendement autorisé");
        $this->addControle(self::TYPE_WARNING, 'vci_rendement_total', "Le stock de vci final dépasse le rendement autorisé : vous devrez impérativement détruire Stock final - Plafond VCI Hls");
        $this->addControle(self::TYPE_ERROR, 'vsi_rendement', "Le volume revendiqué en VSI dépasse le rendement autorisé");
        $this->addControle(self::TYPE_ERROR, 'declaration_volume_l15_complement', 'Vous revendiquez un volume supérieur à celui qui figure sur votre déclaration douanière en L15');
        $this->addControle(self::TYPE_ERROR, 'declaration_volume_l15_dr', 'Certaines informations provenant de votre déclaration douanière sont manquantes');

        $this->addControle(self::TYPE_ERROR, 'vci_substitue_rafraichi', 'Vous ne pouvez ni subsituer ni rafraichir un volume de VCI supérieur à celui qui figure sur votre déclaration douanière en L15');

        $this->addControle(self::TYPE_ERROR, 'revendication_superficie_dr', 'Les données de superficie provenant de votre déclaration douanière sont manquantes');
        $this->addControle(self::TYPE_ERROR, 'revendication_superficie', 'Vous revendiquez une superficie supérieure à celle qui figure sur votre déclaration douanière en L4');
        $this->addControle(self::TYPE_WARNING, 'revendication_superficie_warn', 'Vous revendiquez une superficie supérieure à celle qui figure sur votre déclaration douanière en L4');

        $this->addControle(self::TYPE_WARNING, 'parcellaire_affectation_superficie_sup', 'La superficie revendiquée est supérieure à celle qui figure sur votre affectation parcellaire');

        $this->addControle(self::TYPE_WARNING, 'dr_recolte_rendement', "Vous dépassez le rendement dans votre DR (L15)");
        $this->addControle(self::TYPE_WARNING, 'sv12_recolte_rendement', "Vous dépassez le rendement dans votre SV12");
        $this->addControle(self::TYPE_WARNING, 'sv11_recolte_rendement', "Vous dépassez le rendement dans votre SV11");

        $this->addControle(self::TYPE_WARNING, 'drev_habilitation_inao', "Vous ne semblez pas habilité pour ce produit");

        $this->addControle(self::TYPE_ERROR, 'drev_habilitation_odg', "Vous n'êtes pas habilité en vinification pour ce produit");

        $this->addControle(self::TYPE_WARNING, 'bailleurs', "Des bailleurs ne sont pas connus");

        $this->addControle(self::TYPE_ERROR, 'mutage_ratio', "Le volume d'alcool de mutage ajouté n'est pas compris entre 5 et 10% du volume récolté");

        $this->addControle(self::TYPE_ERROR, 'dr_vci_vsi', "Du VCI et VSI ont été déclarés pour le même produit dans la déclaration de récolte");
        $this->addControle(self::TYPE_ERROR, 'declaration_lot_millesime_inf_n_1', "Le lot révendiqué est anterieur au millésime ".($this->document->periode-1));

        /*
         * Engagement
         */
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV11, DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_SV11));
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV12, DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_SV12));
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VCI, DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_VCI));
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_MUTAGE_DECLARATION, DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_MUTAGE_DECLARATION));

        $produits_manquants = $this->getManquants();
        foreach($produits_manquants as $pourcentage => $produits) {
            foreach($produits as $libelle => $p) {
                $this->addControle(self::TYPE_ENGAGEMENT, str_replace('MANQUANTES_', 'MANQUANTES'.str_replace('/', "_", $p->getConfig()->getAppellation()->getHash().'_'), constant("DRevDocuments::DOC_PARCELLES_MANQUANTES_".$pourcentage."_OUEX_INF")), DRevDocuments::getEngagementLibelle(constant("DRevDocuments::DOC_PARCELLES_MANQUANTES_".$pourcentage."_OUEX_INF")) );
                $this->addControle(self::TYPE_ENGAGEMENT, str_replace('MANQUANTES_', 'MANQUANTES'.str_replace('/', "_", $p->getConfig()->getAppellation()->getHash().'_'), constant("DRevDocuments::DOC_PARCELLES_MANQUANTES_".$pourcentage."_OUEX_SUP")), DRevDocuments::getEngagementLibelle(constant("DRevDocuments::DOC_PARCELLES_MANQUANTES_".$pourcentage."_OUEX_SUP")) );
            }
        }
        $this->addControle(self::TYPE_ERROR, 'MANQUANTES_declaration_missing', "Déclaration de pieds morts manquante");


        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_DEPASSEMENT_CONSEIL, DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_DEPASSEMENT_CONSEIL));
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_ELEVAGE_CONTACT_SYNDICAT, DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_ELEVAGE_CONTACT_SYNDICAT));
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_CONTRAT_VENTE_EN_VRAC, DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_VIP2C_OU_CONTRAT_VENTE_EN_VRAC));
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_CONDITIONNEMENT,DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_VIP2C_OU_CONDITIONNEMENT));

        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VSI_DESTRUCTION, DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_VSI_DESTRUCTION));

        foreach ($this->document->getProduitsWithoutLots() as $produit_hash => $produit) {
            if (VIP2C::hasVolumeSeuil() && $this->document->hasDestinationVrac([$produit_hash])) {
                $contrats = VIP2C::getContratsFromAPI($this->document->declarant->cvi, $this->document->campagne, $produit->getConfig()->getHash());
                foreach($contrats as $k=>$v){
                    $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_CONTRAT_VENTE_EN_VRAC."_".$k,DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_VIP2C_OU_CONTRAT_VENTE_EN_VRAC).'<strong>'.$v['numero']."</strong> avec un volume proposé de <strong>".$v['volume']." hl</strong>.");
                }
            }
        }
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_PAS_INFORMATION, "<strong>Je n'ai pas l'information</strong>");

        /* Lots */

        $this->configureLots();
        $this->addControle(self::TYPE_ERROR, 'lot_igp_inexistant_dans_dr_err', "Ce lot IGP est inexistant dans la DR.");
        $this->addControle(self::TYPE_WARNING, 'lot_igp_inexistant_dans_dr_warn', "Ce lot IGP est inexistant dans la DR.");
        $this->addControle(self::TYPE_ERROR, 'lot_volume_total_depasse', 'Les volumes revendiqués de vos lots sont supérieurs aux volumes revendicables déclarés dans votre DR, SV11 ou SV12');
        $this->addControle(self::TYPE_WARNING, 'lot_volume_total_depasse_warn', 'Les volumes revendiqués de vos lots sont supérieurs aux volumes revendicables déclarés dans votre DR, SV11 ou SV12');

        if(array_key_exists('produits', $this->vip2c)){
            foreach($this->vip2c['produits'] as $produit) {
                $this->addControle(self::TYPE_ERROR, 'vip2c_pas_de_contrats_'.$produit['hash_regex'],"Depuis le millésime ".VIP2C::getConfigCampagneVolumeSeuil().", la filière a mis en place le Volume Individuel de Production Commercialisable Certifiée (VIP2C). Vous avez dépassé les  ".$produit['volume_max']." hl qui vous ont été attribués. Pour pouvoir revendiquer ces lots, vous devez apporter une preuve de leur commercialisation or Declarvins nous informe que vous n'avez pas de contrat de vrac non soldé. Veuillez prendre contact avec Intervins Sud Est - 04 90 42 90 04.");
                $this->addControle(self::TYPE_WARNING, 'declaration_superieur_volume_commerciable_'.$produit['hash_regex'],"À partir de la campagne ".VIP2C::getConfigCampagneVolumeSeuil().", la filière a mis en place le Volume Individuel de Production Commercialisable Certifiée (VIP2C). Vous êtes sur le point de dépasser les ".$produit['volume_max']." hl qui vous ont été attribués. Au delà, vous devrez avoir une preuve de commercialisation pour pouvoir revendiquer vos volumes.");
                $this->addControle(self::TYPE_WARNING, 'declaration_superieur_volume_autorise_'.$produit['hash_regex'],"À partir de la campagne ".VIP2C::getConfigCampagneVolumeSeuil().", la filière a mis en place le Volume Individuel de Production Commercialisable Certifiée (VIP2C). Vous avez dépassé les  ".$produit['volume_max']." hl qui vous ont été attribués. Pour pouvoir revendiquer ces lots, vous devez apporter une preuve de leur commercialisation.");

                $lots = [];
                foreach ($produit['hashes'] as $produit_hash) {
                    if ($this->document->exist($produit_hash)) {
                        foreach ($this->document->get($produit_hash) as $lot) {
                            $lots[] = $lot->libelle;
                        }
                    }
                }
                $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_CONDITIONNEMENT.'_'.$produit['hash_regex'], "<strong>J'atteste de conditionnements,</strong> en revendiquant au-delà de mon Volume Individuel de Production Commercialisable Certifiée (VIP2C), je m'engage à fournir à Intervins Sud Est <strong>une copie du registre de conditionnement</strong> pour les lots <strong>".implode(', ', $lots)."</strong> en dépassement sur cette revendication.");

                if(array_key_exists('contrats', $produit)){
                    foreach($produit['contrats'] as $k=>$v){
                        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_CONTRAT_VENTE_EN_VRAC."_".$k,DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_VIP2C_OU_CONTRAT_VENTE_EN_VRAC).'<strong>'.$v['numero']."</strong> avec un volume proposé de <strong>".$v['volume']." hl</strong>.");
                    }
                }
            }
        }
    }

    public function controle()
    {
        $produits = array();
        foreach ($this->document->getProduitsWithoutLots() as $hash => $produit) {
          $this->controleRevendication($produit);
          $this->controleVci($produit);
        }
        $this->controleRecoltes();

        foreach ($this->document->getProduits() as $hash => $produit) {
          $produits[$produit->getParent()->getHash()] = $produit;
        }
        $this->controleNeant();

        if(array_key_exists('produits', $this->vip2c)){
            foreach($this->vip2c['produits'] as $produit) {
                $this->controleVolumeSeuilDeclare($produit);
            }
        }

        $this->controleEngagementVCI();
        $this->controleEngagementSv();
        $this->controleEngagementMutage();
        $this->controleEngagementParcelleManquante();
        $this->controleProduitsDocumentDouanier($produits);
        $this->controleHabilitationINAO();
        $this->controleHabilitationODG();
        $this->controleBailleurs();
        $this->controleLots();
        $this->controleVsi();
    }

    protected function controleNeant()
    {
        $superficie = 0;
        $volume = 0;
        foreach($this->document->getProduits() as $p) {
            $volume += $p->volume_revendique_total;
            if ($volume) {
                return;
            }
            $superficie += $p->superficie_revendique;
        }
        if($this->document->exist('lots') && count($this->document->lots) && (!$this->document->hasDocumentDouanier() || $superficie)) {
    		return;
    	}
    	$this->addPoint(self::TYPE_WARNING, 'declaration_neant', '', $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
    }

    protected function controleProduitsDocumentDouanier($produits)
    {
        $produits_hash = [];
        foreach(array_keys($produits) as $h) {
            $h = str_replace(['/EFF/', '/MOU/'], '/VDB/', $h);
            $produits_hash[] = $h;
        }
    	$drev = $this->document->getFictiveFromDocumentDouanier();
    	$hasDiff = false;
    	foreach ($drev->getProduits() as $hash_prod => $produit) {
            $hash = $produit->getParent()->getHash();
            $hash = str_replace(['/EFF/', '/MOU/'], '/VDB/', $hash);
            if (!in_array($hash, $produits_hash)) {
    			$hasDiff = true;
    		}
    	}
    	if ($hasDiff) {
    		$this->addPoint(self::TYPE_WARNING, 'declaration_produits_incoherence', '', $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
    	}
    }

    protected function controleEngagementVCI()
    {
        if($this->document->isPapier()) {
            return;
        }
        if (!$this->document->hasVciDetruit()) {
        	return;
        }
        $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VCI, '');
    }

    protected function controleEngagementMutage()
    {
        if($this->document->isPapier()) {
            return;
        }
        if(!$this->document->declaration->getTotalVolumeRevendiqueMutage()) {
            return;
        }
        $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_MUTAGE_DECLARATION, '');
    }

    private function getManquants() {
        $produits_manquants = array();
        foreach($this->document->getProduits() as $produit) {
            $pourcentage = $produit->getConfig()->getAttribut('engagement_parcelles_manquantes');
            if(!$pourcentage) {
                continue;
            }
            if (!constant("DRevDocuments::DOC_PARCELLES_MANQUANTES_".$pourcentage."_OUEX_INF") || !constant("DRevDocuments::DOC_PARCELLES_MANQUANTES_".$pourcentage."_OUEX_SUP")) {
                throw new sfException("engagement_parcelles_manquantes $pourcentage pour ".$produit->getLibelle()." (".$produit->getConfig()->getDocument()->_id.") ne correspond pas à une constante connue (DRevDocuments::DOC_PARCELLES_MANQUANTES_".$pourcentage."_OUEX_INF DRevDocuments::DOC_PARCELLES_MANQUANTES_".$pourcentage."_OUEX_SUP)");
            }
            @$produits_manquants[$pourcentage][$produit->getConfig()->getAppellation()->getLibelleComplet()] = $produit;
        }
        return $produits_manquants;
    }

    protected function controleEngagementParcelleManquante()
    {
        if($this->document->isPapier()) {
            return;
        }

        $produits_manquants = $this->getManquants();
        if(!count($produits_manquants)) {
            return;
        }
        foreach($produits_manquants as $pourcentage => $produits) {
            foreach($produits as $libelle => $p) {
                $this->addPoint(self::TYPE_ENGAGEMENT, str_replace('MANQUANTES_', 'MANQUANTES'.str_replace('/', "_", $p->getConfig()->getAppellation()->getHash().'_'), constant("DRevDocuments::DOC_PARCELLES_MANQUANTES_".$pourcentage."_OUEX_INF")), $libelle);
                $this->addPoint(self::TYPE_ENGAGEMENT, str_replace('MANQUANTES_', 'MANQUANTES'.str_replace('/', "_", $p->getConfig()->getAppellation()->getHash().'_'), constant("DRevDocuments::DOC_PARCELLES_MANQUANTES_".$pourcentage."_OUEX_SUP")), $libelle);
            }
        }
        if (ParcellaireConfiguration::getInstance()->isManquantMandatory() && $this->document->exist('documents')) {
            $manquant_needed = false;
            foreach($this->document->documents as $key => $eng) {
                if (strpos($key, '_SUP')) {
                    $manquant_needed = true;
                    break;
                }
            }
            if ($manquant_needed) {
                $m = ParcellaireManquantClient::getInstance()->find(str_replace('DREV-', 'PARCELLAIREMANQUANT-', $this->document->_id));
                if (!$m || !$m->validation) {
                    $this->addPoint(self::TYPE_ERROR, 'MANQUANTES_declaration_missing', $this->document->campagne);
                }
            }
        }
    }
    protected function controleEngagementSv()
    {
        if($this->document->isPapier()) {
            return;
        }
        if ($this->document->hasDocumentDouanier()) {
        	return;
        }
        if ($this->document->getDocumentDouanierType() == SV11CsvFile::CSV_TYPE_SV11) {
            $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV11, '');
        }
        if ($this->document->getDocumentDouanierType() == SV12CsvFile::CSV_TYPE_SV12) {
            $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV12, '');
        }
    }

    protected function controleRecoltes()
    {
        foreach($this->document->getProduits() as $produit) {
            if($produit->getConfig()->getRendementDrL15() && ($produit->getCepage()->getRendementDrL15() > $produit->getConfig()->getRendementDrL15()) ) {
                if(!array_key_exists($produit->getCepage()->gethash(),$this->produit_revendication_rendement)){
                  $type_msg = strtolower($this->document->getDocumentDouanierType()).'_recolte_rendement';
                  $this->addPoint(self::TYPE_WARNING,$type_msg , $produit->getCepage()->getLibelleComplet(), $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
                }
            }
        }
    }

    protected function controleRevendication($produit)
    {
        if ($produit->isCleanable()) {
          return;
        }
        $has_point_dr = false;
        if ($this->document->getDocumentDouanierType() != DRCsvFile::CSV_TYPE_DR && !$produit->getSommeProduitsCepage('recolte/volume_sur_place')) {
            $this->addPoint(self::TYPE_ERROR, 'declaration_volume_l15_dr', $produit->getCepage()->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
            $has_point_dr = true;
        } elseif ($this->document->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR && !$produit->getSommeProduitsCepage('recolte/recolte_nette')) {
            if (strpos($produit->getHash(), '/MOU/') === false) {
                $this->addPoint(self::TYPE_WARNING, 'declaration_volume_l15_dr_zero', $produit->getCepage()->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
            }
            $has_point_dr = true;
        } else {

            if ((round($produit->getSommeProduitsCepage('volume_revendique_issu_recolte') + $produit->getSommeProduitsCepage('vci/rafraichi'), 4)) != round($produit->getSommeProduitsCepage('recolte/recolte_nette'), 4) && round($produit->getSommeProduitsCepage('recolte/volume_total'), 4) == round($produit->getSommeProduitsCepage('recolte/volume_sur_place'), 4)) {
                $this->addPoint(self::TYPE_WARNING, 'declaration_volume_l15', $produit->getCepage()->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
            }
            if (round($produit->getVolumeRevendiqueRendement(), 4) > round($produit->getSommeProduitsCepage('recolte/recolte_nette') + $produit->getSommeProduitsCepage('vci/complement') + $produit->getSommeProduitsCepage('recolte/vsi') , 4) && (!$this->document->exist('achat_tolerance') || !$this->document->achat_tolerance)) {
                $this->addPoint(self::TYPE_ERROR, 'declaration_volume_l15_complement', $produit->getCepage()->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
            }
            if ($produit->recolte->recolte_nette && ($produit->recolte->recolte_nette + $produit->vci->complement) < ($produit->vci->substitution + $produit->vci->rafraichi)) {
                $this->addPoint(self::TYPE_ERROR, 'vci_substitue_rafraichi', $produit->getLibelleComplet(), $this->generateUrl('drev_vci', array('sf_subject' => $this->document)));
            }
        }
        if (!$has_point_dr) {
            if ($produit->recolte && !$produit->recolte->volume_sur_place) {
                $this->addPoint(self::TYPE_WARNING, 'revendication_apporteur_total_warn', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
            }elseif($produit->getSommeProduitsCepage('superficie_revendique') === null) {
                $this->addPoint(self::TYPE_ERROR, 'revendication_incomplete_superficie', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
            }
            if(!$produit->getSommeProduitsCepage('volume_revendique_issu_recolte')) {
                if ($produit->getCepage()->hasDonneesRecolte()) {
                    $this->addPoint(self::TYPE_ERROR, 'revendication_incomplete_volume', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
                } else {
                    $this->addPoint(self::TYPE_WARNING, 'revendication_incomplete_volume_warn', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
                }
            }
        }
        if ($produit->superficie_revendique > 0 && $produit->volume_revendique_issu_recolte > 0) {

	        if($produit->getConfig()->getRendement() !== null && round(($produit->getCepage()->getRendementEffectif()), 2) > round($produit->getConfig()->getRendement(), 2)) {
                if ($produit->getDocument()->exist('achat_tolerance') && $produit->getDocument()->get('achat_tolerance')) {
                    $this->addPoint(self::TYPE_WARNING, 'revendication_rendement_warn', $produit->getCepage()->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
                }else{
                    if ((round(($produit->getCepage()->getRendementEffectifHorsVCI()), 2) <= round($produit->getConfig()->getRendement(), 2))) {
                        $this->addPoint(self::TYPE_WARNING, 'revendication_rendement_warn', $produit->getCepage()->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
                    }else{
                        $this->addPoint(self::TYPE_ERROR, 'revendication_rendement', $produit->getCepage()->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
                    }
                }
                $this->produit_revendication_rendement[$produit->getHash()] = $produit->getHash();
            } elseif($produit->getConfig()->getRendementConseille() > 0 && round(($produit->getRendementEffectif()), 2) > round($produit->getConfig()->getRendementConseille(), 2)) {
                $this->addPoint(self::TYPE_WARNING, 'revendication_rendement_conseille', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
                if(!$this->document->isPapier()) {
                    $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_DEPASSEMENT_CONSEIL, $produit->getLibelleComplet());
                }
            }
        }
        if ( (!$produit->getSommeProduitsCepage('recolte/superficie_total') && $produit->getSommeProduitsCepage('superficie_revendique') > 0) || (round($produit->getSommeProduitsCepage('superficie_revendique'), 4) > round($produit->getSommeProduitsCepage('recolte/superficie_total'), 4)) ) {
            if ($this->document->getDocumentDouanierType() == SV12CsvFile::CSV_TYPE_SV12) {
                $this->addPoint(self::TYPE_WARNING, 'revendication_superficie_warn', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
            }else{
        	    $this->addPoint(self::TYPE_ERROR, 'revendication_superficie', $produit->getCepage()->getLibelleComplet(), $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
            }
        }
        if (($produit->getConfig()->getRendement() > $produit->getSommeProduitsCepage('volume_revendique_issu_recolte')) && ($produit->vci->stock_precedent > 0) && ($produit->vci->stock_precedent > $produit->vci->complement) && ($produit->getPlafondStockVci() > $produit->vci->complement)) {
        	$this->addPoint(self::TYPE_WARNING, 'vci_complement', $produit->getCepage()->getLibelleComplet(), $this->generateUrl('drev_vci', array('sf_subject' => $this->document)));
        }

        if($produit->getConfig()->hasMutageAlcoolique()) {
            $ratioMutageRecolte = ($produit->volume_revendique_issu_recolte) ? round($produit->volume_revendique_issu_mutage * 100 / $produit->volume_revendique_issu_recolte, 2) : 0;
            if ($ratioMutageRecolte < 5 || $ratioMutageRecolte > 10.42) {
            	$this->addPoint(self::TYPE_ERROR, 'mutage_ratio', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
            }
        }

        if ($produit->getSommeProduitsCepage('recolte/vci_constitue') > 0 && $produit->getSommeProduitsCepage('recolte/vsi') > 0) {
            $this->addPoint(self::TYPE_ERROR, 'dr_vci_vsi', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        }
    }

    protected function controleVci($produit)
    {
        if(!$produit->hasVci()) {
            return;
        }
        if(round(intval($produit->vci->stock_precedent), 4) != round($produit->getTotalVciUtilise(), 4)) {
            $this->addPoint(self::TYPE_ERROR, 'vci_stock_utilise', $produit->getLibelleComplet(), $this->generateUrl('drev_vci', array('sf_subject' => $this->document)));
        }
        if($produit->getConfig()->rendement_vci_total !== null && round($produit->getPlafondStockVci(), 4) < $produit->vci->stock_final) {
            $point = $this->addPoint(self::TYPE_WARNING, 'vci_rendement_total', $produit->getLibelleComplet(), $this->generateUrl('drev_vci', array('sf_subject' => $this->document)));
            $vol = $produit->vci->stock_final - round($produit->getPlafondStockVci(), 4);
            $point->setMessage($point->getMessage() . " soit $vol hl");
        }
        if(round($produit->getCepage()->getRendementVCIConstitue(), 2) > $produit->getConfig()->getRendementVci()) {
            $point = $this->addPoint(self::TYPE_ERROR, 'vci_rendement', $produit->getLibelleComplet() . ' (rendement VCI de ' . round($produit->getCepage()->getRendementVciConstitue(), 2) . ' hl/ha pour '. $produit->getConfig()->getRendementVci().' hl/ha autorisé)', $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        }
    }

    protected function controleHabilitationINAO()
    {
        if (!DRevConfiguration::getInstance()->hasHabilitationINAO()) {
            return;
        }
        foreach($this->document->getNonHabilitationINAO() as $produit) {
            $this->addPoint(self::TYPE_WARNING, 'drev_habilitation_inao', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
        }
    }

    protected function controleHabilitationODG()
    {
        $e = EtablissementFindByCviView::getInstance()->findByCvi($this->document->declarant->cvi);
        if(count($e) > 1 && ($this->document->declarant->cvi || !$this->document->declarant->ppm)) {
            $this->addPoint(self::TYPE_ERROR, 'declaration_multi_cvi', 'Le CVI '.$this->document->declarant->cvi, $this->generateUrl('compte_search', array('q' => $this->document->declarant->cvi, 'contacts_all' => 1, 'tags' => 'automatique:etablissement')) );
        }

        if (DRevConfiguration::getInstance()->hasHabilitationINAO()) {
            return;
        }
        foreach($this->document->getNonHabilitationODG() as $produit) {
            $this->addPoint(self::TYPE_ERROR, 'drev_habilitation_odg', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
        }
    }

    protected function controleBailleurs(){
        if(!sfContext::getInstance()->getUser()->hasDrevAdmin()) {
            return;
        }
        $bailleursNonReconnus = array();
        foreach($this->document->getBailleurs() as $b) {
            if($b['etablissement_id']) {
                continue;
            }
            $bailleursNonReconnus[] = $b['raison_sociale'] . " (".$b['ppm'].")";
        }

        if(count($bailleursNonReconnus)) {
            $this->addPoint(self::TYPE_WARNING, 'bailleurs', implode(", ", $bailleursNonReconnus));
        }
    }

    protected function controleLots(){
        if(!$this->document->exist('lots')){
            return;
        }

        $this->controleLotsGenerique('drev_lots');

        $is_elevage = false;
        foreach ($this->document->lots as $key => $lot) {
            if($lot->hasBeenEdited()){
              continue;
            }

            if($lot->isEmpty()){
              continue;
            }

            //si lots IGP n'existent pas dans la DR
            if(!$lot->lotPossible() && $this->document->hasDocumentDouanier() && $lot->getVolume()){
                $volume = sprintf("%01.02f",$lot->getVolume());
                if (preg_match('/(DEFAUT|MULTI)$/', $lot->produit_hash)) {
                    $this->addPoint(self::TYPE_WARNING, 'lot_igp_inexistant_dans_dr_warn', $lot->getProduitLibelle(). " ( ".$lot->volume." hl )", $this->generateUrl('drev_lots', array("id" => $this->document->_id, "appellation" => $key)));
                }else{
                    $this->addPoint(self::TYPE_ERROR, 'lot_igp_inexistant_dans_dr_err', $lot->getProduitLibelle(). " ( ".$lot->volume." hl )", $this->generateUrl('drev_lots', array("id" => $this->document->_id, "appellation" => $key)));
                }
            }
            if ($lot->isInElevage()) {
                $is_elevage = true;
            }
        }
        if ($is_elevage) {
            $this->addPoint(self::TYPE_WARNING, 'declaration_elevage', '');
        }

        $synthese = $this->document->summerizeProduitsLotsByCouleur('couleur');
        foreach ($synthese as $couleur => $synt) {
            if (strpos($couleur, 'Total') === false) {
                continue;
            }
            if ($this->document->hasDR() && isset($synthese[$couleur]['millesime']) && $synthese[$couleur]['millesime'] == $this->document->getDR()->periode && isset($synthese[$couleur]['volume_restant_max']) && round($synthese[$couleur]['volume_restant_max'], 4) < -0.0001) {
                if ($this->document->exist('achat_tolerance') && $this->document->get('achat_tolerance')) {
                    $this->addPoint(self::TYPE_WARNING, 'lot_volume_total_depasse_warn', $synthese[$couleur]['libelle'], $this->generateUrl('drev_lots', array('id' => $this->document->_id)));
                }else{
                    $this->addPoint(self::TYPE_ERROR, 'lot_volume_total_depasse', $synthese[$couleur]['libelle'], $this->generateUrl('drev_lots', array('id' => $this->document->_id)));
                }
            }
        }
    }

    protected function controleVolumeSeuilDeclare($produit){
        if(!$this->document->hasVolumeSeuilAndSetIfNecessary()){
            return null;
        }

        if (count($produit['hashes']) === 0) {
            return false;
        }

        $libelle = $produit['libelle']." ".$this->document->getDefaultMillesime();

        $volumeTotalSeuilDeclare = $produit['volume'];
        $volumeCommercialisableLibre = $produit['volume_max'] - ($produit['volume_max'] * 0.1);
        $volumeMaxAutorise = $produit['volume_max'];

        if(($volumeCommercialisableLibre < $volumeTotalSeuilDeclare) && ($volumeTotalSeuilDeclare < $volumeMaxAutorise)) {
            $this->addPoint(self::TYPE_WARNING, 'declaration_superieur_volume_commerciable_'.$produit['hash_regex'], $libelle." (".$volumeTotalSeuilDeclare." hl)", $this->generateUrl('drev_lots', array("id" => $this->document->_id)));
        } elseif($volumeMaxAutorise < $volumeTotalSeuilDeclare) {
            if($this->document->hasDestinationConditionnement($produit['hashes'])){
                $this->addPoint(self::TYPE_WARNING, 'declaration_superieur_volume_autorise_'.$produit['hash_regex'], $libelle." (".$volumeTotalSeuilDeclare." hl)", $this->generateUrl('drev_lots', array("id" => $this->document->_id)));
                $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_CONDITIONNEMENT.'_'.$produit['hash_regex'], '', null, $produit['hash_regex']);
            }
            if (VIP2C::hasVolumeSeuil() && $this->document->hasDestinationVrac($produit['hashes'])) {
                $contrats = (array_key_exists('contrats', $produit))? $produit['contrats'] : [];

                if (count($contrats) === 0) {
                    $this->addPoint(self::TYPE_ERROR,'vip2c_pas_de_contrats_'.$produit['hash_regex'], $libelle, $this->generateUrl('drev_lots', array("id" => $this->document->_id)) );
                } else {
                    $this->addPoint(self::TYPE_WARNING, 'declaration_superieur_volume_autorise_'.$produit['hash_regex'], $libelle." (".$volumeTotalSeuilDeclare." hl)", $this->generateUrl('drev_lots', array("id" => $this->document->_id)));
                    foreach($contrats as $numero => $contrat){
                        $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_CONTRAT_VENTE_EN_VRAC."_".$numero, $libelle, null, $produit['hash_regex']);
                    }
                }

                if ($contrats && count($contrats) > 1) {
                    $this->addPoint(self::TYPE_WARNING, 'declaration_superieur_volume_autorise_'.$produit['hash_regex'], $libelle." (".$volumeTotalSeuilDeclare." hl)", $this->generateUrl('drev_lots', array("id" => $this->document->_id)));
                    if(sfContext::getInstance()->getUser()->hasDrevAdmin()) {
                        $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_PAS_INFORMATION,"");
                    }
                }
            }
        }
    }


    public function controleVsi() {
        if(!$this->document->hasVsi()) {
            return;
        }
        foreach($this->document->getProduits() as $produit) {
            if(round($produit->getCepage()->getRendementVsi(), 2) <= $produit->getConfig()->getRendementVsi()) {
                continue;
            }

            $point = $this->addPoint(self::TYPE_ERROR, 'vsi_rendement', $produit->getLibelleComplet() . ' (rendement VSI de ' . round($produit->getCepage()->getRendementVsi(), 2) . ' hl/ha pour '. $produit->getConfig()->getRendementVsi().' hl/ha autorisé)', $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        }
        if($this->document->isPapier()) {
            return;
        }
        $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VSI_DESTRUCTION, '');
    }
}
