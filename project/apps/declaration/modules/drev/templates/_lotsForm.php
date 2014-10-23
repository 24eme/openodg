    <?php if ($sf_user->hasFlash('notice')): ?>
<div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>
<?php if ($sf_user->hasFlash('erreur')): ?>
<p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('erreur') ?></p>
<?php endif; ?>

<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>

<table class="table table-striped<?php if ($form->hasGlobalErrors()): ?> has-error<?php endif; ?>">
    <thead>
        <tr>
            <th class="col-xs-6"><?php echo $title ?></th>
            <th class="text-center col-xs-6">Lots Hors VT / SGN</th>
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
                    <div class="form-group <?php if($embedForm['nb_hors_vtsgn']->hasError()): ?>has-error<?php endif; ?>">
                        <?php echo $embedForm['nb_hors_vtsgn']->renderError() ?>
                        <div class="col-xs-4 col-xs-offset-4">
                            <?php echo $embedForm['nb_hors_vtsgn']->render(array('class' => 'form-control input num_int input-rounded text-right')) ?>
                        </div>
                    </div>
                    
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