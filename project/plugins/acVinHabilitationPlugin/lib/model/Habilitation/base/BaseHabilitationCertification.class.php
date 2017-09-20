<?php
/**
 * BaseHabilitationCertification
 * 
 * Base model for HabilitationCertification

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()
 
 */

abstract class BaseHabilitationCertification extends _HabilitationDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationCertification';
    }
                
}