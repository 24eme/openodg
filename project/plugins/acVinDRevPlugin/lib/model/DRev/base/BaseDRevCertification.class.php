<?php
/**
 * BaseDRevCertification
 * 
 * Base model for DRevCertification

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()
 
 */

abstract class BaseDRevCertification extends _DRevDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevCertification';
    }
                
}