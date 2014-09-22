<?php
class DRevValidation extends DocumentValidation
{
    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';
	
	public function __construct($document, $options = null)
    {
        parent::__construct($document, $options);
        $this->noticeVigilance = false;
    }
    
  	public function configure() 
  	{
  		/*
  		 * Warning
  		 */
  		$this->addControle(self::TYPE_WARNING, 'dr_surface', 'La surface revendiquée est différente de celle déclarée de votre DR.');
  		$this->addControle(self::TYPE_WARNING, 'dr_volume', 'Le volume revendiqué est différent de celui déclaré de votre DR.');
  		$this->addControle(self::TYPE_WARNING, 'prelevement_vtsgn_sans_lot', 'Vous avez fait une demande de prélèvement VT/SGN sans déclarer de lot.');
  		$this->addControle(self::TYPE_WARNING, 'lot_vtsgn_sans_prelevement', 'Vous avez déclaré des lots VT/SGN sans spécifier de période de prélèvement.');
  		$this->addControle(self::TYPE_WARNING, 'lot_vtsgn_sans_controle_externe', 'Vous n\'avez pas renseigné vos informations VT/SGN.');
  		
  		$this->addControle(self::TYPE_WARNING, 'dr_cepage', 'Vous ne déclarez aucun lot pour un cépage présent dans votre DR.'); // !!!!
  		/*
  		 * Error
  		 */
    	$this->addControle(self::TYPE_ERROR, 'revendication_incomplete', 'Vous devez saisir la superficie et le volume pour vos produits revendiqués.');
    	$this->addControle(self::TYPE_ERROR, 'volume_revendique_incorrect', 'Le volume revendiqué ne peut pas être inférieur au volume sur place déduit des usages industriels et supérieur au volume sur place.');
    	$this->addControle(self::TYPE_ERROR, 'prelevement_alsace', 'Vous devez saisir une semaine de prélèvement pour l\'AOC Alsace.');
    	$this->addControle(self::TYPE_ERROR, 'revendication_alsace_sans_lot_alsace', 'Vous avez revendiqué des produits AOC Alsace sans spécifier de lot.');
    	$this->addControle(self::TYPE_ERROR, 'lot_alsace_sans_revendication_alsace', 'Vous avez spécifié des lots AOC Alsace sans revendiquer de produit AOC Alsace.');
    	$this->addControle(self::TYPE_ERROR, 'revendication_grdcru_sans_lot_grdcru', 'Vous avez revendiqué des produits AOC Alsace Grand Cru sans spécifier de lot.');
    	$this->addControle(self::TYPE_ERROR, 'lot_grdcru_sans_revendication_grdcru', 'Vous avez spécifié des lots AOC Alsace Grand Cru sans revendiquer de produit AOC Alsace Grand Cru.');
    	$this->addControle(self::TYPE_ERROR, 'prelevement_alsace_sans_revendication_alsace', 'Vous avez fait une demande de prélèvement AOC Alsace sans revendiqué de produit AOC Alsace.');
    	$this->addControle(self::TYPE_ERROR, 'revendication_alsace_sans_prelevement_alsace', 'Vous avez revendiqué des produits AOC Alsace sans faire de demande de prélèvement AOC Alsace.');
    	$this->addControle(self::TYPE_ERROR, 'prelevement_grdcru_sans_revendication_grdcru', 'Vous avez fait une demande de prélèvement AOC Alsace Grand Cru sans revendiqué de produit AOC Alsace Grand Cru.');
    	$this->addControle(self::TYPE_ERROR, 'revendication_grdcru_sans_prelevement_grdcru', 'Vous avez revendiqué des produits AOC Alsace Grand Cru sans faire de demande de prélèvement AOC Alsace Grand Cru.');
    	
    	$this->addControle(self::TYPE_ERROR, 'controle_externe_vtsgn', 'Vous devez renseigner une semaine et le nombre total de lots pour le VT/SGN'); // !!!!
    	
    	
  	}

