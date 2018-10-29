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

	public function getPlafondStockVci() {

		return $this->superficie_revendique * $this->getConfig()->rendement_vci_total;
	}

	public function canHaveVtsgn() {

		return false;
	}

	public function hasVci($saisie = false) {
		if ($saisie) {
			return ($this->vci->stock_precedent || $this->vci->destruction || $this->vci->complement || $this->vci->substitution || $this->vci->rafraichi || $this->vci->constitue);
		}
		return ($this->vci->stock_precedent !== null || $this->vci->destruction !== null || $this->vci->complement !== null || $this->vci->substitution !== null || $this->vci->rafraichi !== null || $this->vci->constitue !== null);
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
				if ($this->recolte->superficie_total == null) {
					return true;
				}

        return false;
    }

	public function update($params = array()) {
		$this->vci->stock_final = null;
		$this->volume_revendique_issu_vci = null;
		if($this->hasVci()) {
			$this->volume_revendique_issu_vci = ((float) $this->vci->complement) + ((float) $this->vci->substitution) + ((float) $this->vci->rafraichi);
			$this->vci->stock_final = ((float) $this->vci->rafraichi) + ((float) $this->vci->constitue);
		}
		$this->volume_revendique_total = ((float) $this->volume_revendique_issu_recolte) + ((float) $this->volume_revendique_issu_vci);
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

	public function getTheoriticalVolumeRevendiqueIssuRecole() {
		return $this->recolte->recolte_nette - $this->vci->rafraichi - $this->vci->substitution;
	}

}
