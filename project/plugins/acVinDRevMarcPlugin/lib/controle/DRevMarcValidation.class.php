<?php
class DRevMarcValidation extends DocumentValidation
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
  		$this->addControle(self::TYPE_WARNING, 'dr_volume', 'Le volume revendiqué est différent de celui déclaré dans votre DR.');
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
    }
  	
  

}