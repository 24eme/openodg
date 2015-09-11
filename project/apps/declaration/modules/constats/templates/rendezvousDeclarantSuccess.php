<?php use_helper("Date") ?>
<?php use_javascript("constats.js?201504020331", "last") ?>
<?php include_partial('admin/menu', array('active' => 'constats')); ?>
<div class="row row-margin">
    <form method="post" action="" role="form" class="form-horizontal" id="rendezvousDeclarantForm">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
        <div class="form-group">
            <?php echo $form["login"]->renderError(); ?>
            <div class="col-xs-8 col-xs-offset-2">
                <?php
                echo $form["login"]->render(array("class" => "form-control input-lg select2 select2-offscreen select2autocompleteremote",
                    "placeholder" => ($compte->_id) ? "" . $compte->raison_sociale . " (" . $compte->identifiant . ") " . $compte->adresse . " (" . $compte->code_postal . ")" : "Se connecter à un opérateur",
                    "data-url" => url_for('compte_recherche_json', array('type_compte' => CompteClient::TYPE_COMPTE_ETABLISSEMENT))
                ));
                ?>
            </div>
            <!--            <div class="col-xs-2">
                            <button class="btn btn-default btn-lg" type="submit">Se connecter</button>
                        </div>-->
        </div>

    </form>
</div>

<h3>Prendre le 1er rendez-vous <span class="icon-raisins size-36"></span></h3>
<div class="row">    
    <div class="col-xs-12">        
        <div class="list-group">
            <?php foreach ($compte->getChais() as $keyChai => $chai): ?>
                <div class="list-group-item">
                    <?php include_partial('constats/rendezvousModification', array('chai' => $chai, 'form' => $formsRendezVous[$keyChai], 'rendezvous' => $formsRendezVous[$keyChai]->getObject(), 'creation' => true)); ?> 
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>

<div class="row">    
    <div class="col-xs-12">
        <h3>Les rendez-vous</h3>
        <table class="table table-hover table-bordered">
            <tr>
                <th>Chai</th>
                <th>Infos</th>
                <th>RDV</th>
                <th>Résultat</th>  
            </tr>
            <?php foreach ($rendezvousDeclarant as $rendezvous) : ?>
                <tr>
                    <td class="text-center"><?php echo "" . $rendezvous->idchai + 1; ?></td>
                    <td><?php echo RendezvousClient::$rendezvous_statut_libelles[$rendezvous->statut]; ?></td>
                    <td class="text-center">
                        <?php if ($rendezvous->isRendezvousRaisin()): ?>
                            <span class="icon-raisins size-36"></span>
                        <?php endif; ?>
                        <?php if ($rendezvous->isRendezvousVolume()): ?>
                            <span class="icon-mouts size-36"></span>
                        <?php endif; ?>
                        &nbsp;
                        <a href="<?php echo url_for('rendezvous_modification', $rendezvous); ?>">
                            <?php echo format_date($rendezvous->date, "P", "fr_FR"); ?><?php if ($rendezvous->isRendezvousRaisin()): ?> <?php echo str_replace(':', 'h', $rendezvous->heure); ?> <?php endif; ?>
                        </a>
                    </td>                
                    <td>Rés</td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<div class="row">    
    <div class="col-xs-12 text-right">
        <a class="btn btn-default btn-upper" href="<?php echo url_for('constats', array('jour' => date('Y-m-d'))); ?>" >Accèder aux planifications&nbsp;<span class="glyphicon glyphicon-arrow-right"></span>
        </a>
        <br/>
    </div>
</div> 