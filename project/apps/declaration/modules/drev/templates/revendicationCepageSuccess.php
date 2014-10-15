<?php include_partial('drev/step', array('step' => 'revendication', 'drev' => $drev)) ?>

<div class="page-header no-border">
    <h2>Revendication</h2>
</div>

<?php include_component('drev', 'stepRevendication', array('drev' => $drev, 'noeud' => $noeud)) ?>

<form role="form" class="ajaxForm" action="<?php echo url_for("drev_revendication_cepage", $noeud) ?>" method="post">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <p>Veuillez saisir les données par cépage</p>

    <?php if ($sf_user->hasFlash('notice')): ?>
        <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
    <?php endif; ?>
    <?php if ($sf_user->hasFlash('erreur')): ?>
        <p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('erreur') ?></p>
    <?php endif; ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th class="col-xs-3">Produits</th>
                <th class="text-center col-xs-3">Volume revendiqué <small class="text-muted">(hl)</small></th>
                <th class="text-center col-xs-3">Volume revendiqué VT <small class="text-muted">(hl)</small></th>
                <th class="text-center col-xs-3">Volume revendiqué SGN <small class="text-muted">(hl)</small></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($form['produits'] as $hash => $embedForm): ?> 
                <?php $produit = $drev->get($hash); ?> 
                <tr>
                    <td><?php if ($produit->getParent()->getParent()->getLibelle()): ?><?php echo $produit->getParent()->getParent()->getLibelle() ?> - <?php endif; ?><?php echo $produit->getLibelle() ?></td>
                    <td class="text-center">
                        <div class="form-group <?php if($embedForm['volume_revendique']->hasError()): ?>has-error<?php endif; ?>">
                            <?php echo $embedForm['volume_revendique']->renderError() ?>
                            <div class="col-xs-8 col-xs-offset-2">
                                <?php echo $embedForm['volume_revendique']->render(array('class' => 'form-control input input-rounded num_float text-right')) ?>
                            </div>
                        </div>

                    </td>
                    <?php if (isset($embedForm['volume_revendique_vt']) && isset($embedForm['volume_revendique_sgn'])): ?>
                        <td class="text-center">
                            <div class="form-group <?php if($embedForm['volume_revendique_vt']->hasError()): ?>has-error<?php endif; ?>">
                                <?php echo $embedForm['volume_revendique_vt']->renderError() ?>
                                <div class="col-xs-8 col-xs-offset-2">
                                    <?php echo $embedForm['volume_revendique_vt']->render(array('class' => 'form-control input input-rounded num_float text-right')) ?>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-group <?php if($embedForm['volume_revendique_sgn']->hasError()): ?>has-error<?php endif; ?>">
                                <?php echo $embedForm['volume_revendique_sgn']->renderError() ?>
                                <div class="col-xs-8 col-xs-offset-2">
                                    <?php echo $embedForm['volume_revendique_sgn']->render(array('class' => 'form-control input input-rounded num_float text-right')) ?>
                                </div>
                            </div>
                        </td>
                    <?php else: ?>
                        <td></td>
                        <td></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if ($ajoutForm->hasProduits()): ?>
                <tr>
                    <td>
                        <button class="btn btn-sm btn-warning ajax" data-toggle="modal" data-target="#popupForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter un produit / cépage</button>
                    </td>
                    <td></td><td></td><td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <?php if ($noeud->getPreviousSister()): ?>
                <a href="<?php echo url_for('drev_revendication_cepage', $noeud->getPreviousSister()) ?>" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'appellation précédente</small></a>
            <?php else: ?>
                <a href="<?php echo url_for("drev_exploitation", $drev) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
            <?php endif; ?>
        </div>
        <div class="col-xs-6 text-right">
        	<?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
	           <button id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span> Retourner <small>à la validation</small>&nbsp;&nbsp;</button>
	        <?php elseif($noeud->getNextSister()): ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper btn-default-step">Continuer <small>vers l'appellation suivante</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
            <?php else: ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper btn-default-step">Continuer <small>vers le récapitulatif</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
	        <?php endif; ?>
        </div>
    </div>
</form>

<?php include_partial('drev/popupAjoutForm', array('url' => url_for('drev_revendication_cepage_ajout', $noeud), 'form' => $ajoutForm)); ?>