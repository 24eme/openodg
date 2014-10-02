    <?php if ($sf_user->hasFlash('notice')): ?>
<div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>
<?php if ($sf_user->hasFlash('erreur')): ?>
<p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('erreur') ?></p>
<?php endif; ?>

<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>

<table class="table table-striped">
    <thead>
        <tr>
            <th class="col-xs-6">Produits</th>
            <th class="text-center col-xs-3">Lots Hors VT / SGN</th>
            <th class="text-center col-xs-3">Lots VT / SGN</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            foreach ($form['lots'] as $key => $embedForm) : 
                $produit = $form->getObject()->lots->get($key);
        ?>
            <tr>
                <td><?php echo $produit->getLibelle() ?></td>
                <td class="text-center">
                    <span class="text-danger"><?php echo $embedForm['nb_hors_vtsgn']->renderError() ?></span>
                    <div class="form-group">
                        <div class="col-xs-8 col-xs-offset-2">
                            <?php echo $embedForm['nb_hors_vtsgn']->render(array('class' => 'form-control input input-rounded')) ?>
                        </div>
                    </div>
                    
                </td>
                <td class="text-center">
                    <?php if(isset($embedForm['nb_vtsgn'])): ?>
                        <span class="text-danger"><?php echo $embedForm['nb_vtsgn']->renderError() ?></span>
                        <div class="form-group">
                            <div class="col-xs-8 col-xs-offset-2">
                                <?php echo $embedForm['nb_vtsgn']->render(array('class' => 'form-control input input-rounded')) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (isset($ajoutForm) && $ajoutForm->hasProduits()): ?>
            <tr>
                <td>
                    <button class="btn btn-warning ajax btn-sm" data-toggle="modal" data-target="#popupForm" type="button">Ajouter un produit&nbsp;<span class="eleganticon icon_plus"></span></button>
                </td>
                <td></td>
                <td></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>