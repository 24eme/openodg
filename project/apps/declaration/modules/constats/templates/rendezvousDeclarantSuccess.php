<?php use_helper("Date") ?>
<?php use_javascript("constats.js?201504020331", "last") ?>
<?php include_partial('admin/menu', array('active' => 'constats')); ?>
<div class="row row-margin text-center">
    <h2>Prise de Rendez-vous</h2>
</div>
<div class="row row-margin">
    <form method="post" action="" role="form" class="form-horizontal" id="rendezvousDeclarantForm">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
        <div class="form-group">
            <?php echo $form["login"]->renderError(); ?>
            <div class="col-xs-8 col-xs-offset-2">
                <?php
                echo $form["login"]->render(array("class" => "form-control input-lg select2 select2-offscreen select2autocompleteremote",
                    "placeholder" => ($compte->_id) ? "" . $compte->raison_sociale . " (" . $compte->cvi . ") " . $compte->adresse . " (" . $compte->code_postal . ")" : "Se connecter à un opérateur",
                    "data-url" => url_for('compte_recherche_json', array('type_compte' => CompteClient::TYPE_COMPTE_ETABLISSEMENT))
                ));
                ?>
            </div>           
        </div>

    </form>
</div>

<h3>Prendre le 1er rendez-vous <span class="icon-raisins size-36"></span></h3>
<div class="row">    
    <div class="col-xs-12">        
        <div class="list-group">
            <?php foreach ($compte->getChais() as $keyChai => $chai): ?>
                <div class="list-group-item">
                    <?php include_partial('constats/rendezvousForm', array('chai' => $chai, 'form' => $formsRendezVous[$keyChai], 'rendezvous' => $formsRendezVous[$keyChai]->getObject(), 'creation' => true)); ?> 
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>

