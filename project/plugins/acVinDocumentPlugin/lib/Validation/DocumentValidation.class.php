<?php

abstract class DocumentValidation
{
    protected $types = array(
        'engagement',
        'vigilance',
        'erreur',
    );

    protected $document;

    protected $controles = array();

    protected $points = array();

    protected $noticeVigilance;

    public function __construct($document, $options = null)
    {
        $this->document = $document;
        $this->noticeVigilance = true;

        foreach($this->types as $type) {
            $this->points[$type] = array();
        }

        $this->configure();
        $this->controle();
    }

    abstract public function configure();
    abstract public function controle();

    public function addControle($type, $code, $message) {
        if (!$this->isTypeExist($type)) {

            throw new sfException(sprintf("Le type de controle '%s' n'existe pas", $type));
        }

        $this->controles[sprintf("%s-%s", $type, $code)] = new DocumentValidationControle($type, $code, $message);
    }

    public function hasControle($type, $code) {

        return array_key_exists(sprintf("%s-%s", $type, $code), $this->controles);
    }

    public function findControle($type, $code)
    {

        return $this->controles[sprintf("%s-%s", $type, $code)];
    }

    public function addPoint($type, $code, $info, $lien = null) {
        $controle = $this->findControle($type, $code);

        if(!$controle) {
            throw new sfException(sprintf("Le controle de type '%s' pour le code '%s' n'existe pas", $type, $code));
        }

        $point_controle = clone $controle;
        $point_controle->setInfo($info);
        $point_controle->setLien($lien);

        $this->points[$type][] = $point_controle;

        return $point_controle;
    }

    public function getPoints($type) {

        return $this->points[$type];
    }

    public function getPointsByCodes($type) {
        $points = array();
        foreach($this->getPoints($type) as $controle) {
            $points[$controle->getCode()][] = $controle;
        }

        return $points;
    }

    public function getPointsByCode($type, $code){
      $typesPoints = $this->getPointsByCodes($type);
      return isset($typesPoints[$code])? $typesPoints[$code] : array();
    }

    public function getEngagements()
    {
        return $this->getPoints('engagement');
    }

    public function getVigilances()
    {

        return $this->getPoints('vigilance');
    }

    public function getErreurs()
    {

        return $this->getPoints('erreur');
    }

    public function hasEngagements()
    {
        return count($this->getEngagements()) > 0;
    }

    public function hasVigilances()
    {
        return count($this->getVigilances()) > 0;
    }

    public function hasErreurs()
    {
        return count($this->getErreurs()) > 0;
    }

    public function hasPoints()
    {
        return $this->hasVigilances() || $this->hasErreurs();
    }

    public function isValide() {

        return !($this->hasErreurs());
    }

    protected function generateUrl($route, $params = array(), $absolute = false)
    {
        return sfContext::getInstance()->getRouting()->generate($route, $params, $absolute);
    }

    protected function isTypeExist($type) {

        return in_array($type, $this->types);
    }

    public function printNoticeVigilance()
    {
    	return $this->noticeVigilance;
    }
}
