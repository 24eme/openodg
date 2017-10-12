<?php
/**
 * BaseHabilitationActivite
 * 
 * Base model for HabilitationActivite

 * @property string $date
 * @property string $commentaire
 * @property string $statut

 * @method string getDate()
 * @method string setDate()
 * @method string getCommentaire()
 * @method string setCommentaire()
 * @method string getStatut()
 * @method string setStatut()
 
 */

abstract class BaseHabilitationActivite extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationActivite';
    }
                
}