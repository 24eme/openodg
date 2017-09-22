<?php
/**
 * Model for HabilitationCertification
 *
 */

class HabilitationCertification extends BaseHabilitationCertification {
  
  public function getChildrenNode()
  {
      return $this->getGenres();
  }

  public function getGenres()
  {
      return $this->filter('^genre');
  }

}
