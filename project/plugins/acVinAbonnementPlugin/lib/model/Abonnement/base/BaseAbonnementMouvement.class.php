<?php
/**
 * BaseAbonnementMouvement
 * 
 * Base model for AbonnementMouvement

 * @property integer $facture
 * @property integer $facturable

 * @method integer getFacture()
 * @method integer setFacture()
 * @method integer getFacturable()
 * @method integer setFacturable()
 
 */

abstract class BaseAbonnementMouvement extends Mouvement {
                
    public function configureTree() {
       $this->_root_class_name = 'Abonnement';
       $this->_tree_class_name = 'AbonnementMouvement';
    }
                
}