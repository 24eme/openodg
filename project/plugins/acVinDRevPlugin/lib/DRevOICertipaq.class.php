<?php
class DRevOICertipaq
{
	public $drev;
	public $context;
	public $regions;
	private $res;


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
        $api_res = array();
		if($this->regions){
			$regionSended = array();
			foreach ($this->regions as $region => $regionOpt) {
                foreach($this->drev->declaration->getProduits($region) as $produit) {
                    $api_res[$produit->getHash()] = $this->sendProduit($produit);
                }
				$sended[] = $region;
			}
		}else{
			$sended[] = null;
            foreach($this->drev->declaration->getProduits() as $produit) {
                $api_res[$produit->getHash()] = $this->sendProduit($produit);
            }
		}
        if(count($api_res)){
            if (!$this->drev->exist('envoi_oi') || ! $this->drev->envoi_oi) {
                $this->drev->add('envoi_oi', date('c'));
                $this->drev->save();
            }
        }
        $this->res = $api_res;
        return $api_res;
	}

    public function sendProduit($produit){
        try {
         $res = CertipaqDRev::getInstance()->createDRevLigne($produit);
         return ['success' => true, 'res' => $res, 'erreor' => null, 'debug' => CertipaqDRev::getInstance()->getLastQuery()];
        }catch(sfException $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'res' => null, 'debug' => CertipaqDRev::getInstance()->getLastQuery()];
        }
	}

    public function storeResultInDrev() {
        $this->drev->add('_attachments');
        $tmpfname = tempnam("/tmp", "CertipaDREV_".date('c').'_').'.json';
        file_put_contents($tmpfname, json_encode($this->res));
        $this->drev->storeAttachment($tmpfname, "text/json");
    }

    public function getDebugInfo() {
        if (!$this->drev->exist('_attachments')) {
            return array();
        }
        $debug_id = '00';
        $debug_info = null;
        foreach($this->drev->_attachments as $id => $a) {
            if (strpos($id, 'CertipaDREV_2') === false) {
                continue;
            }
            if ($debug_id > $id) {
                continue;
            }
            $debug_id = $id;
            $debug_info = $a;
        }
        $json = file_get_contents($this->drev->getAttachmentUri($debug_id));
        return json_decode($json);
;
    }

}
