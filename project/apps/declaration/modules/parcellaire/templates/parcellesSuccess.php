<?php include_partial('step', array('step' => 'parcelles', 'parcellaire' => $parcellaire)); ?>

<div class="page-header">
    <h2>Saisie des parcelles</h2>
</div>

<ul class="nav nav-tabs">
    <?php 
    $selectedAppellationName = "";
    foreach ($parcellaireAppellations as $appellationKey => $appellationName) : 
        $styleOpacity = ($appellation == $appellationKey || $parcellaire->hasParcelleForAppellationKey($appellationKey))? '' : 'style="opacity: 0.5;"';
        $isSelectedAppellation = ($appellation == $appellationKey);
        if(!$selectedAppellationName && $isSelectedAppellation){
            $selectedAppellationName = $appellationName;
        }
        ?>
        <li role="presentation" class="<?php echo ($isSelectedAppellation) ? 'active' : '' ?>" <?php echo $styleOpacity; ?> ><a href="<?php echo url_for('parcellaire_parcelles', array('id' => $parcellaire->_id, 'appellation' => $appellationKey)) ?>" class="ajax"><?php echo $appellationName; ?></a></li>
    <?php endforeach; ?>
</ul>

<form action="<?php echo url_for('parcellaire_parcelles', array('id' => $parcellaire->_id, 'appellation' => $appellation)); ?>" method="post" class="form-horizontal ajaxForm">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <div class="row">       
        <div class="col-xs-12">
            <div id="listes_cepages" class="list-group">
                <?php if(count($parcelles)) : ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="col-xs-3">Commune</th>        
                            <th class="col-xs-1">Section</th>        
                            <th class="col-xs-1">Numéro</th>        
                            <th class="col-xs-2">Lieu-dit</th>      
                            <th class="col-xs-3">Cépage</th>        
                            <th class="col-xs-3">Superficie en ares</th>           
                            <th class="col-xs-1"></th>           
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($parcelles as $key => $parcelle):
                            ?>
                            <tr>
                                <td><?php echo $parcelle->getCommune(); ?></td>         
                                <td><?php echo $parcelle->getSection(); ?></td>         
                                <td><?php echo $parcelle->getNumeroParcelle(); ?></td>         
                                <td><?php echo $parcelle->getLieuLibelle(); ?></td>        
                                <td><?php echo $parcelle->getCepageLibelle(); ?></td>        
                                <td><?php echo $form['produits'][$parcelle->getHashForKey()]['superficie']->render(array('class' => "form-control text-right input-rounded num_float num_float4")); ?></td>                 
                                <td><a href="<?php echo url_for('parcellaire_parcelle_delete' , array('id' => $parcellaire->_id, 'appellation' => $appellation,'parcelle' => $parcelle->getHashForKey())); ?>" >suppr.</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else : ?>
            <p>Vous n'avez aucunes parcelles pour les <?php echo $selectedAppellationName; ?></p><br/>
            <?php endif; ?>
            <div class="text-left">
                <button class="btn btn-sm btn-warning ajax" data-toggle="modal" data-target="#popupForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter une parcelle</button>
            </div>
        </div>
    </div>
    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <?php if ($appellationNode->getPreviousAppellationKey()): ?>
                <a href="<?php echo url_for('parcellaire_parcelles', array('id' => $parcellaire->_id, 'appellation' => $appellationNode->getPreviousAppellationKey())); ?>" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'appellation précédente</small></a>
            <?php else : ?>
                <a href="<?php echo url_for("parcellaire_exploitation", $parcellaire) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
            <?php endif; ?>

        </div>
        <div class="col-xs-6 text-right">
            <?php if ($parcellaire->exist('etape') && $parcellaire->etape == ParcellaireEtapes::ETAPE_VALIDATION): ?>
                <button id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span> Retourner <small>à la validation</small>&nbsp;&nbsp;</button>
            <?php elseif ($appellationNode->getNextAppellationKey()): ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper btn-default-step">Continuer <small>vers l'appellation suivante</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
            <?php else: ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper btn-default">Continuer <small>vers les acheteurs</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
                <?php endif; ?>
        </div>
    </div>
</form>

<?php include_partial('parcellaire/popupAjoutForm', array('url' => url_for('parcellaire_parcelle_ajout', array('id' => $parcellaire->_id, 'appellation' => $appellation)), 'form' => $ajoutForm)); ?>