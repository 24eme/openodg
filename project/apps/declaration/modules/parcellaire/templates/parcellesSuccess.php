<?php
use_helper('Float');
include_partial('step', array('step' => 'parcelles', 'parcellaire' => $parcellaire));
$isVtSgn = is_string($appellationNode) && ($appellationNode == ParcellaireClient::APPELLATION_VTSGN);
?>

<div class="page-header">
    <h2>Saisie des parcelles<?php echo ($parcellaire->isParcellaireCremant()) ? ' de Crémant' : ''; ?></h2>
</div>

<ul class="nav nav-tabs">
    <?php
    $selectedAppellationName = "";
    foreach ($parcellaireAppellations as $appellationKey => $appellationName) :
        if ($appellationKey == ParcellaireClient::APPELLATION_VTSGN) {
            $nb = count($parcellaire->declaration->getProduitsCepageDetails(true, true));
        } else {
            $nb = ($parcellaire->declaration->exist("certification/genre/appellation_" . $appellationKey)) ? count($parcellaire->declaration->get("certification/genre/appellation_" . $appellationKey)->getProduitsCepageDetails()) : 0;
        }
        $isSelectedAppellation = ($appellation == $appellationKey);
        if (!$selectedAppellationName && $isSelectedAppellation) {
            $selectedAppellationName = $appellationName;
        }
        ?>
        <li role="presentation" class="<?php echo ($isSelectedAppellation) ? 'active' : '' ?>"><a href="<?php echo url_for('parcellaire_parcelles', array('id' => $parcellaire->_id, 'appellation' => $appellationKey)) ?>" class="ajax"><?php echo $appellationName; ?> <span class="badge"><?php echo $nb ?></span></a></li>
    <?php endforeach; ?>
</ul>

<?php if ($sf_user->hasFlash('warning')): ?>
    <div class="alert alert-warning" role="alert"><?php echo $sf_user->getFlash('warning') ?></div>
<?php endif; ?>

