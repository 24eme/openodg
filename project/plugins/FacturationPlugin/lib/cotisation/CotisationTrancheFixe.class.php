<?php
class CotisationTrancheFixe extends CotisationTranche
{
    protected function getConfigComplement() {

        return $this->getConfig()->complement;
    }

	public function getQuantite()
	{

        return 1;
	}

	public function getPrix()
	{
        
		return round(($this->getConfigPrix() * parent::getQuantite()) + $this->getConfigComplement(), self::PRECISION);
	}
}
