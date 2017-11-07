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
        $this->addControle(self::TYPE_WARNING, 'declaration_volume_l15', 'Vous revendiquez un volume différent de celui qui figure sur votre DR en L15');
        $this->addControle(self::TYPE_WARNING, 'vci_rendement_annee', "Le vci de l'annéee dépasse le rendement autorisé");
        $this->addControle(self::TYPE_WARNING, 'declaration_neant', "Vous n'avez déclaré aucun produit");
        /*
         * Error
         */
        $this->addControle(self::TYPE_ERROR, 'revendication_incomplete', "Toutes les informations de revendication n'ont pas été saisies");
        $this->addControle(self::TYPE_ERROR, 'revendication_rendement', "Le rendement sur le volume revendiqué n'est pas respecté");
        $this->addControle(self::TYPE_ERROR, 'vci_stock_utilise', "Le stock de vci n'a pas été correctement reparti");
        $this->addControle(self::TYPE_ERROR, 'vci_rendement_total', "Le stock de vci final dépasse le rendement autorisé");
        $this->addControle(self::TYPE_ERROR, 'declaration_volume_l15_complement', 'Vous revendiquez un volume revendiqué supérieur à celui qui figure sur votre DR en L15');
        $this->addControle(self::TYPE_ERROR, 'vci_substitue_rafraichi', 'Vous ne pouvez ni subsituer ni rafraichir un volume de VCI supérieur à celui qui figure sur votre DR en L15');
        /*
         * Engagement
         */
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV11, 'Joindre une copie de votre SV11');
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV12, 'Joindre une copie de votre SV12');
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VCI, 'Je m\'engage à transmettre le justificatif de destruction de VCI');
    }

    public function controle() 
    {
        foreach ($this->document->getProduits() as $hash => $produit) {
            $this->controleRevendication($produit);
            $this->controleVci($produit);
        }
        $this->controleNeant();
        $this->controleEngagementVCI();
        $this->controleEngagementSv();
    }

    protected function controleNeant()
    {
    	if(count($this->document->getProduits()) > 0) {
    		return;
    	}
    	$this->addPoint(self::TYPE_WARNING, 'declaration_neant', '', $this->generateUrl('drev_revendication_superficie', array('sf_subject' => $this->document)));
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
        if($produit->getConfig()->getRendement() !== null && round(($produit->volume_revendique_total / $produit->superficie_revendique), 2) > $produit->getConfig()->getRendement()) {
            $this->addPoint(self::TYPE_ERROR, 'revendication_rendement', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        }
        if (!$produit->isHabilite()) {
            $this->addPoint(self::TYPE_WARNING, 'declaration_habilitation', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        }
        if ($produit->volume_revendique_total != $produit->recolte->recolte_nette && $produit->recolte->volume_total == $produit->recolte->volume_sur_place) {
          	$this->addPoint(self::TYPE_WARNING, 'declaration_volume_l15', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        }
        if ($produit->volume_revendique_total > ($produit->recolte->recolte_nette + $produit->vci->complement)) {
        	$this->addPoint(self::TYPE_ERROR, 'declaration_volume_l15_complement', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
        }
        if (($produit->recolte->recolte_nette + $produit->vci->complement) < ($produit->vci->substitution + $produit->vci->rafraichi)) {
        	$this->addPoint(self::TYPE_ERROR, 'vci_substitue_rafraichi', $produit->getLibelleComplet(), $this->generateUrl('drev_vci', array('sf_subject' => $this->document)));
        }

    }

    protected function controleVci($produit) 
    {
        if(!$produit->hasVci()) {
            return;
        }
        if($produit->vci->stock_precedent != $produit->getTotalVciUtilise()) {
            $this->addPoint(self::TYPE_ERROR, 'vci_stock_utilise', $produit->getLibelleComplet(), $this->generateUrl('drev_vci', array('sf_subject' => $this->document)));
        }
        if($produit->getConfig()->getRendementVci() !== null && round($produit->getConfig()->getRendementVci() * $produit->superficie_revendique, 2) < round($produit->vci->constitue, 2)) {
            $this->addPoint(self::TYPE_WARNING, 'vci_rendement_annee', $produit->getLibelleComplet(), $this->generateUrl('drev_vci', array('sf_subject' => $this->document)));
        }
        if($produit->getConfig()->rendement_vci_total !== null && round($produit->getConfig()->rendement_vci_total * $produit->superficie_revendique, 2) < $produit->vci->stock_final) {
            $this->addPoint(self::TYPE_ERROR, 'vci_rendement_total', $produit->getLibelleComplet(), $this->generateUrl('drev_vci', array('sf_subject' => $this->document)));
        }
    }
}