<form action="<?php echo url_for('parcellaire_parcelles', array('id' => $parcellaire->_id, 'appellation' => $appellation)); ?>" method="post" class="form-horizontal ajaxForm parcellaireForm">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <div class="row">       
        <div class="col-xs-12">
            <div id="listes_cepages" class="list-group">
                <?php if (count($parcelles)) : ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="col-xs-1">Déclarer</th>           
                                <th class="col-xs-2">Commune</th>        
                                <th class="col-xs-1">Section</th>        
                                <th class="col-xs-1">Numéro</th>        
                                <th class="col-xs-2"><?php if ($appellation == ParcellaireClient::APPELLATION_VTSGN): ?>Appellation<?php else: ?>Lieu-dit<?php endif; ?></th>      
                                <th class="col-xs-3"><?php if ($appellation == ParcellaireClient::APPELLATION_VTSGN): ?>Lieu-dit / <?php endif; ?>Cépage</th>        
                                <th class="col-xs-2">Superficie</th>           
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tabindex = 1;
                            foreach ($parcelles as $key => $parcelle):
                                $attention_ret = ($attention && ($attention == $parcelle->getHashForKey()));
                                $erreur_ret = ($erreur && ($erreur == $parcelle->getHashForKey()));
                                $class = ($erreur_ret || $attention_ret) ? 'error_field_to_focused' : '';
                                $styleErr = ($attention_ret) ? 'style="border-style: solid; border-width: 1px; border-color: darkorange;"' : "";
                                $styleWar = ($erreur_ret) ? 'style="border-style: solid; border-width: 1px; border-color: darkred;"' : "";
                                ?>
                                <tr <?php echo $styleErr . $styleWar; ?> >
                                    <td class="text-center">
                                        <?php
                                        if (isset($form['produits'][$parcelle->getHashForKey()]['vtsgn'])) {
                                            echo $form['produits'][$parcelle->getHashForKey()]['vtsgn']->render();
                                        } else {
                                            echo $form['produits'][$parcelle->getHashForKey()]['active']->render();
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $parcelle->getCommune(); ?></td>         
                                    <td><?php echo $parcelle->getSection(); ?></td>         
                                    <td><?php echo $parcelle->getNumeroParcelle(); ?></td>         


                                    <td>
                                        <?php
                                        if ($appellation == ParcellaireClient::APPELLATION_VTSGN) {
                                            echo ParcellaireClient::getAppellationLibelle($parcelle->getAppellation()->getKey());
                                        } else {
                                            echo $parcelle->getLieuLibelle();
                                        }
                                        ?>
                                    </td>        
                                    <td>
                                        <?php
                                        if ($appellation == ParcellaireClient::APPELLATION_VTSGN) {
                                            echo ($parcelle->getLieuLibelle())? $parcelle->getLieuLibelle() . " / " : "";
                                        }
                                        echo $parcelle->getCepageLibelle();
                                        ?>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-xs-6 text-right">
                                                <?php echoFloat($parcelle->getSuperficie()) ?>
                                            </div> 
                                            <div class="col-xs-6 text-left">    
                                                <?php if (!$isVtSgn || $parcelle->isFromAppellation(ParcellaireClient::APPELLATION_ALSACEBLANC)): ?>
                                                    &nbsp;<a class="btn btn-link btn-xs" href="<?php echo url_for('parcellaire_parcelle_modification', array('id' => $parcellaire->_id, 'appellation' => $appellation, 'parcelle' => $parcelle->getHashForKey())); ?>"><span class="glyphicon glyphicon-pencil"></span></a>
                                                <?php endif; ?>
                                            </div> 
                                        </div>

                                    </td>             
                                    <!--<td><a href="<?php echo url_for('parcellaire_parcelle_delete', array('id' => $parcellaire->_id, 'appellation' => $appellation, 'parcelle' => $parcelle->getHashForKey())); ?>" class="btn btn-danger btn-sm deleteButton"><span class="glyphicon glyphicon-remove"></span></a><a class="ajax fakeDeleteButton hidden" href="<?php echo url_for('parcellaire_parcelle_delete', array('id' => $parcellaire->_id, 'appellation' => $appellation, 'parcelle' => $parcelle->getHashForKey())); ?>"></a></td>-->
                                </tr>
                                <?php
                                $tabindex++;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <p class="text-muted">Vous n'avez affecté aucune parcelle pour cette appellation.</p><br/>
<?php endif; ?>
            <div class="text-left">
                <button class="btn btn-sm btn-warning ajax" data-toggle="modal" data-target="#popupForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter une parcelle</button>
            </div>
        </div>
    </div>
    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <?php if ($isVtSgn) : ?>
                <a href="<?php echo url_for('parcellaire_parcelles', array('id' => $parcellaire->_id, 'appellation' => "GRDCRU")); ?>" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Précédent</a>
            <?php elseif ($appellationNode->getPreviousAppellationKey()) : ?>
                <a href="<?php echo url_for('parcellaire_parcelles', array('id' => $parcellaire->_id, 'appellation' => $appellationNode->getPreviousAppellationKey())); ?>" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Précédent</a>
            <?php else : ?>
                <a href="<?php echo url_for("parcellaire_exploitation", $parcellaire) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Précédent</a>
            <?php endif; ?>
        </div>
        <div class="col-xs-6 text-right">
            <?php if ($parcellaire->exist('etape') && $parcellaire->etape == ParcellaireEtapes::ETAPE_VALIDATION): ?>
                <button id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Retourner <small>à la validation</small></button>
            <?php elseif ($appellationNode->getNextAppellationKey()): ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper btn-default-step">Continuer&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
                <?php else: ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper btn-default">Continuer&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
<?php endif; ?>
        </div>
    </div>
</form>

<?php include_partial('parcellaire/popupAjoutForm', array('url' => url_for('parcellaire_parcelle_ajout', array('id' => $parcellaire->_id, 'appellation' => $appellation)), 'form' => $ajoutForm)); ?>
