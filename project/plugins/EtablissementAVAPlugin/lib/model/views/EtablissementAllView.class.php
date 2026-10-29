<?php

class EtablissementAllView extends acCouchdbView
{
	const KEY_INTERPRO_ID = 0;
	const KEY_STATUT = 1;
  const KEY_FAMILLE = 2;
  const KEY_SOCIETE_ID = 3;
  const KEY_ETABLISSEMENT_ID = 4;
	const KEY_NOM = 5;
	const KEY_IDENTIFIANT = 6;
	const KEY_CVI = 7;
  const KEY_REGION = 8;

  const VALUE_RAISON_SOCIALE = 0;
  const VALUE_ADRESSE = 1;
  const VALUE_COMMUNE = 2;
  const VALUE_CODE_POSTAL = 3;
  const VALUE_NO_ACCISES = 4;
  const VALUE_CARTE_PRO = 5;
  const VALUE_EMAIL = 6;
  const VALUE_TELEPHONE = 7;
  const VALUE_FAX = 8;
  const VALUE_RECETTE_LOCALE_SOCIETE_ID = 9;
  const VALUE_RECETTE_LOCALE_NOM = 10;

	public static function getInstance() {

        return acCouchdbManager::getView('etablissement', 'all', 'Etablissement');
    }

	public function getAll() {
		return EtablissementClient::getInstance()->getAll();
    }

    public function findByInterpro($interpro) {

        return $this->client->startkey(array($interpro))
                            ->endkey(array($interpro, array()))
			    ->reduce(false)
			    ->getView($this->design, $this->view);
    }

    public function findByInterproAndStatut($interpro, $statut, $filter = null, $limit = null) {
      return $this->findByInterproStatutAndFamilles($interpro, $statut, array(), $filter, $limit);
    }

    public function findByInterproAndFamilles($interpro, array $familles, $filter = null, $limit = null) {
      return $this->findByInterproStatutsAndFamilles($interpro, null, $familles, $filter, $limit);
    }

    public function findByInterproStatutAndFamilles($interpro, $statut, array $familles, $filter = null, $limit = null) {
      return $this->findByInterproStatutsAndFamilles($interpro, array($statut), $familles, $filter, $limit);
    }

    public function findByInterproAndFamille($interpro, $famille, $filter = null, $limit = null) {
      return $this->findByInterproStatutsAndFamilles($interpro, array(), array($famille), $filter, $limit);
    }

    public function findByInterproStatutsAndFamilles($interpro, array $statuts, array $familles, $filter = null, $limit = null) {
      return $this->findByInterproStatutsAndFamillesVIEW($interpro, $statuts, $familles, $filter, $limit) ;
    }

    private function findByInterproStatutsAndFamillesVIEW($interpro, array $statuts, array $familles, $filter = null, $limit = null) {
		throw new sfException("Not in AVA");
    }

    public function findByInterproStatutAndFamille($interpro, $statut, $famille, $filter = null, $limit = null) {
		throw new sfException("Not in AVA");
    }

    private function findByInterproStatutAndFamilleELASTIC($interpro, $statut, $famille, $query = null, $limit = 100) {
		throw new sfException("Not in AVA");
    }

    private function elasticRes2View($resultset) {
		throw new sfException("Not in AVA");
    }

    public function findByInterproStatutAndFamilleVIEW($interpro, $statut, $famille, $filter = null, $limit = null) {
		if ($interpro != 'INTERPRO-declaration' || $statut != 'ACTIF' || $famile || $filter || $limit) {
			throw new sfException("Not in AVA");
		}
		$etablissements = array();
		foreach(EtablissementClient::getInstance()->getAll() as $e) {
			$e = (object) $e;
			$e->id = $e->_id;
			$etablissements[] = $e;
		}
		return $etablissements;
    }

    public function findByEtablissement($identifiant) {
		throw new sfException("Not in AVA");
    }

		public static function makeLibelle($row) {
            $libelle = '🏠 ';

			if ($nom = $row->key[self::KEY_NOM]) {
				$libelle .= Anonymization::hideIfNeeded($nom);
			}

			$libelle .= ' ('.$row->key[self::KEY_IDENTIFIANT];

			if (isset($row->key[self::KEY_CVI]) && $cvi = $row->key[self::KEY_CVI]) {
				$libelle .= ' / '.$cvi;
			}

			if (isset($row->value[self::VALUE_NO_ACCISES]) && $numAccises = $row->value[self::VALUE_NO_ACCISES]) {
				$libelle .= ' / '.$numAccises . ') ';
			}else {
				$libelle .= " / sans n° d'accise ) ";
			}

			if (isset($row->key[self::KEY_FAMILLE]))
				$libelle .= $row->key[self::KEY_FAMILLE];

			if (isset($row->value[self::VALUE_COMMUNE]))
				$libelle .= ' '.$row->value[self::VALUE_COMMUNE];

			if (isset($row->value[self::VALUE_CODE_POSTAL]))
				$libelle .= ' '.$row->value[self::VALUE_CODE_POSTAL];

			$libelle .= " (Etablissement)";

			return trim($libelle);
		}

}
