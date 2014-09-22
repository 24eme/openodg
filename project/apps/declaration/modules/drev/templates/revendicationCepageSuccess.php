<?php include_partial('drev/step', array('step' => 'revendication', 'drev' => $drev)) ?>

<div class="page-header no-border">
    <h2>Revendication</h2>
</div>

<?php include_partial('drev/stepRevendication', array('drev' => $drev, 'noeud' => $noeud)) ?>

<form role="form" action="" method="post">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <p>Veuillez saisir les données par cépage</p>
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="col-xs-3">Produits</th>
                <th class="text-center col-xs-3">Superficie</th>
                <th class="text-center col-xs-3">Volume</th>
                <th class="text-center col-xs-3">Volume VT/SGN</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($form['produits'] as $hash => $embedForm) : 
                    $produit = $drev->get($hash);
            ?>
                <tr>
                    <td><?php if($produit->getParent()->getParent()->getLibelle()): ?><?php echo $produit->getParent()->getParent()->getLibelle() ?> - <?php endif; ?><?php echo $produit->getLibelle() ?></td>
                    <td class="text-center">
                        <span class="text-danger"><?php echo $embedForm['superficie_total']->renderError() ?></span>
                        <div class="form-group">
                            <div class="col-xs-8 col-xs-offset-2">
                                <?php echo $embedForm['superficie_total']->render(array('class' => 'form-control input input-rounded text-right')) ?>
                            </div>
                        </div>
                        
                    </td>
                    <td class="text-center">
                        <span class="text-danger"><?php echo $embedForm['volume_sur_place']->renderError() ?></span>
                        <div class="form-group">
                            <div class="col-xs-8 col-xs-offset-2">
                                <?php echo $embedForm['volume_sur_place']->render(array('class' => 'form-control input input-rounded text-right')) ?>
                            </div>
                        </div>
                        
                    </td>
                    <td class="text-center">
                        <span class="text-danger"><?php echo $embedForm['volume_sur_place_vtsgn']->renderError() ?></span>
                        <div class="form-group">
                            <div class="col-xs-8 col-xs-offset-2">
                                <?php echo $embedForm['volume_sur_place_vtsgn']->render(array('class' => 'form-control input input-rounded text-right')) ?>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="row row-margin">
        <div class="col-xs-6">
            <?php if($noeud->getPreviousSister()): ?>
                <a href="<?php echo url_for('drev_revendication_cepage', array('sf_subject' => $drev, 'hash' => $noeud->getPreviousSister()->getKey())) ?>" class="btn btn-primary"><span class="eleganticon arrow_carrot-left"></span>Appellation précédente</a>
            <?php else: ?>
                 <a href="<?php echo url_for("drev_revendication", $drev) ?>" class="btn btn-primary"><span class="eleganticon arrow_carrot-left"></span>Toutes les appellations</a>
            <?php endif; ?>
        </div>
        <div class="col-xs-6 text-right">
            <?php if($noeud->getNextSister()): ?>
                <button type="submit" class="btn btn-default">Valider et saisir l'appellation suivante<span class="eleganticon arrow_carrot-right"></span></button>
            <?php else: ?>
                <button type="submit" class="btn btn-default btn-lg"><span class="eleganticon arrow_carrot-right pull-right"></span>Étape suivante</button>
            <?php endif; ?>
        </div>
    </div>
</form>