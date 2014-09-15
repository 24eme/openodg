<?php include_partial('drev/step', array('step' => 'revendication', 'drev' => $drev)) ?>

<?php include_partial('drev/stepRevendication', array('drev' => $drev, 'noeud' => $noeud)) ?>

<form role="form" action="" method="post">
    <div class="tab-content">
        <div class="tab-pane active">
            <?php echo $form->renderHiddenFields(); ?>
            <?php echo $form->renderGlobalErrors(); ?>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="col-xs-9">Produits</th>
                        <th class="text-center col-xs-3">Volume revendiqué</th>
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
                                <span class="text-danger"><?php echo $embedForm['volume_sur_place_revendique']->renderError() ?></span>
                                <div class="form-group">
                                    <div class="col-xs-8 col-xs-offset-2">
                                        <?php echo $embedForm['volume_sur_place_revendique']->render(array('class' => 'form-control input input-rounded')) ?>
                                    </div>
                                </div>
                                
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="row row-margin">
                <div class="col-xs-6">
                    <a href="" class="btn btn-default"><span class="eleganticon arrow_carrot-left"></span>Appellation précédente</a>
                </div>
                
                <div class="col-xs-6 text-right">
                    <button type="submit" class="btn btn-default">Appellation suivante<span class="eleganticon arrow_carrot-right"></span></button>
                </div>
            </div>
        </div>
    </div>
</form>