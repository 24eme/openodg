<?php
/**
 * BaseChgtDenomLot
 *
 * Base model for ChgtDenomLot

 * @property string $date
 * @property string $id_document
 * @property string $numero_dossier
 * @property string $numero_archive
 * @property string $numero_logement_operateur
 * @property string $millesime
 * @property string $elevage
 * @property float $volume
 * @property string $destination_type
 * @property string $destination_date
 * @property string $produit_hash
 * @property string $produit_libelle
 * @property string $declarant_identifiant
 * @property string $declarant_nom
 * @property string $origine_mouvement
 * @property string $details
 * @property string $statut
 * @property string $specificite

 * @method string getDate()
 * @method string setDate()
 * @method string getIdDocument()
 * @method string setIdDocument()
 * @method string getNumeroDossier()
 * @method string setNumeroDossier()
 * @method string getNumeroArchive()
 * @method string setNumeroArchive()
 * @method string getNumeroLogementOperateur()
 * @method string setNumeroLogementOperateur()
 * @method string getMillesime()
 * @method string setMillesime()
 * @method string getElevage()
 * @method string setElevage()
 * @method float getVolume()
 * @method float setVolume()
 * @method string getDestinationType()
 * @method string setDestinationType()
 * @method string getDestinationDate()
 * @method string setDestinationDate()
 * @method string getProduitHash()
 * @method string setProduitHash()
 * @method string getProduitLibelle()
 * @method string setProduitLibelle()
 * @method string getDeclarantIdentifiant()
 * @method string setDeclarantIdentifiant()
 * @method string getDeclarantNom()
 * @method string setDeclarantNom()
 * @method string getOrigineMouvement()
 * @method string setOrigineMouvement()
 * @method string getDetails()
 * @method string setDetails()
 * @method string getStatut()
 * @method string setStatut()
 * @method string getSpecificite()
 * @method string setSpecificite()

 */

abstract class BaseChgtDenomLot extends Lot {

    public function configureTree() {
       $this->_root_class_name = 'ChgtDenom';
       $this->_tree_class_name = 'ChgtDenomLot';
    }

}