    public function controle()
    {
      $revendicationProduits = $this->document->declaration->getProduits();
      foreach ($revendicationProduits as $hash => $revendicationProduit) {
        $this->controleWarningDrSurface($revendicationProduit);
        $this->controleWarningDrVolume($revendicationProduit);
        $this->controleErrorRevendicationIncomplete($revendicationProduit);
        $this->controleErrorVolumeRevendiqueIncorrect($revendicationProduit);
      }
      $this->controleErrorPrelevementAlsace();
      $this->controleErrorRevendicationAlsaceSansLotAlsace();
      $this->controleErrorLotAlsaceSansRevendicationAlsace();
      $this->controleErrorRevendicationGrdCruSansLotGrdCru();
      $this->controleErrorLotGrdCruSansRevendicationGrdCru();
      $this->controleErrorPrelevementAlsaceSansRevendicationAlsace();
      $this->controleErrorRevendicationAlsaceSansPrelevementAlsace();
      $this->controleErrorPrelevementGrdCruSansRevendicationGrdCru();
      $this->controleErrorRevendicationGrdCruSansPrelevementGrdCru();
      $this->controleWarningPrelevementVtsgnSansLot();
      $this->controleWarningLotVtsgnSansPrelevement();
      $this->controleWarningLotVtsgnSansControleExterne();
    }
  	
