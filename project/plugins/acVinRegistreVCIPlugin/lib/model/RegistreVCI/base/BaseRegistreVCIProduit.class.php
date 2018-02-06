<?php
/**
 * BaseRegistreVCIProduit
 * 
 * Base model for RegistreVCIProduit

 * @property string $libelle
 * @property float $stock_precedent
 * @property float $destruction
 * @property float $complement
 * @property float $substitution
 * @property float $rafraichi
 * @property float $constitue
 * @property float $stock_final
 * @property acCouchdbJson $details

 * @method string getLibelle()
 * @method string setLibelle()
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
 * @method acCouchdbJson getDetails()
 * @method acCouchdbJson setDetails()
 
 */

abstract class BaseRegistreVCIProduit extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'RegistreVCI';
       $this->_tree_class_name = 'RegistreVCIProduit';
    }
                
}