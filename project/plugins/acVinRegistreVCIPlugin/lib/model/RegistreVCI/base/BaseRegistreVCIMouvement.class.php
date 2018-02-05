<?php
/**
 * BaseRegistreVCIMouvement
 * 
 * Base model for RegistreVCIMouvement

 * @property string $date
 * @property string $produit_hash
 * @property string $produit_libelle
 * @property string $detail_hash
 * @property string $detail_libelle
 * @property string $mouvement_type
 * @property string $volume
 * @property string $stock_resultant

 * @method string getDate()
 * @method string setDate()
 * @method string getProduitHash()
 * @method string setProduitHash()
 * @method string getProduitLibelle()
 * @method string setProduitLibelle()
 * @method string getDetailHash()
 * @method string setDetailHash()
 * @method string getDetailLibelle()
 * @method string setDetailLibelle()
 * @method string getMouvementType()
 * @method string setMouvementType()
 * @method string getVolume()
 * @method string setVolume()
 * @method string getStockResultant()
 * @method string setStockResultant()
 
 */

abstract class BaseRegistreVCIMouvement extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'RegistreVCI';
       $this->_tree_class_name = 'RegistreVCIMouvement';
    }
                
}