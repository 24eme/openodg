<?php

class ConfigurationRouting {

    static public function listenToRoutingLoadConfigurationEvent(sfEvent $event) {
        $r = $event->getSubject();
        
        $r->prependRoute('produits', new sfRoute('/produits', array('module' => 'produit', 
                                                                  'action' => 'index')));

        $r->prependRoute('produit_modification', new sfRoute('/produits/modification/:noeud/:hash', 
            array('module' => 'produit', 'action' => 'modification', 'hash' => null, 'noeud' => null)));

        $r->prependRoute('produit_next_noeud_to_fill', new sfRoute('/produits/prochain_noeud_a_remplir/:hash', 
            array('module' => 'produit', 'action' => 'nextNoeudToFill', 'hash' => null)));

        $r->prependRoute('produit_nouveau', new sfRoute('/produits/nouveau', 
            array('module' => 'produit', 'action' => 'nouveau', 'hash' => null, 'noeud' => null)));

        $r->prependRoute('produits_odg', new sfRoute('/produits_odg/:odg', array('module' => 'produit',
                                                                    'action' => 'odg')));
        $r->prependRoute('produit_habilitation', new sfRoute('/produits_odg/:odg/habilitation.csv', array('module' => 'produit',
                                                                    'action' => 'habilitation')));

    }
 }
