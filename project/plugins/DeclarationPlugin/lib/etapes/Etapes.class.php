<?php
abstract class Etapes
{
    const ETAPE_VALIDATION = 'VALIDATION';

    abstract public function getEtapesHash() ;
    abstract public function getRouteLinksHash() ;
    abstract public function getLibellesHash() ;

    public function __construct()
    {
    }

    public function getEtapes()
    {
        return array_keys($this->getEtapesHash());
    }

    public function exist($step) {
        $etapes = $this->getEtapes();

        return in_array($step, $etapes);
    }

    public function getRouteLink($step) {
        return $this->getRouteLinksHash()[$step];
    }

    public function getLibelle($step, $doc = null) {
        return $this->getLibellesHash()[$step];
    }

    public function getEtapeNum($step) {
        return isset($this->getEtapesHash()[$step]) ? $this->getEtapesHash()[$step] : 0;
    }

    public function getLast()
    {
        $a = $this->getEtapes();
        return $a[count($a)-1];
    }

    public function getFirst()
	{
		$etapes = $this->getEtapes();
		$first = null;
		foreach ($etapes as $etape) {
			$first = $etape;
			break;
		}
		return $first;
	}

	public function getNext($etape)
	{
		if (!$etape) {
			return $this->getFirst();
		}
		$etapes = $this->getEtapes();
		if (!in_array($etape, $etapes)) {
			throw new sfException('Etape inconnue');
		}
		$find = false;
		$next = $this->getDefaultStep();
		foreach ($etapes as $e) {
			if ($find) {
				$next = $e;
				break;
			}
			if ($etape == $e) {
				$find = true;
			}
		}
		return $next;
	}

	public function isGt($etapeToTest, $etape)
	{
        $etapes = $this->getEtapes();

        if (!$etapeToTest) {
			return false;
		}

		if (!in_array($etapeToTest, $etapes)) {
			throw new sfException('"'.$etapeToTest.'" : étape inconnu (arg1)');
		}

		if (!in_array($etape, $etapes)) {
			throw new sfException('"'.$etape.'" : étape inconnu (arg2)');
		}
		$key = array_search($etape, $etapes);
		$keyToTest = array_search($etapeToTest, $etapes);
		return ($keyToTest >= $key);
	}

	public function isLt($etapeToTest, $etape)
	{
		return !$this->isGt($etapeToTest, $etape);
	}

    public function isEtapeDisabled($etape, $doc) {

        return false;
    }


    public function getDefaultStep(){
      return self::ETAPE_VALIDATION;
    }

}
