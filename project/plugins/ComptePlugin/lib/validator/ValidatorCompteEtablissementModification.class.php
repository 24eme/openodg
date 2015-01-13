<?php

/* This file is part of the acVinComptePlugin package.
 * Copyright (c) 2011 Actualys
 * Authors :    
 * Tangui Morlier <tangui@tangui.eu.org>
 * Charlotte De Vichet <c.devichet@gmail.com>
 * Vincent Laurent <vince.laurent@gmail.com>
 * Jean-Baptiste Le Metayer <lemetayer.jb@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * acVinComptePlugin validator.
 * 
 * @package    acVinComptePlugin
 * @subpackage lib
 * @author     Tangui Morlier <tangui@tangui.eu.org>
 * @author     Charlotte De Vichet <c.devichet@gmail.com>
 * @author     Vincent Laurent <vince.laurent@gmail.com>
 * @author     Jean-Baptiste Le Metayer <lemetayer.jb@gmail.com>
 * @version    0.1
 */
class ValidatorCompteEtablissementModification extends sfValidatorBase 
{

    public function configure($options = array(), $messages = array()) 
    {
        $this->addMessage('uniq', "Ce numéro CVI est déjà utilisé");
    }

    protected function doClean($values) 
    {
        if(!isset($values['cvi'])) {

            return $values;
        }
        
        $compte = CompteClient::getInstance()->findByIdentifiant('E'.$values['cvi']);

        if ($compte) {
            throw new sfValidatorErrorSchema($this, array('cvi' => new sfValidatorError($this, 'uniq')));
        }
                
        return $values;
    }
}