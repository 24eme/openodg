<?php

class DRevProduit extends BaseDRevProduit
{
	public function getConfig()
	{
		return $this->getCouchdbDocument()->getConfiguration()->get($this->getProduitHash());
	}

	public function getLibelle() {
		if(!$this->_get('libelle')) {
			$this->libelle = $this->getConfig()->getLibelleComplet();
			if($this->denomination_complementaire) {
				$this->libelle .= ' '.$this->denomination_complementaire;
			}
		}

		return $this->_get('libelle');
	}

	public function getLibelleComplet()
	{

		return $this->getLibelle();
	}

    public function getLibelleCompletHTML()
	{
        $libelle = $this->getConfig()->getLibelleComplet();

        if($this->denomination_complementaire) {
            $libelle .= ' <span class="text-muted">'.$this->denomination_complementaire.'</span>';
        }

        return $libelle;
    }

	public function getChildrenNode()
    {
        return $this->getCepages();
    }

    public function getProduitHash() {
			return $this->getParent()->getHash();
    }

    public function getTotalTotalSuperficie()
    {

		return $this->superficie_revendique + (($this->canHaveVtsgn()) ? $this->superficie_revendique_vtsgn : 0);
    }

    public function getTotalVolumeRevendique()
    {

		return $this->volume_revendique_total + (($this->canHaveVtsgn()) ? $this->volume_revendique_vtsgn : 0);
    }

	public function getTotalVciUtilise() {

		return $this->vci->complement + $this->vci->substitution + $this->vci->rafraichi + $this->vci->destruction;
	}

    public function getVolumeRevendiqueIssuVsi() {
		if(!$this->exist('volume_revendique_issu_vsi')) {
			return 0;
		}

		return $this->_get('volume_revendique_issu_vsi');
    }

	public function getVolumeRevendiqueIssuVciVsi() {
		$volume = $this->volume_revendique_issu_vci;
		if($this->exist('volume_revendique_issu_vsi')) {
			$volume += $this->volume_revendique_issu_vsi;
		}

		return $volume;
	}

	public function getVolumeRevendiqueRendement() {
		if($this->exist('volume_revendique_issu_mutage') && $this->volume_revendique_issu_mutage) {
			return ($this->volume_revendique_total - $this->volume_revendique_issu_mutage);
		}
		return $this->volume_revendique_total;
	}


	public function getPlafondStockVci() {

		return $this->recolte->superficie_total * $this->getConfig()->rendement_vci_total;
	}

	public function canHaveVtsgn() {

		return false;
	}

	public function hasVci($saisie = false) {
		if ($saisie) {
			return ($this->vci->stock_precedent || $this->vci->destruction || $this->vci->complement || $this->vci->substitution || $this->vci->rafraichi || $this->vci->constitue  || $this->vci->ajustement);
		}
		return ($this->vci->stock_precedent !== null || $this->vci->destruction !== null || $this->vci->complement !== null || $this->vci->substitution !== null || $this->vci->rafraichi !== null || $this->vci->constitue !== null || $this->vci->ajustement !== null);
	}

	public function hasVciDetruit() {
		return ($this->vci->destruction && $this->vci->destruction > 0)? true : false;
	}

    public function isActive()
    {

		return true;
    }

    public function isCleanable() {

        if(!$this->isActive()) {

            return true;
        }

        if ($this->exist('volume_revendique_seuil') && $this->volume_revendique_seuil > 0) {
            return false;
        }

		if ($this->recolte->superficie_total === null && $this->recolte->volume_total === null && !$this->superficie_revendique && !$this->volume_revendique_total && !$this->vci->stock_precedent && !$this->vci->stock_final ) {

			return true;
		}

        return false;
    }

	public function update($params = array()) {
		$this->vci->stock_final = null;
		$this->volume_revendique_issu_vci = null;
		if($this->hasVci()) {
			$this->volume_revendique_issu_vci = ((float) $this->vci->complement) + ((float) $this->vci->substitution) + ((float) $this->vci->rafraichi);
			$this->vci->stock_final = ((float) $this->vci->rafraichi) + ((float) $this->vci->constitue) + ((float) $this->vci->ajustement) + ((float) $this->vci->substitution);
		}
        if($this->recolte->exist('vsi') && $this->recolte->vsi) {
            $this->add('volume_revendique_issu_vsi', $this->recolte->vsi);
        }
		$this->volume_revendique_total = ((float) $this->volume_revendique_issu_recolte) + ((float) $this->volume_revendique_issu_vci_vsi + (float) $this->volume_revendique_issu_mutage);

		if ($this->hasReserveInterpro()) {
			$this->add('dont_volume_revendique_reserve_interpro', $this->getVolumeReserveInterpro());
		}

	}