<div class="row">    
    <div class="col-xs-12">
        <h3>Les rendez-vous</h3>
        <table class="table table-bordered table-condensed">
            <tr>
                <th>Chai</th>
                <th>Infos</th>
                <th>RDV</th>
                <th>Résultat</th>  
                <th class="col-xs-1 text-center" ></th>  
            </tr>
            <?php foreach ($rendezvousConstatsDeclarant->rendezvous as $idRendezvous => $rendezvous) : ?>

                <?php if ($rendezvous->statut != RendezvousClient::RENDEZVOUS_STATUT_REALISE): ?>
                    <tr style="border-bottom: 1px solid #cbcbcb;">
                        <td class="text-center"><?php echo "" . $rendezvous->idchai + 1; ?></td>                  
                        <td class="<?php if ($rendezvous->statut == RendezvousClient::RENDEZVOUS_STATUT_PRIS): ?>list-group-item-warning <?php endif; ?> <?php if ($rendezvous->statut == RendezvousClient::RENDEZVOUS_STATUT_ANNULE): ?>list-group-item-danger <?php endif; ?> "><?php echo RendezvousClient::$rendezvous_statut_libelles[$rendezvous->statut]; ?>
                            <?php
                            if ($rendezvous->statut != RendezvousClient::RENDEZVOUS_STATUT_PRIS): echo " pour " . $rendezvous->nom_agent_origine;
                            endif;
                            ?></td>
                        <td class="text-center">                          
                            <a href="<?php echo url_for('rendezvous_modification', $rendezvous); ?>">
                                <?php echo ucfirst(format_date($rendezvous->date, "P", "fr_FR")); ?><?php if ($rendezvous->isRendezvousRaisin()): ?> <?php echo str_replace(':', 'h', $rendezvous->heure); ?> <?php endif; ?>
                            </a>
                        </td>                
                        <td class="text-center">
                            <?php if ($rendezvous->isRendezvousRaisin()): ?>
                                <span class="icon-raisins size-36"></span>
                            <?php endif; ?>
                            <?php if ($rendezvous->isRendezvousVolume()): ?>
                                <span class="icon-mouts size-36"></span>
                            <?php endif; ?>
                            &nbsp;    
                             <!--<span class="glyphicon glyphicon-ban-circle"></span>--> 
                        </td>
                        <td class="col-xs-1 text-center" >
                            <?php if ($rendezvous->statut == RendezvousClient::RENDEZVOUS_STATUT_PRIS): ?>
                                <a class=" glyphicon glyphicon-remove-circle text-danger" href="<?php echo url_for('rendezvous_declarant_remove', array('idrendezvous' => $rendezvous->_id)); ?>" onclick="confirm('Êtes-vous sûre de vouloir supprimer ce rendez-vous?')"></a> 
                            <?php else: ?>
                                <span class=" glyphicon glyphicon-ban-circle"></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rendezvousConstatsDeclarant->constats[$idRendezvous]->constats as $idConstatRendezvous => $constat) : ?>                    
                        <tr style="border-bottom: 1px solid #cbcbcb;" >
                            <td class="text-center"><?php echo "" . $rendezvous->idchai + 1; ?></td>                  
                            <td class="list-group-item-success"><?php echo RendezvousClient::$rendezvous_statut_libelles[$rendezvous->statut]; ?>
                                <?php
                                if ($rendezvous->statut != RendezvousClient::RENDEZVOUS_STATUT_PRIS): echo " par " . $rendezvous->getAgentNom();
                                endif;
                                ?></td>
                            <td class="text-center">
                                <?php echo ucfirst(format_date($rendezvous->date, "P", "fr_FR")); ?><?php if ($rendezvous->isRendezvousRaisin()): ?> <?php echo str_replace(':', 'h', $rendezvous->heure); ?> <?php endif; ?>
                            </td>                
                            <td class="text-center" colspan="2">
                                <?php if (($constat->statut_raisin == ConstatsClient::STATUT_APPROUVE) && ($constat->statut_volume == ConstatsClient::STATUT_APPROUVE) && $rendezvousConstatsDeclarant->rendezvous[$idRendezvous]->isRendezvousRaisin()): ?>
                                    <span class="icon-raisins size-36"></span>&nbsp;<?php echo $constat->produit_libelle; ?>
                                    <br/>
                                    Constat volume le <?php echo format_date(substr($constat->date_volume, 0, 4) . '-' . substr($constat->date_volume, 4, 2) . '-' . substr($constat->date_volume, 6), "P", "fr_FR"); ?>
                                <?php elseif (($constat->statut_raisin == ConstatsClient::STATUT_APPROUVE) && ($constat->statut_volume == ConstatsClient::STATUT_APPROUVE)): ?>
                                    <span class="icon-mouts size-36"></span>&nbsp;<?php echo $constat->produit_libelle; ?>
                                    <br/>
                                    <span class="label label-success" >Approuvé</span>&nbsp;<a href="<?php echo url_for('constat_pdf', array('identifiant' => $constat->getDocument()->getCvi(), 'campagne' => substr($constat->getKey(), 0, 4), 'identifiantconstat' => $constat->getKey())) ?>"  class="btn btn-xs btn-default btn-primary-step"><span class="glyphicon glyphicon-file"></span>&nbsp;Visualiser</a>
                                <?php elseif (($constat->statut_raisin == ConstatsClient::STATUT_APPROUVE) && ($constat->statut_volume == ConstatsClient::STATUT_REFUSE) && $rendezvousConstatsDeclarant->rendezvous[$idRendezvous]->isRendezvousRaisin()): ?>
                                    <span class="icon-raisins size-36"></span>&nbsp;<?php echo $constat->produit_libelle; ?>
                                    <br/>
                                    Constat volume le <?php echo format_date(substr($constat->date_volume, 0, 4) . '-' . substr($constat->date_volume, 4, 2) . '-' . substr($constat->date_volume, 6), "P", "fr_FR"); ?>
                                <?php elseif (($constat->statut_raisin == ConstatsClient::STATUT_APPROUVE) && ($constat->statut_volume == ConstatsClient::STATUT_REFUSE)): ?>
                                    <span class="icon-mouts size-36"></span>&nbsp;<?php echo $constat->produit_libelle; ?><br/>
                                    <?php if ($constat->raison_refus == ConstatsClient::RAISON_REFUS_ASSEMBLE): ?>
                                        <span class="label label-warning">Assemblé</span>
                                    <?php else: ?>
                                        <span class="label label-danger">Refusé</span>
                                    <?php endif; ?>
                                <?php elseif (($constat->statut_raisin == ConstatsClient::STATUT_APPROUVE) && ($constat->statut_volume == ConstatsClient::STATUT_NONCONSTATE)): ?>
                                    <span class="icon-raisins size-36"></span>&nbsp;<?php echo $constat->produit_libelle; ?>
                                    <br/>
                                    &nbsp;A finir le <?php echo format_date(substr($constat->date_volume, 0, 4) . '-' . substr($constat->date_volume, 4, 2) . '-' . substr($constat->date_volume, 6), "P", "fr_FR"); ?>
                                <?php elseif ($constat->statut_raisin == ConstatsClient::STATUT_REFUSE): ?>
                                    <span class="icon-raisins size-36"></span>&nbsp;<?php echo $constat->produit_libelle; ?><br/>
                                    <span class="label label-danger">Refusé</span>                                   
                                <?php else: ?>
                                    <span class="label label-default"></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>



        </table>
    </div>
</div>
<div class="row">    
    <div class="col-xs-12">
        <a class="btn btn-warning btn-upper" href="<?php echo url_for('constats', array('jour' => date('Y-m-d'))) ?>"><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;&nbsp;Accueil</a>
        <a href="<?php echo url_for('constats_planifications', array('date' => date('Y-m-d'))) ?>" class="btn btn-lg btn-default btn-upper pull-right"><span class="glyphicon glyphicon-calendar"></span>&nbsp;&nbsp;Accéder à la planification d'aujourd'hui</a>
        <br/>
    </div>
</div> 