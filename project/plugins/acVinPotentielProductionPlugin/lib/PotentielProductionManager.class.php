<?php
class PotentielProductionManager
{
    protected $generator;
    
    public function __construct($identifiant_or_etablissement) 
    {
        $ppGenerator = sfConfig::get("app_potentielproductiongenerator");
        if (!$ppGenerator) {
            throw new sfException("potentielproductiongenerator non configuré dans l'app du projet");
        }
        if (!class_exists($ppGenerator)) {
           throw new sfException("La classe $ppGenerator n'existe pas");
        }
        $this->generator = new $ppGenerator($identifiant_or_etablissement);
        if (!($this->generator instanceof PotentielProductionGenerator)) {
            throw new sfException("La classe $ppGenerator doit étendre la classe PotentielProductionGenerator");
        }
    }
    
    public function calculate()
    {
        return $this->generator->infos();
    }
    
    public function getRevendicables()
    {
        return $this->generator->getRevendicables();
    }
    
    public function getSuperficies()
    {
        return $this->generator->getSuperficies();
    }
    
    public function respecteRegles()
    {
        return $this->generator->respecteRegles();
    }
    
    public function getGenerator()
    {
        return $this->generator;
    }
}