  	protected function controleWarningDrSurface($produit)
  	{
  		if (
  			$produit->superficie_revendique !== null && 
  			$produit->superficie_revendique !== null && 
  			$produit->superficie_revendique != $produit->detail->superficie_total
  		) {
  			$this->addPoint(self::TYPE_WARNING, 'dr_surface', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document))); 
  		}
  	}
  	
  	protected function controleWarningDrVolume($produit)
  	{
  		if (
  			$produit->volume_revendique !== null && 
  			$produit->detail->volume_sur_place_revendique !== null && 
  			$produit->volume_revendique != $produit->detail->volume_sur_place_revendique
  		) {
  			$this->addPoint(self::TYPE_WARNING, 'dr_volume', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document))); 
  		}
  	}
  	
  	protected function controleErrorRevendicationIncomplete($produit)
  	{
  		if (
  			($produit->superficie_revendique !== null && $produit->volume_revendique === null) ||
  			($produit->superficie_revendique === null && $produit->volume_revendique !== null)
  		) {
  			$this->addPoint(self::TYPE_ERROR, 'revendication_incomplete', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
  		}
  	}
  	
  	protected function controleErrorVolumeRevendiqueIncorrect($produit)
  	{
  		if (
  			$produit->volume_revendique !== null && 
  			$produit->detail->volume_sur_place !== null && 
  			$produit->detail->usages_industriels_total !== null && 
  			($produit->detail->volume_sur_place - $produit->detail->usages_industriels_total) > $produit->volume_revendique	
  		) {
  			$this->addPoint(self::TYPE_ERROR, 'volume_revendique_incorrect', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
  		}
  		
  		if (
  			$produit->volume_revendique !== null && 
  			$produit->detail->volume_sur_place !== null && 
  			$produit->volume_revendique > $produit->detail->volume_sur_place
  		) {
  			$this->addPoint(self::TYPE_ERROR, 'volume_revendique_incorrect', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
  		}
  	}
  	
  	protected function controleErrorPrelevementAlsace()
  	{
  		if (!$this->document->addPrelevement(DRev::CUVE_ALSACE)->date) {
  			$this->addPoint(self::TYPE_ERROR, 'prelevement_alsace', '', $this->generateUrl('drev_degustation_conseil', array('sf_subject' => $this->document)));
  		}
  	}
  	
  	protected function controleErrorRevendicationAlsaceSansLotAlsace()
  	{
  		if ($this->document->hasRevendicationAlsace() && !$this->document->addPrelevement(DRev::CUVE_ALSACE)->hasLots()) {
  			$this->addPoint(self::TYPE_ERROR, 'revendication_alsace_sans_lot_alsace', '', $this->generateUrl('drev_lots', array('sf_subject' => $this->document)));
  		}
  	}
  	
  	protected function controleErrorLotAlsaceSansRevendicationAlsace()
  	{
  		if (!$this->document->hasRevendicationAlsace() && $this->document->addPrelevement(DRev::CUVE_ALSACE)->hasLots()) {
  			$this->addPoint(self::TYPE_ERROR, 'lot_alsace_sans_revendication_alsace', '', $this->generateUrl('drev_revendication', array('sf_subject' => $this->document->addPrelevement(DRev::CUVE_ALSACE))));
  		}
  	}
  	
  	protected function controleErrorRevendicationGrdCruSansLotGrdCru()
  	{
  		if ($this->document->hasRevendicationGrdCru() && !$this->document->addPrelevement(DRev::CUVE_GRDCRU)->hasLots()) {
  			$this->addPoint(self::TYPE_ERROR, 'revendication_grdcru_sans_lot_grdcru', '', $this->generateUrl('drev_lots', $this->document->addPrelevement(DRev::CUVE_GRDCRU)));
  		}
  	}
  	
  	protected function controleErrorLotGrdCruSansRevendicationGrdCru()
  	{
  		if (!$this->document->hasRevendicationGrdCru() && $this->document->addPrelevement(DRev::CUVE_GRDCRU)->hasLots()) {
  			$this->addPoint(self::TYPE_ERROR, 'lot_grdcru_sans_revendication_grdcru', '', $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
  		}
  	}
  	
  	protected function controleErrorPrelevementAlsaceSansRevendicationAlsace()
  	{
  		if (!$this->document->hasRevendicationAlsace() && $this->document->addPrelevement(DRev::BOUTEILLE_ALSACE)->date) {
  			$this->addPoint(self::TYPE_ERROR, 'prelevement_alsace_sans_revendication_alsace', '', $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
  		}
  	}
  	
  	protected function controleErrorRevendicationAlsaceSansPrelevementAlsace()
  	{
  		if ($this->document->hasRevendicationAlsace() && !$this->document->addPrelevement(DRev::BOUTEILLE_ALSACE)->date) {
  			$this->addPoint(self::TYPE_ERROR, 'revendication_alsace_sans_prelevement_alsace', '', $this->generateUrl('drev_controle_externe', array('sf_subject' => $this->document)));
  		}
  	}
  	
  	protected function controleErrorPrelevementGrdCruSansRevendicationGrdCru()
  	{
  		if (!$this->document->hasRevendicationGrdCru() && $this->document->addPrelevement(DRev::BOUTEILLE_GRDCRU)->date) {
  			$this->addPoint(self::TYPE_ERROR, 'prelevement_grdcru_sans_revendication_grdcru', '', $this->generateUrl('drev_revendication', array('sf_subject' => $this->document)));
  		}
  	}
  	
  	protected function controleErrorRevendicationGrdCruSansPrelevementGrdCru()
  	{
  		if ($this->document->hasRevendicationGrdCru() && !$this->document->addPrelevement(DRev::BOUTEILLE_GRDCRU)->date) {
  			$this->addPoint(self::TYPE_ERROR, 'revendication_grdcru_sans_prelevement_grdcru', '', $this->generateUrl('drev_controle_externe', array('sf_subject' => $this->document)));
  		}
  	}
  	
  	protected function controleWarningPrelevementVtsgnSansLot()
  	{
  		if ($this->document->addPrelevement(DRev::CUVE_VTSGN)  && !$this->document->hasLots(true)) {
  			$this->addPoint(self::TYPE_WARNING, 'prelevement_vtsgn_sans_lot', '', $this->generateUrl('drev_lots', $this->document->addPrelevement(DRev::CUVE_ALSACE)));
  		}
  	}
  	
  	protected function controleWarningLotVtsgnSansPrelevement()
  	{
  		if (!$this->document->addPrelevement(DRev::CUVE_VTSGN) && $this->document->hasLots(true)) {
  			$this->addPoint(self::TYPE_WARNING, 'lot_vtsgn_sans_prelevement', '', $this->generateUrl('drev_degustation_conseil', array('sf_subject' => $this->document)));
  		}
  	}
  	
  	protected function controleWarningLotVtsgnSansControleExterne()
  	{
  		if (!$this->document->addPrelevement(DRev::BOUTEILLE_VTSGN) && $this->document->hasLots(true)) {
  			$this->addPoint(self::TYPE_WARNING, 'lot_vtsgn_sans_controle_externe', '', $this->generateUrl('drev_controle_externe', array('sf_subject' => $this->document)));
  		}
  	}

}