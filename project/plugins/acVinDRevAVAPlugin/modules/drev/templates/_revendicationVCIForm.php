<?php $global_error_class = ($appellation && ($appellation_hash == $key))? 'error_field_to_focused' : ''; ?>
<tr class="<?php echo (isset($form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)])) ? 'with_superficie' : ''; ?>" >
    <td><?php echo $produit->getLibelleComplet() ?> <small class="text-muted">-&nbsp;<?php echo $produit->stockage_libelle ?></small></td>
    <td class="text-right"><?php echoFloat($produit->constitue); ?>&nbsp;hl &nbsp;</td>
    <td class="text-center">
        <div class="form-group <?php if ($form['destruction']->hasError()): ?>has-error<?php endif; ?>">
            <?php echo $form['destruction']->renderError() ?>
            <div class="col-xs-10 col-xs-offset-1">
                <?php echo $form['destruction']->render(array('class' => 'disabled form-control text-right input-rounded num_float', 'placeholder' => "hl")) ?>
            </div>
        </div>
    </td>
    <td class="text-center">
        <div class="form-group <?php if ($form['complement']->hasError()): ?>has-error<?php endif; ?>">
            <?php echo $form['complement']->renderError() ?>
            <div class="col-xs-10 col-xs-offset-1">
                <?php echo $form['complement']->render(array('class' => 'disabled form-control text-right input-rounded num_float', 'placeholder' => "hl")) ?>
            </div>
        </div>
    </td>
    <td class="text-center">
        <div class="form-group <?php if ($form['substitution']->hasError()): ?>has-error<?php endif; ?>">
            <?php echo $form['substitution']->renderError() ?>
            <div class="col-xs-10 col-xs-offset-1">
                <?php echo $form['substitution']->render(array('class' => 'disabled form-control text-right input-rounded num_float', 'placeholder' => "hl")) ?>
            </div>
        </div>
    </td>
    <td class="text-center">
        <div class="form-group <?php if ($form['rafraichi']->hasError()): ?>has-error<?php endif; ?>">
            <?php echo $form['rafraichi']->renderError() ?>
            <div class="col-xs-10 col-xs-offset-1">
                <?php echo $form['rafraichi']->render(array('class' => 'disabled form-control text-right input-rounded num_float', 'placeholder' => "hl")) ?>
            </div>
        </div>
    </td>
</tr>
