<?php
/**
 * BaseHabilitationProduit
 * 
 * Base model for HabilitationProduit

 * @property string $libelle
 * @property HabilitationActivites $activites

 * @method string getLibelle()
 * @method string setLibelle()
 * @method HabilitationActivites getActivites()
 * @method HabilitationActivites setActivites()
 
 */

abstract class BaseHabilitationProduit extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationProduit';
    }
                
}