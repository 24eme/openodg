<?php
/**
 * BaseParcellaireManquantDeclarant
 *
 * Base model for ParcellaireManquantDeclarant

 * @property string $nom
 * @property string $raison_sociale
 * @property string $cvi
 * @property string $ppm
 * @property string $siret
 * @property string $adresse
 * @property string $commune
 * @property string $code_postal
 * @property string $telephone
 * @property string $telephone_bureau
 * @property string $telephone_mobile
 * @property string $fax
 * @property string $email

 * @method string getNom()
 * @method string setNom()
 * @method string getRaisonSociale()
 * @method string setRaisonSociale()
 * @method string getCvi()
 * @method string setCvi()
 * @method string getPpm()
 * @method string setPpm()
 * @method string getSiret()
 * @method string setSiret()
 * @method string getAdresse()
 * @method string setAdresse()
 * @method string getCommune()
 * @method string setCommune()
 * @method string getCodePostal()
 * @method string setCodePostal()
 * @method string getTelephone()
 * @method string setTelephone()
 * @method string getTelephoneBureau()
 * @method string setTelephoneBureau()
 * @method string getTelephoneMobile()
 * @method string setTelephoneMobile()
 * @method string getFax()
 * @method string setFax()
 * @method string getEmail()
 * @method string setEmail()

 */

abstract class BaseParcellaireManquantDeclarant extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'ParcellaireManquant';
       $this->_tree_class_name = 'ParcellaireManquantDeclarant';
    }

}