	public function isHabilite() {
		$date = date('Y-m-d');
		if($this->document->isValidee()){
			$date = $this->document->validation;
		}
		$hab = HabilitationClient::getInstance()->findPreviousByIdentifiantAndDate($this->document->identifiant, $date);
		if (!$hab) {
			return false;
		}
		return $hab->isHabiliteFor($this->getProduitHash(), HabilitationClient::ACTIVITE_VINIFICATEUR);
	}

	public function getCodeCouleur()
	{
		if (preg_match('/\/rouge\//', $this->getHash())) {
			return 1;
		}
		if (preg_match('/\/rose\//', $this->getHash())) {
			return 2;
		}
		if (preg_match('/\/blanc\//', $this->getHash())) {
			return 3;
		}
		return null;
	}

    public function getCepage() {
        return $this->getParent();
    }

    public function getAppellation() {
        return $this->getCepage()->getAppellation();
    }

	public function canCalculTheoriticalVolumeRevendiqueIssuRecolte() {
        if(!DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire() && !$this->getCepage()->hasProduitsSansDonneesRecolte()) {
            if(!$this->superficie_revendique || $this->getSommeProduitsCepage('superficie_revendique') != $this->superficie_revendique) {

    			return false;
    		}

            if($this->getSommeProduitsCepage('recolte/volume_total') == $this->getSommeProduitsCepage('recolte/volume_sur_place')) {

                return true;
            }

            if(round($this->getSommeProduitsCepage('recolte/volume_sur_place'), 2) == round($this->getSommeProduitsCepage('recolte/recolte_nette') + $this->getSommeProduitsCepage('recolte/usages_industriels_total'), 2)) {

                return true;
            }

            if(round($this->getSommeProduitsCepage('recolte/volume_sur_place'), 2) == round($this->getSommeProduitsCepage('recolte/recolte_nette') + $this->getSommeProduitsCepage('recolte/usages_industriels_sur_place'), 2)) {

                return true;
            }

            return false;
        }

        if(!$this->hasDonneesRecolte()) {

            return false;
        }

        if(round($this->recolte->volume_total, 2) == round($this->recolte->volume_sur_place, 2)) {
			return true;
		}

		if(round($this->recolte->volume_sur_place, 2) == round($this->recolte->recolte_nette + $this->recolte->usages_industriels_total, 2)) {

			return true;
		}

        if(round($this->recolte->volume_sur_place, 2) == round($this->recolte->recolte_nette + $this->recolte->usages_industriels_sur_place, 2)) {

			return true;
		}

        if(round($this->recolte->volume_sur_place, 2) == round($this->recolte->recolte_nette, 2)) {
			return true;
		}

		return false;
	}

	public function getTheoriticalVolumeRevendiqueIssuRecole() {
        if(!DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire() && !$this->getCepage()->hasProduitsSansDonneesRecolte()) {
    		if($this->getSommeProduitsCepage('vci/rafraichi') + $this->getSommeProduitsCepage('vci/substitution')) {
				return $this->getSommeProduitsCepage('recolte/recolte_nette') - $this->getSommeProduitsCepage('vci/rafraichi') - $this->getSommeProduitsCepage('vci/substitution');
			} else {
				return $this->getSommeProduitsCepage('recolte/recolte_nette');
			}
		}

        if($this->vci->rafraichi + $this->vci->substitution)
			return $this->recolte->recolte_nette - $this->vci->rafraichi - $this->vci->substitution;
		else
			return $this->recolte->recolte_nette;
	}

	public function getRendementVci(){
		if(!$this->superficie_revendique) {

			return null;
		}
		if(!$this->exist('vci') || !$this->vci->exist('constitue')) {

			return null;
		}

		return $this->vci->constitue / $this->superficie_revendique;
	}
	public function getRendementVciTotal(){
		if(!$this->superficie_revendique) {

			return null;
		}
		if(!$this->exist('vci') || !$this->vci->exist('stock_final')) {

			return null;
		}

		return $this->vci->stock_final / $this->superficie_revendique;
	}


