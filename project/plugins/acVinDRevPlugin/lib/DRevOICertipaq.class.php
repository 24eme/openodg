<?php
class DRevOICertipaq
{
	public $drev;
	public $context;
	public $regions;


	public function __construct(DRev $drev, $context = null) {
		if (!$drev->validation_odg) {
			throw new sfException('DRev non validée');
		}
		$this->drev = $drev;
		$this->context = ($context) ? $context : sfContext::getInstance();
		$this->regions = sfConfig::get('app_oi_regions');
	}

	public function send()
	{
		$sended = array();
        $produits = array();
		if($this->regions){
			$regionSended = array();
			foreach ($this->regions as $region => $regionOpt) {
                foreach($this->drev->declaration->getProduits($region) as $produit) {
                    $this->sendProduit($produit);
                }
				$sended[] = $region;
			}
		}else{
			$sended[] = null;
            foreach($this->drev->declaration->getProduits() as $produit) {
                $this->sendProduit($produit);
            }
		}
        if(count($sended)){
            if (!$this->drev->exist('envoi_oi') || ! $this->drev->envoi_oi) {
                $this->drev->add('envoi_oi', date('c'));
                $this->drev->save();
            }
        }
		return $sended;
	}

    public function sendProduit(){
        //TODO
	}

}
