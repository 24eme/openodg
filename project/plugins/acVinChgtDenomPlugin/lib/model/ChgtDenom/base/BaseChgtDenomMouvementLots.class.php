<?php
/**
 * BaseChgtDenomMouvementLots
 *
 * Base model for ChgtDenomMouvementLots

 * @property integer $prelevable
 * @property integer $preleve
 * @property string $produit_hash
 * @property string $produit_libelle
 * @property string $produit_couleur
 * @property float $volume
 * @property string $date
 * @property string $millesime
 * @property string $elevage
 * @property string $region
 * @property string $numero_dossier
 * @property string $numero_archive
 * @property string $numero_logement_operateur
 * @property string $version
 * @property string $origine_hash
 * @property string $origine_type
 * @property string $origine_document_id
 * @property string $origine_mouvement
 * @property string $declarant_identifiant
 * @property string $declarant_nom
 * @property string $destination_type
 * @property string $destination_date
 * @property string $details
 * @property string $campagne
 * @property string $id_document
 * @property string $statut
 * @property string $specificite

 * @method integer getPrelevable()
 * @method integer setPrelevable()
 * @method integer getPreleve()
 * @method integer setPreleve()
 * @method string getProduitHash()
 * @method string setProduitHash()
 * @method string getProduitLibelle()
 * @method string setProduitLibelle()
 * @method string getProduitCouleur()
 * @method string setProduitCouleur()
 * @method float getVolume()
 * @method float setVolume()
 * @method string getDate()
 * @method string setDate()
 * @method string getMillesime()
 * @method string setMillesime()
 * @method string getElevage()
 * @method string setElevage()
 * @method string getRegion()
 * @method string setRegion()
 * @method string getNumeroDossier()
 * @method string setNumeroDossier()
 * @method string getNumeroArchive()
 * @method string setNumeroArchive()
 * @method string getNumeroLogementOperateur()
 * @method string setNumeroLogementOperateur()
 * @method string getVersion()
 * @method string setVersion()
 * @method string getOrigineHash()
 * @method string setOrigineHash()
 * @method string getOrigineType()
 * @method string setOrigineType()
 * @method string getOrigineIdDocument()
 * @method string setOrigineIdDocument()
 * @method string getOrigineMouvement()
 * @method string setOrigineMouvement()
 * @method string getDeclarantIdentifiant()
 * @method string setDeclarantIdentifiant()
 * @method string getDeclarantNom()
 * @method string setDeclarantNom()
 * @method string getDestinationType()
 * @method string setDestinationType()
 * @method string getDestinationDate()
 * @method string setDestinationDate()
 * @method string getDetails()
 * @method string setDetails()
 * @method string getCampagne()
 * @method string setCampagne()
 * @method string getIdDocument()
 * @method string setIdDocument()
 * @method string getStatut()
 * @method string setStatut()
 * @method string getSpecificite()
 * @method string setSpecificite()

 */

abstract class BaseChgtDenomMouvementLots extends MouvementLots {

    public function configureTree() {
       $this->_root_class_name = 'ChgtDenom';
       $this->_tree_class_name = 'ChgtDenomMouvementLots';
    }

}
