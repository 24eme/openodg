<?php
class CompteLdap extends acVinLdap {

  public function saveCompte($compte, $verbose = 0)
    {
      $info = $this->info($compte);
      if ($verbose) {
	echo "save : ";
	print_r($info);
      }
      return $this->save(self::getIdentifiant($compte), $info);
    }

    /**
     *
     * @param _Compte $compte
     * @return bool
     */
    public function deleteCompte($compte, $verbose = 0) {
      if ($verbose) {
	       echo self::getIdentifiant($compte)." deleted\n";
      }
      $id = $compte;
      if (!is_string($compte)) {
        $id = self::getIdentifiant($compte);
      }
      return $this->delete($id);
    }

    protected static function getIdentifiant($compte) {
        if ($compte->exist('login')) {
            return $compte->login;
        }
        if ($compte->isSocieteContact()) {
	        return $compte->getSociete()->identifiant;
        }else{
	        return $compte->identifiant;
        }
    }

    /**
     *
     * @param _Compte $compte
     * @return array
     */
    protected function info($compte)
    {
      $info = array();
      $info['uid']              = self::getIdentifiant($compte);
      $info['cn']               = $compte->nom_a_afficher;
      $info['objectClass'][0]   = 'top';
      $info['objectClass'][1]   = 'person';
      $info['objectClass'][2]   = 'posixAccount';
      $info['objectClass'][3]   = 'inetOrgPerson';
      $info['loginShell']       = '/bin/bash';
      $info['uidNumber']        = (int)self::getIdentifiant($compte);
      $info['gidNumber']        = '1000';
      $info['homeDirectory']    = '/home/'.self::getIdentifiant($compte);
      $info['o']                = ($compte->getSociete()) ? $compte->getSociete()->raison_sociale : '';
      $info['description']      = ($compte->societe_informations->type)? $compte->societe_informations->type : '';

      if (!is_null($compte->getNom())){
	        $info['sn']             = $compte->getNom();
      } else {
	        $info['sn']             = $compte->nom_a_afficher;
      }
      if (!$info['o']) {
          $info['o']                = $info['sn'];
      }
      if ($compte->getPrenom()){
          $info['givenName']        = $compte->getPrenom();
      }
      if ($compte->email && preg_match('/@/', $compte->email))
	       $info['mail']             = $compte->email;
      if ($compte->adresse) {
         $info['street']           = preg_replace('/;/', '\n', $compte->adresse);
      if ($compte->adresse_complementaire)
	       $info['street']        .= " \n ".preg_replace('/;/', '\n', $compte->adresse_complementaire);
      }
      if ($compte->commune)
          $info['l']                = trim($compte->commune);
      if ($compte->code_postal)
          $info['postalCode']       = trim($compte->code_postal);
      if ($compte->telephone_bureau)
          $info['telephoneNumber']  = trim(str_replace("_", "", $compte->telephone_bureau));
      if ($compte->telephone_mobile)
          $info['mobile']           = trim(str_replace("_", "", $compte->telephone_mobile));
      if ($compte->exist('mot_de_passe')) {
	        $info['userPassword']  = $compte->mot_de_passe;
	        if(!$compte->isActif()) {
	           $info['userPassword'] = null;
	        }
      }
      return $info;
    }

}
