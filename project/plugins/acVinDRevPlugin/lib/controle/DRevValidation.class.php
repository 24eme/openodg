<?php
class DRevValidation extends DocumentValidation
{
    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';
    const TYPE_ENGAGEMENT = 'engagement';

    protected $etablissement = null;

    public function __construct($document, $options = null)
    {
        $this->etablissement = $document->getEtablissementObject();
        parent::__construct($document, $options);
        $this->noticeVigilance = true;
    }

    public function configure()
    {
        /*
         * Warning
         */
        $this->addControle(self::TYPE_WARNING, 'declaration_habilitation', 'Vous avez déclaré du volume sans habilitation');
        $this->addControle(self::TYPE_WARNING, 'declaration_volume_l15', 'Vous ne revendiquez pas le même volume que celui qui figure sur votre déclaration douanière en L15 (peut-être dû au complèment de VCI)');
        $this->addControle(self::TYPE_WARNING, 'declaration_neant', "Vous n'avez déclaré aucun produit");
        $this->addControle(self::TYPE_WARNING, 'declaration_produits_incoherence', "Vous ne déclarez pas tous les produits de votre déclaration douanière");
        $this->addControle(self::TYPE_WARNING, 'declaration_surface_bailleur', "Vous n'avez pas reparti votre part de surface avec le bailleur");
        $this->addControle(self::TYPE_WARNING, 'vci_complement', "Vous ne complétez pas votre volume malgré votre stock VCI disponible");
        $this->addControle(self::TYPE_WARNING, 'declaration_volume_l15_dr_zero', 'Le volume de recolte nette est à 0');

        $this->addControle(self::TYPE_WARNING, 'lot_millesime_non_saisie', "Le millesime du lot n'a pas été saisie");
        $this->addControle(self::TYPE_WARNING, 'lot_destination_non_saisie', "La destination du lot n'a pas été renseignée entièrement");
        /*
         * Error
         */
        $this->addControle(self::TYPE_ERROR, 'revendication_incomplete_volume', "Le volume revendique n'a pas été saisie");
        $this->addControle(self::TYPE_ERROR, 'revendication_incomplete_superficie', "La superficie revendiqué n'a pas été saisie");
        $this->addControle(self::TYPE_ERROR, 'revendication_rendement', "Le rendement sur le volume revendiqué n'est pas respecté");
        $this->addControle(self::TYPE_WARNING, 'revendication_rendement_conseille', "Le rendement sur le volume revendiqué dépasse le rendement légal il faudra fournir une dérogation pour être autorisé à revendiquer ce rendement");
        $this->addControle(self::TYPE_ERROR, 'vci_stock_utilise', "Le stock de vci n'a pas été correctement reparti");
        $this->addControle(self::TYPE_WARNING, 'vci_rendement_total', "Le stock de vci final dépasse le rendement autorisé : vous devrez impérativement détruire Stock final - Plafond VCI Hls");
        $this->addControle(self::TYPE_ERROR, 'declaration_volume_l15_complement', 'Vous revendiquez un volume supérieur à celui qui figure sur votre déclaration douanière en L15');
        $this->addControle(self::TYPE_ERROR, 'declaration_volume_l15_dr', 'Certaines informations provenant de votre déclaration douanière sont manquantes');

        $this->addControle(self::TYPE_ERROR, 'vci_substitue_rafraichi', 'Vous ne pouvez ni subsituer ni rafraichir un volume de VCI supérieur à celui qui figure sur votre déclaration douanière en L15');
        $this->addControle(self::TYPE_ERROR, 'revendication_superficie', 'Vous revendiquez une superficie supérieur à celle qui figure sur votre déclaration douanière en L4');
        $this->addControle(self::TYPE_ERROR, 'revendication_superficie_dr', 'Les données de superficie provenant de votre déclaration douanière sont manquantes');

        $this->addControle(self::TYPE_WARNING, 'dr_recolte_rendement', "Vous dépassez le rendement dans votre DR (L5 - L15)");
        $this->addControle(self::TYPE_WARNING, 'sv12_recolte_rendement', "Vous dépassez le rendement dans votre SV12");
        $this->addControle(self::TYPE_WARNING, 'sv11_recolte_rendement', "Vous dépassez le rendement dans votre SV11");

        $this->addControle(self::TYPE_WARNING, 'drev_habilitation_inao', "Vous ne semblez pas habilité pour ce produit");

        $this->addControle(self::TYPE_ERROR, 'lot_volume_total_depasse', 'Le volume total est dépassé');
        $this->addControle(self::TYPE_ERROR, 'lot_cepage_volume_different', "Le volume déclaré ne correspond pas à la somme des volumes des cépages");

        /*
         * Engagement
         */
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV11, 'Joindre une copie de votre SV11');
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV12, 'Joindre une copie de votre SV12');
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VCI, 'Je m\'engage à transmettre le justificatif de destruction de VCI');
    }

    public function controle()
    {
        $this->controleRecoltes();
    	$produits = array();
        foreach ($this->document->getProduitsWithoutLots() as $hash => $produit) {
              $this->controleRevendication($produit);
              $this->controleVci($produit);
        }

        foreach ($this->document->getProduits() as $hash => $produit) {
          $produits[$hash] = $produit;
        }
        $this->controleNeant();
        $this->controleEngagementVCI();
        $this->controleEngagementSv();
        $this->controleProduitsDocumentDouanier($produits);
        $this->controleSurfaceBailleur();
        $this->controleHabilitationINAO();
        $this->controleLots();
    }

    protected function controleNeant()
    {
    	if(count($this->document->getProduits()) > 0) {
    		return;
    	}
    	$this->addPoint(self::TYPE_WARNING, 'declaration_neant', '', $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
    }

    protected function controleProduitsDocumentDouanier($produits)
    {
    	$drev = $this->document->getFictiveFromDocumentDouanier();
    	$hasDiff = false;
    	foreach ($drev->getProduits() as $hash => $produit) {
    		if (!array_key_exists($hash, $produits)) {
    			$hasDiff = true;
    		}
    	}
    	if ($hasDiff) {
    		$this->addPoint(self::TYPE_WARNING, 'declaration_produits_incoherence', '', $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
    	}
    }

    protected function controleSurfaceBailleur()
    {
    	$bailleurs = $this->document->getProduitsBailleur();
    	foreach ($this->document->getProduits() as $produit) {
    		if (in_array($produit->getConfig()->getHash(), $bailleurs)) {
	    		if (round($produit->recolte->superficie_total,4) == round($produit->superficie_revendique,4)) {
	    			$this->addPoint(self::TYPE_WARNING, 'declaration_surface_bailleur', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
	    		}
    		}
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
            if((($produit->recolte->volume_total - $produit->recolte->usages_industriels_total) / $produit->recolte->superficie_total) > $produit->getConfig()->getRendement()) {
                $type_msg = strtolower($this->document->getDocumentDouanierType()).'_recolte_rendement';
                $this->addPoint(self::TYPE_WARNING,$type_msg , $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
            }
        }
    }

    protected function controleRevendication($produit)
    {
        if ($produit->isCleanable()) {
          return;
        }
        if($produit->superficie_revendique === null) {
            $this->addPoint(self::TYPE_ERROR, 'revendication_incomplete_superficie', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
        }
        if($produit->volume_revendique_issu_recolte === null) {
            $this->addPoint(self::TYPE_ERROR, 'revendication_incomplete_volume', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        }
        if ($produit->superficie_revendique > 0 && $produit->volume_revendique_issu_recolte > 0) {
	        if($produit->getConfig()->getRendement() !== null && round(($produit->getRendementEffectif()), 2) > round($produit->getConfig()->getRendement(), 2)) {
	        	$this->addPoint(self::TYPE_ERROR, 'revendication_rendement', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
	        } elseif($produit->getConfig()->getRendementConseille() > 0 && round(($produit->getRendementEffectif()), 2) > round($produit->getConfig()->getRendementConseille(), 2)) {
                $this->addPoint(self::TYPE_WARNING, 'revendication_rendement_conseille', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
            }
        }
        if (!DRevConfiguration::getInstance()->hasHabilitationINAO() && !$produit->isHabilite()) {
            $this->addPoint(self::TYPE_WARNING, 'declaration_habilitation', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        }

        if ($this->document->getDocumentDouanierType() != DRCsvFile::CSV_TYPE_DR && !$produit->recolte->volume_sur_place) {
            $this->addPoint(self::TYPE_ERROR, 'declaration_volume_l15_dr', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        } elseif ($this->document->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR && !$produit->recolte->recolte_nette) {
            $this->addPoint(self::TYPE_WARNING, 'declaration_volume_l15_dr_zero', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        } else {

	        if (round($produit->volume_revendique_issu_recolte, 4) != round($produit->recolte->recolte_nette, 4) && round($produit->recolte->volume_total, 4) == round($produit->recolte->volume_sur_place, 4)) {
	          	$this->addPoint(self::TYPE_WARNING, 'declaration_volume_l15', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
	        }
	        if (round($produit->volume_revendique_total, 4) > round($produit->recolte->recolte_nette + $produit->vci->complement, 4) && round($produit->recolte->volume_total, 4) == round($produit->recolte->volume_sur_place, 4) && (!$this->document->exist('achat_tolerance') || !$this->document->achat_tolerance)) {
	        	$this->addPoint(self::TYPE_ERROR, 'declaration_volume_l15_complement', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
	        }
	        if ($produit->recolte->recolte_nette && ($produit->recolte->recolte_nette + $produit->vci->complement) < ($produit->vci->substitution + $produit->vci->rafraichi)) {
	        	$this->addPoint(self::TYPE_ERROR, 'vci_substitue_rafraichi', $produit->getLibelleComplet(), $this->generateUrl('drev_vci', array('sf_subject' => $this->document)));
	        }
        }
        if (!$produit->recolte->superficie_total) {
        	$this->addPoint(self::TYPE_ERROR, 'revendication_superficie_dr', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
        } else {
	        if ($produit->superficie_revendique > $produit->recolte->superficie_total) {
	        	$this->addPoint(self::TYPE_ERROR, 'revendication_superficie', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
	        }
        }
        if ($produit->getConfig()->getRendement() > $produit->volume_revendique_issu_recolte && $produit->vci->stock_precedent > 0 && $produit->vci->stock_precedent > $produit->vci->complement) {
        	$this->addPoint(self::TYPE_WARNING, 'vci_complement', $produit->getLibelleComplet(), $this->generateUrl('drev_vci', array('sf_subject' => $this->document)));
        }

    }

    protected function controleVci($produit)
    {
        if(!$produit->hasVci()) {
            return;
        }
        if(round($produit->vci->stock_precedent, 4) != round($produit->getTotalVciUtilise(), 4)) {
            $this->addPoint(self::TYPE_ERROR, 'vci_stock_utilise', $produit->getLibelleComplet(), $this->generateUrl('drev_vci', array('sf_subject' => $this->document)));
        }
        if($produit->getConfig()->rendement_vci_total !== null && round($produit->getPlafondStockVci(), 4) < $produit->vci->stock_final) {
            $point = $this->addPoint(self::TYPE_WARNING, 'vci_rendement_total', $produit->getLibelleComplet(), $this->generateUrl('drev_vci', array('sf_subject' => $this->document)));
            $vol = $produit->vci->stock_final - round($produit->getPlafondStockVci(), 4);
            $point->setMessage($point->getMessage() . " soit $vol hl");
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

    protected function controleLots(){
      if($this->document->exist('lots')){
        foreach ($this->document->lots as $key => $lot) {
          if($lot->hasBeenEdited()){
            continue;
          }
          if(!$lot->hasVolumeAndHashProduit()){
            continue;
          }
          $volume = sprintf("%01.02f",$lot->getVolume());
          if(!$lot->exist('millesime') || !$lot->millesime){
              $this->addPoint(self::TYPE_WARNING, 'lot_millesime_non_saisie', $lot->getProduitLibelle()." ( ".$volume." hl )", $this->generateUrl('drev_lots', array("id" => $this->document->_id, "appellation" => $key)));
          }
          if(!$lot->exist('destination_type') || !$lot->destination_type || !$lot->exist('destination_date') || !$lot->destination_date){
              $this->addPoint(self::TYPE_WARNING, 'lot_destination_non_saisie', $lot->getProduitLibelle(). " ( ".$volume." hl )", $this->generateUrl('drev_lots', array("id" => $this->document->_id, "appellation" => $key)));
          }
          if(count($lot->cepages)){
            $somme = 0.0;
            foreach ($lot->cepages as $cepage => $v) {
              $somme+=$v;
            }
            if($somme != $lot->volume){
              $this->addPoint(self::TYPE_ERROR, 'lot_cepage_volume_different', $lot->getProduitLibelle(). " ( ".$volume." hl )", $this->generateUrl('drev_lots', array("id" => $this->document->_id, "appellation" => $key)));
            }
          }
      }

        $synthese = $this->document->summerizeProduitsByCouleur();
        foreach ($this->document->getLotsByCouleur() as $couleur => $lot) {
            if (! isset($synthese[$couleur])) {
                continue;
            }

            $volume = 0;
            foreach ($lot as $produit) {
                $volume += $produit->volume;
            }

            if ($volume > $synthese[$couleur]['volume_total']) {
                $this->addPoint(self::TYPE_ERROR, 'lot_volume_total_depasse', $couleur);
            }
        }
    }
  }
}
