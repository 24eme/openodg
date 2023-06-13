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

    public function getPreviousLink($step) {
        return $this->getRouteLink($this->getPrevious($step));
    }

    public function getNextLink($step) {
        return $this->getRouteLink($this->getNext($step));
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

	private function getNextPrev($etape, $next=true)
	{
		if (!$etape) {
			return $this->getFirst();
		}
		$etapes = $this->getEtapes();

        if (false !== $index_etape = array_search($etape, $etapes)) {
            if ($next) {
                return ($index_etape + 1 <= count($etapes)) ? $etapes[$index_etape+1] : $this->getLast();
            }
            else {
                return ($index_etape - 1 >= 0) ? $etapes[$index_etape-1] : $this->getFirst();
            }
        }

        throw new sfException('Etape inconnue');
	}

    public function getPrevious($etape)
	{
        return $this->getNextPrev($etape, false);
    }

    public function getNext($etape)
	{
        return $this->getNextPrev($etape, true);
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
