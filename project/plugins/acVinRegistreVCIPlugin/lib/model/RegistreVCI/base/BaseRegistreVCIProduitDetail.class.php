<?php
/**
 * BaseRegistreVCIProduitDetail
 * 
 * Base model for RegistreVCIProduitDetail

 * @property string $stockage_libelle
 * @property string $stockage_identifiant
 * @property string $denomination_complementaire
 * @property float $stock_precedent
 * @property float $destruction
 * @property float $complement
 * @property float $substitution
 * @property float $rafraichi
 * @property float $constitue
 * @property float $stock_final

 * @method string getStockageLibelle()
 * @method string setStockageLibelle()
 * @method string getStockageIdentifiant()
 * @method string setStockageIdentifiant()
 * @method string getDenominationComplementaire()
 * @method string setDenominationComplementaire()
 * @method float getStockPrecedent()
 * @method float setStockPrecedent()
 * @method float getDestruction()
 * @method float setDestruction()
 * @method float getComplement()
 * @method float setComplement()
 * @method float getSubstitution()
 * @method float setSubstitution()
 * @method float getRafraichi()
 * @method float setRafraichi()
 * @method float getConstitue()
 * @method float setConstitue()
 * @method float getStockFinal()
 * @method float setStockFinal()
 
 */

abstract class BaseRegistreVCIProduitDetail extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'RegistreVCI';
       $this->_tree_class_name = 'RegistreVCIProduitDetail';
    }
                
}