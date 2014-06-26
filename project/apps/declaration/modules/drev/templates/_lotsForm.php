<?php if ($sf_user->hasFlash('notice')): ?>
<p class="bg-success"><?php echo $sf_user->getFlash('notice') ?></p>
<?php endif; ?>
<?php if ($sf_user->hasFlash('erreur')): ?>
<p class="bg-danger"><?php echo $sf_user->getFlash('erreur') ?></p>
<?php endif; ?>

<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>

<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th class="col-md-6">Cépages</th>
            <th class="text-center col-md-3">Lots Hors VT / SGN</th>
            <th class="text-center col-md-3">Lots VT / SGN</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            foreach ($form['produits'] as $key => $embedForm) : 
                $produit = $form->getObject()->produits->get($key);
        ?>
            <tr>
                <td><?php echo $produit->getLibelle() ?></td>
                <td class="text-center">
                    <span class="text-danger"><?php echo $embedForm['nb_hors_vtsgn']->renderError() ?></span>
                    <?php echo $embedForm['nb_hors_vtsgn']->render(array('class' => 'text-right')) ?>
                </td>
                <td class="text-center">
                    <?php if(isset($embedForm['nb_vtsgn'])): ?>
                        <span class="text-danger"><?php echo $embedForm['nb_vtsgn']->renderError() ?></span>
                        <?php echo $embedForm['nb_vtsgn']->render(array('class' => 'text-right')) ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>