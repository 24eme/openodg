<?php
class DRevValidation extends DocumentValidation
{
    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';
    const TYPE_ENGAGEMENT = 'engagement';
	
	public function __construct($document, $options = null)
    {
        parent::__construct($document, $options);
        $this->noticeVigilance = true;
    }
    
  	public function configure() 
  	{
  		/*
  		 * Warning
  		 */

  		$this->addControle(self::TYPE_WARNING, 'dr_surface', 'la surface revendiquée est différente de celle déclarée de votre DR.');
      	$this->addControle(self::TYPE_WARNING, 'dr_volume', 'le volume revendiqué est différent de celui déclaré de votre DR.');

  		$this->addControle(self::TYPE_WARNING, 'dr_cepage', 'Vous ne déclarez aucun lot pour un cépage présent dans votre DR'); // !!!!
      	//$this->addControle(self::TYPE_WARNING, 'lot_vtsgn_sans_prelevement', 'Vous avez déclaré des lots VT/SGN sans spécifier de période de prélèvement.');
      
  		/*
  		 * Error
  		 */
    	$this->addControle(self::TYPE_ERROR, 'revendication_incomplete', 'Vous devez saisir la superficie et le volume pour vos produits revendiqués');
    	$this->addControle(self::TYPE_ERROR, 'volume_revendique_incorrect', 'le volume revendiqué ne peut pas être inférieur au volume sur place déduit des usages industriels et supérieur au volume sur place.');
    	$this->addControle(self::TYPE_ERROR, 'prelevement', 'Vous devez saisir une semaine de prélèvement');
    	$this->addControle(self::TYPE_ERROR, 'revendication_sans_lot', 'Vous avez revendiqué des produits sans spécifier de lots');
		$this->addControle(self::TYPE_ERROR, 'controle_externe_vtsgn', 'Vous devez renseigner une semaine et le nombre total de lots pour le VT/SGN');
		
		/*
  		 * Engagement
  		 */
    	$this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_DR, 'Joindre un copie de votre Déclaration de Récolte');
    	$this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV, 'Joindre une copie de votre SV11 ou SV12');
    	$this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_PRESSOIR, 'Joindre une copie de votre carnet de pressoir');
    	
  	}

    public function controle()
    {
      $revendicationProduits = $this->document->declaration->getProduits();
      foreach ($revendicationProduits as $hash => $revendicationProduit) {
        $this->controleWarningDrSurface($revendicationProduit);
        $this->controleWarningDrVolume($revendicationProduit);
        $this->controleErrorRevendicationIncomplete($revendicationProduit);
        $this->controleErrorVolumeRevendiqueIncorrect($revendicationProduit);
      	$this->controleEngagementPressoir($revendicationProduit);
      }

      $this->controleErrorPrelevement(DRev::CUVE_ALSACE);
      $this->controleErrorPrelevement(DRev::BOUTEILLE_ALSACE);
      $this->controleErrorPrelevement(DRev::BOUTEILLE_GRDCRU);
      
      $this->controleErrorRevendicationSansLot(DRev::CUVE_ALSACE);
      $this->controleErrorRevendicationSansLot(DRev::CUVE_GRDCRU);
      
      $this->controleEngagementDr();
      $this->controleEngagementSv();
    }
    
    protected function controleEngagementDr()
    {
    	if (!$this->document->isNonRecoltant()) {
  			$this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_DR, ''); 
  		}
    }
    
    protected function controleEngagementSv()
    {
    	if (!$this->document->isNonRecoltant()) {
  			$this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_SV, ''); 
  		}
    }
    
    protected function controleEngagementPressoir($produit)
    {
    	if ($produit->volume_revendique !== null && $produit->getAppellation()->getKey() == 'appellation_CREMANT') {
  			$this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_PRESSOIR, ''); 
  		}
    }
  	
  	protected function controleWarningDrSurface($produit)
  	{
      if(!$this->document->hasDR()) {

        return;
      }
      
  		if (
  			$produit->superficie_revendique !== null && 
  			$produit->superficie_revendique !== null && 
  			$produit->superficie_revendique != $produit->detail->superficie_total
  		) {
                    $appellation_hash = str_replace('/','-',$produit->getHash()).'-surface';
  			$this->addPoint(self::TYPE_WARNING, 'dr_surface', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document, 'appellation' => $appellation_hash))); 
  		}
  	}
  	
  	protected function controleWarningDrVolume($produit)
  	{

      if(!$this->document->hasDR()) {

        return;
      }

  		if (
  			$produit->volume_revendique !== null && 
  			$produit->detail->volume_sur_place_revendique !== null && 
  			$produit->volume_revendique != $produit->detail->volume_sur_place_revendique
  		) {
                    $appellation_hash = str_replace('/','-',$produit->getHash()).'-volume';
  			$this->addPoint(self::TYPE_WARNING, 'dr_volume', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document,'appellation' => $appellation_hash))); 
  		}
  	}
  	
  	protected function controleErrorRevendicationIncomplete($produit)
  	{
      if($this->document->isNonRecoltant()) {

        return;
      }
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
                    $appellation_hash =  str_replace('/','-',$produit->getHash()).'-volume';
  			$this->addPoint(self::TYPE_ERROR, 'volume_revendique_incorrect', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document, 'appellation' => $appellation_hash)));
  		}
  		
  		if (
  			$produit->volume_revendique !== null && 
  			$produit->detail->volume_sur_place !== null && 
  			$produit->volume_revendique > $produit->detail->volume_sur_place
  		) {
                    $appellation_hash = str_replace('/','-',$produit->getHash()).'-volume';
  			$this->addPoint(self::TYPE_ERROR, 'volume_revendique_incorrect', $produit->getLibelleComplet(), $this->generateUrl('drev_revendication', array('sf_subject' => $this->document, 'appellation' => $appellation_hash)));
  		}
  	}

    protected function controleErrorPrelevement($key)
    {
      if(!$this->document->prelevements->exist($key)) {

          return;
      }

      $prelevement = $this->document->prelevements->get($key);

      if (!$prelevement->date) {
        $this->addPoint(self::TYPE_ERROR, 
                        'prelevement', 
                        sprintf("%s - %s", $prelevement->libelle, $prelevement->libelle_produit), 
                        $this->generateUrl('drev_degustation_conseil', array('sf_subject' => $this->document)));
      }
    }

    protected function controleErrorRevendicationSansLot($key)
    {
        if(!$this->document->prelevements->exist($key)) {

            return;
        }

        $prelevement = $this->document->prelevements->get($key);

        if (!$prelevement->hasLots()) {
          $this->addPoint(self::TYPE_ERROR, 
                          'revendication_sans_lot', 
                          sprintf("%s - %s", $prelevement->libelle, $prelevement->libelle_produit), 
                          $this->generateUrl('drev_lots', $this->document->prelevements->get($key)));
        }
    }
  	
  	protected function controleWarningLotVtsgnSansPrelevement()
  	{
  		if (!$this->document->addPrelevement(DRev::CUVE_VTSGN) && $this->document->hasLots(true)) {
  			$this->addPoint(self::TYPE_WARNING, 'lot_vtsgn_sans_prelevement', '', $this->generateUrl('drev_degustation_conseil', array('sf_subject' => $this->document)));
  		}
  	}

}