	public function getRendementEffectif(){
		if(!$this->superficie_revendique) {

			return null;
		}

		return $this->getVolumeRevendiqueRendement() / $this->superficie_revendique;
	}

	public function isDepassementRendementEffectif(){
		$rendementLocal = $this->getRendementEffectif();
		return ($this->getConfig()->getRendement() !== null && (round(($rendementLocal), 2) > round($this->getConfig()->getRendement(), 2)));
	}

	public function getRendementEffectifHorsVCI(){
		if(!$this->superficie_revendique) {

			return null;
		}

		return $this->volume_revendique_issu_recolte / $this->superficie_revendique;
	}


	public function getRendementDrL5(){
		if(!$this->exist('recolte') || !$this->recolte->exist('volume_total') || !$this->recolte->exist('superficie_total')) {

			return null;
		}
		if ($this->recolte->superficie_total) {
			return $this->recolte->volume_total / $this->recolte->superficie_total;
		}
		return 0;
	}

	public function isDepassementRendementDrL5(){
		$rendementLocal = $this->getRendementDrL5();
		return ($this->getConfig()->getRendementDrL5() !== null && (round(($rendementLocal), 2) > round($this->getConfig()->getRendementDrL5(), 2)));
	}

	public function getRendementDrL15(){
		if(!$this->exist('recolte') || !$this->recolte->exist('volume_sur_place_revendique') || !$this->recolte->exist('superficie_total')) {

			return null;
		}
		if ($this->recolte->superficie_total) {
			return $this->recolte->volume_sur_place_revendique / $this->recolte->superficie_total;
		}
		return 0;
	}

	public function isDepassementRendementDrL15(){
		$rendementLocal = $this->getRendementDrL15();
		return ($this->getConfig()->getRendementDrL15() !== null && (round(($rendementLocal), 2) > round($this->getConfig()->getRendementDrL15(), 2)));
	}

	public function hasDonneesRecolte() {
       if ($this->exist('recolte')) {
           foreach ($this->recolte as $k => $v) {
               //Pour les apporteurs en cave coop => a des volumes mais pas de récolte pour la cave particulière
               if (in_array($k, ['usages_industriels_total', 'volume_total', 'superficie_total', 'recolte_nette', 'vci_constitue'])) {
                   continue;
               }
               if ($v && $v > 0) {
                   return true;
               }
           }
        }

	    return false;
	}

	public function validateOdg($date = null){
		if(is_null($date)) {
				$date = date('Y-m-d');
		}
		$this->add('validation_odg',$date);
	}


	public function setStatutOdg($statut) {
		if (!$this->exist('statut_odg')) {
			$this->add('statut_odg');
		}
		return $this->_set('statut_odg', $statut);
	}

	public function getStatutOdg() {
		if (!$this->exist('statut_odg')) {
			return null;
		}
		return $this->_get('statut_odg');
	}

	public function isValidateOdg(){
		return ($this->exist('validation_odg') && $this->validation_odg);
	}

    public function hasReserveInterpro() {
        return ($this->getVolumeReserveInterpro());
    }

	protected function getVolumeReserveInterproAndButoir() {
		if (!$this->getConfig()->hasRendementReserveInterpro()) {
			return 0;
		}
		$diff = $this->volume_revendique_total - ($this->superficie_revendique * $this->getConfig()->getRendementReserveInterpro());
		if ($diff <= $this->getConfig()->getRendementReserveInterproMin()) {
			return 0;
		}
		return $diff;
	}

    public function getVolumeReserveInterpro() {
        if (!$this->getConfig()->hasRendementReserveInterpro()) {
            return 0;
        }
        $diff = $this->getVolumeReserveInterproAndButoir();
		$diff_butoir = $this->volume_revendique_total - ($this->superficie_revendique * $this->getConfig()->getRendement());
		if ($diff_butoir > 0) {
			return $diff - $diff_butoir;
		}
		return $diff;
    }

	public function getVolumeRevendiqueCommecialisable() {
		return $this->volume_revendique_total - $this->getVolumeReserveInterproAndButoir();
	}

	public function getSommeProduitsCepage($hash) {
		return $this->getCepage()->getSommeProduits($hash);
	}

    public function hasVolumeOrSuperficieRevendicables() {
        return $this->recolte->volume_sur_place || $this->volume_revendique_total || $this->superficie_revendique;

    }

    public function getRegion() {

        return RegionConfiguration::getInstance()->getOdgRegion($this->getHash());
    }
}
