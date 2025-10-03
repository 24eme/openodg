<?php
abstract class BaseControleDeclarant extends ParcellaireDeclarant
{
    public function configureTree()
    {
       $this->_root_class_name = 'Controle';
       $this->_tree_class_name = 'ControleDeclarant';
    }
}
