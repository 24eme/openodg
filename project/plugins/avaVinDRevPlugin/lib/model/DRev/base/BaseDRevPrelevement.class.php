<?php
/**
 * BaseDRevPrelevement
 * 
 * Base model for DRevPrelevement

 * @property string $total_lots
 * @property string $date
 * @property string $date_precedente
 * @property string $libelle_produit
 * @property string $libelle_produit_type
 * @property string $libelle_date
 * @property string $libelle
 * @property acCouchdbJson $lots

 * @method string getTotalLots()
 * @method string setTotalLots()
 * @method string getDate()
 * @method string setDate()
 * @method string getDatePrecedente()
 * @method string setDatePrecedente()
 * @method string getLibelleProduit()
 * @method string setLibelleProduit()
 * @method string getLibelleProduitType()
 * @method string setLibelleProduitType()
 * @method string getLibelleDate()
 * @method string setLibelleDate()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method acCouchdbJson getLots()
 * @method acCouchdbJson setLots()
 
 */

abstract class BaseDRevPrelevement extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevPrelevement';
    }
                
}