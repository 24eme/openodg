<?php
/**
 * BaseControleManquement
 * 
 * Base model for ControleManquement

 * @property string $libelle
 * @property string $observations

 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getObservations()
 * @method string setObservations()
 
 */

abstract class BaseControleManquement extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Controle';
       $this->_tree_class_name = 'ControleManquement';
    }
                
}