<?php
/**
 * BaseConfigurationAppellation
 * 
 * Base model for ConfigurationAppellation

 * @property string $appellation
 * @property string $libelle
 * @property string $libelle_long
 * @property float $rendement
 * @property float $rendement_appellation
 * @property integer $mout
 * @property integer $auto_ds
 * @property integer $no_total_cepage
 * @property integer $detail_lieu_editable
 * @property integer $exclude_total
 * @property string $no_vtsgn
 * @property ConfigurationDouane $douane

 * @method string getAppellation()
 * @method string setAppellation()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getLibelleLong()
 * @method string setLibelleLong()
 * @method float getRendement()
 * @method float setRendement()
 * @method float getRendementAppellation()
 * @method float setRendementAppellation()
 * @method integer getMout()
 * @method integer setMout()
 * @method integer getAutoDs()
 * @method integer setAutoDs()
 * @method integer getNoTotalCepage()
 * @method integer setNoTotalCepage()
 * @method integer getDetailLieuEditable()
 * @method integer setDetailLieuEditable()
 * @method integer getExcludeTotal()
 * @method integer setExcludeTotal()
 * @method string getNoVtsgn()
 * @method string setNoVtsgn()
 * @method ConfigurationDouane getDouane()
 * @method ConfigurationDouane setDouane()
 
 */

abstract class BaseConfigurationAppellation extends _ConfigurationDeclaration {
                
    public function configureTree() {
       $this->_root_class_name = 'Configuration';
       $this->_tree_class_name = 'ConfigurationAppellation';
    }
                
}