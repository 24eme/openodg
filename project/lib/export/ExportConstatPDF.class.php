<?php

class ExportConstatPDF extends ExportPDF {

    protected $constats = null;
    protected $constatNode = null;
    protected $constat = null;

    public function __construct($constats,$constatNode, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->constats = $constats;
        $this->constatNode = $constatNode;
        $this->constat = $this->constats->constats->get($this->constatNode);
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Date'));
        if (!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $this->printable_document->addPage($this->getPartial('constats/pdf', array('constats' => $this->constats,'constat' => $this->constats->constats->get($this->constatNode))));
      
    }

    protected function getHeaderTitle() {
        return sprintf("Rapport de constat du %s",  Date::francizeDate($this->constat->date_signature));
    }

    protected function getHeaderSubtitle() {
        $tourneeRaisin = TourneeClient::getInstance()->findTourneeByIdRendezvous($this->constat->rendezvous_raisin);
        $tourneeVolume = TourneeClient::getInstance()->findTourneeByIdRendezvous($this->constat->rendezvous_volume);
        $agentRaisin = $tourneeRaisin->getAgentUniqueObj()->prenom . ' ' . $tourneeRaisin->getAgentUniqueObj()->nom;
        $agentVolume = $tourneeVolume->getAgentUniqueObj()->prenom . ' ' . $tourneeVolume->getAgentUniqueObj()->nom;
        
        $agentsName = 'Fait par : '.$agentRaisin;
        if($agentRaisin != $agentVolume){
            $agentsName .= " / ".$agentVolume;
        }
        $header_subtitle = sprintf("%s\n%s\n", $this->constats->raison_sociale,$agentsName);
        
        return $header_subtitle;
    }

    protected function getConfig() {

        return new ExportDRevPDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->constats,$this->constatNode, true, false);
    }

    public static function buildFileName($constats, $constatNode, $with_rev = false) {
        $filename = sprintf("CONSTATS_%s_%s_%s", $constats->identifiant, $constats->campagne,$constatNode);

        $declarant_nom = strtoupper(KeyInflector::slugify($constats->raison_sociale));
        $filename .= '_' . $declarant_nom;

        if ($with_rev) {
            $filename .= '_' . $constats->_rev;
        }

        return $filename . '.pdf';
    }

}
