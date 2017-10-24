<?php
/**
 * BaseTravauxMarcFournisseur
 *
 * Base model for TravauxMarcFournisseur

 * @property string $nom
 * @property string $date_livraison
 * @property string $quantite

 * @method string getEtablissementId()
 * @method string setEtablissementId()
 * @method string getNom()
 * @method string setNom()
 * @method string getDateLivraison()
 * @method string setDateLivraison()
 * @method string getQuantite()
 * @method string setQuantite()
 
 */

abstract class BaseTravauxMarcFournisseur extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'TravauxMarc';
       $this->_tree_class_name = 'TravauxMarcFournisseur';
    }
                
}