<?php
/**
 * Model for DegustationMouvementLots
 *
 */

class DegustationMouvementLots extends BaseDegustationMouvementLots
{
    const SPECIFICITE_PASSAGES = 'Xème passage';

    public function updatePassage($plus = 1, $defaut = 2) {
      $nb = $defaut;

      if (preg_match("/.*([0-9]+)".str_replace('X', '', self::SPECIFICITE_PASSAGES).".*/", $this->specificite, $m)) {
        $nb = ((int)$m[1]) + $plus;
      }

      if ($this->specificite === null) {
          $this->specificite = str_replace('X', $nb, self::SPECIFICITE_PASSAGES);
      } else {
          $this->specificite = (strpos($this->specificite, str_replace('X', '', self::SPECIFICITE_PASSAGES)) !== false)
              ? str_replace($nb - 1, $nb, $this->specificite)                              // il y a déjà un X passage
              : $this->specificite.', '.str_replace('X', $nb, self::SPECIFICITE_PASSAGES); // il n'y a pas de passage
      }
    }

}
