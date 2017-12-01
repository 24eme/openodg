<?php
class DRevOI
{
	public $drev;
	public $context;
	
	public function __construct(DRev $drev, $context = null) {
		if (!$drev->validation_odg) {
			throw new sfException('DRev non validée');
		}
		$this->drev = $drev;
		$this->context = ($context) ? $context : sfContext::getInstance();
	}
	
	public function send()
	{
		$headers = array(
			"Content-Type: text/xml;charset=UTF-8",
			"SOAPAction: http://".sfConfig::get('app_oi_domain_action')."/CreationDrev"
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, sfConfig::get('app_oi_url_http'));
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getXml());
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		if ($output === false || $info['http_code'] != 200) {
			$result = "No cURL data returned (HTTP Code : ". $info['http_code']. ")";
			if (curl_error($ch)) {
				$result .= "\n". curl_error($ch);
			}
			throw new sfException($result);
		} else {
			$stderr = fopen("php://stderr", "w"); 
			fwrite($stderr, "Retour envoi oi : ".$output);
		}
		$this->drev->add('envoi_oi', date('c'));
		$this->drev->save();
		curl_close($ch);
	}
	
	protected function getXml() {
		return $this->context->getController()->getAction('drev', 'main')->getPartial('drev/xml', array('drev' => $this->drev));
	}
}