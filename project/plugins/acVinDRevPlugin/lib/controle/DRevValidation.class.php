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
        $this->addControle(self::TYPE_WARNING, 'declaration_volume_l15', 'Vous revendiquez plus de volume que celui qui figure sur votre déclaration douanière en L15');
        $this->addControle(self::TYPE_WARNING, 'vci_rendement_annee', "Le vci de l'annéee dépasse le rendement autorisé");
        $this->addControle(self::TYPE_WARNING, 'declaration_neant', "Vous n'avez déclaré aucun produit");
        $this->addControle(self::TYPE_WARNING, 'declaration_produits_incoherence', "Vous ne déclarez pas tous les produits de votre déclaration douanière");
        $this->addControle(self::TYPE_WARNING, 'declaration_surface_bailleur', "Vous n'avez pas reparti votre part de surface avec le bailleur");
        $this->addControle(self::TYPE_WARNING, 'vci_complement', "Vous ne complétez pas votre volume malgré votre stock VCI disponible");
        /*
         * Error
         */
        $this->addControle(self::TYPE_ERROR, 'revendication_incomplete', "Toutes les informations de revendication n'ont pas été saisies");
        $this->addControle(self::TYPE_ERROR, 'revendication_rendement', "Le rendement sur le volume revendiqué n'est pas respecté");
        $this->addControle(self::TYPE_ERROR, 'vci_stock_utilise', "Le stock de vci n'a pas été correctement reparti");
        $this->addControle(self::TYPE_WARNING, 'vci_rendement_total', "Le stock de vci final dépasse le rendement autorisé : vous devrez impérativement détruire Stock final - Plafond VCI Hls");
        $this->addControle(self::TYPE_ERROR, 'declaration_volume_l15_complement', 'Vous revendiquez un volume supérieur à celui qui figure sur votre déclaration douanière en L15');
        $this->addControle(self::TYPE_ERROR, 'declaration_volume_l15_dr', 'Certaines informations provenant de votre déclaration douanière sont manquantes');
        $this->addControle(self::TYPE_ERROR, 'vci_substitue_rafraichi', 'Vous ne pouvez ni subsituer ni rafraichir un volume de VCI supérieur à celui qui figure sur votre déclaration douanière en L15');
        $this->addControle(self::TYPE_ERROR, 'revendication_superficie', 'Vous revendiquez une superficie supérieur à celle qui figure sur votre déclaration douanière en L4');
        $this->addControle(self::TYPE_ERROR, 'revendication_superficie_dr', 'Les données de superficie provenant de votre déclaration douanière sont manquantes');
        /*
         * Engagement
         */
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV11, 'Joindre une copie de votre SV11');
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV12, 'Joindre une copie de votre SV12');
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VCI, 'Je m\'engage à transmettre le justificatif de destruction de VCI');
    }

    public function controle()
    {
    	$produits = array();
        foreach ($this->document->getProduits() as $hash => $produit) {
            $this->controleRevendication($produit);
            $this->controleVci($produit);
            $produits[$hash] = $produit;
        }
        $this->controleNeant();
        $this->controleEngagementVCI();
        $this->controleEngagementSv();
        $this->controleProduitsDocumentDouanier($produits);
        $this->controleSurfaceBailleur();
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
	    		if (round($produit->recolte->superficie_total,2) == round($produit->superficie_revendique,2)) {
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

    protected function controleRevendication($produit)
    {
        if($produit->superficie_revendique === null || $produit->volume_revendique_issu_recolte === null) {
            $this->addPoint(self::TYPE_ERROR, 'revendication_incomplete', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        }
        if ($produit->superficie_revendique > 0) {
	        if($produit->getConfig()->getRendement() !== null && round(($produit->volume_revendique_total / $produit->superficie_revendique), 2) > $produit->getConfig()->getRendement()) {
	        	$this->addPoint(self::TYPE_ERROR, 'revendication_rendement', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
	        }
        } else{
        	if($produit->getConfig()->getRendement() !== null && round(($produit->volume_revendique_total), 2) > $produit->getConfig()->getRendement()) {
        		$this->addPoint(self::TYPE_ERROR, 'revendication_rendement', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        	}
        }
        if (!$produit->isHabilite()) {
            $this->addPoint(self::TYPE_WARNING, 'declaration_habilitation', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        }

        if ($this->document->getDocumentDouanierType() != DRCsvFile::CSV_TYPE_DR && !$produit->recolte->volume_sur_place) {
            $this->addPoint(self::TYPE_ERROR, 'declaration_volume_l15_dr', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        } elseif ($this->document->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR && !$produit->recolte->recolte_nette) {
            $this->addPoint(self::TYPE_ERROR, 'declaration_volume_l15_dr', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        } else {
	        if (round($produit->volume_revendique_total, 2) != round($produit->recolte->recolte_nette, 2) && round($produit->recolte->volume_total, 2) == round($produit->recolte->volume_sur_place, 2)) {
	          	$this->addPoint(self::TYPE_WARNING, 'declaration_volume_l15', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
	        }
	        if (round($produit->volume_revendique_total, 2) > round($produit->recolte->recolte_nette + $produit->vci->complement, 2) && round($produit->recolte->volume_total, 2) == round($produit->recolte->volume_sur_place, 2)) {
	        	$this->addPoint(self::TYPE_ERROR, 'declaration_volume_l15_complement', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
	        }
	        if (($produit->recolte->recolte_nette + $produit->vci->complement) < ($produit->vci->substitution + $produit->vci->rafraichi)) {
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
        if(round($produit->vci->stock_precedent, 2) != round($produit->getTotalVciUtilise(), 2)) {
            $this->addPoint(self::TYPE_ERROR, 'vci_stock_utilise', $produit->getLibelleComplet(), $this->generateUrl('drev_vci', array('sf_subject' => $this->document)));
        }
        if($produit->getConfig()->getRendementVci() !== null && round($produit->getConfig()->getRendementVci() * $produit->superficie_revendique, 2) < round($produit->vci->constitue, 2)) {
            $this->addPoint(self::TYPE_WARNING, 'vci_rendement_annee', $produit->getLibelleComplet(), $this->generateUrl('drev_vci', array('sf_subject' => $this->document)));
        }
        if($produit->getConfig()->rendement_vci_total !== null && round($produit->getPlafondStockVci(), 2) < $produit->vci->stock_final) {
            $point = $this->addPoint(self::TYPE_WARNING, 'vci_rendement_total', $produit->getLibelleComplet(), $this->generateUrl('drev_vci', array('sf_subject' => $this->document)));
            $vol = $produit->vci->stock_final - round($produit->getPlafondStockVci(), 2);
            $point->setMessage($point->getMessage() . " soit $vol hl");
        }
    }
}
