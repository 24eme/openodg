<?php
abstract class BaseControleParcelle extends ParcellaireParcelle
{
    public function configureTree()
    {
       $this->_root_class_name = 'Controle';
       $this->_tree_class_name = 'ControleParcelle';
    }
}
