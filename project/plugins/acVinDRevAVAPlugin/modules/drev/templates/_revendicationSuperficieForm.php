<?php $global_error_class = ($appellation && ($appellation_hash == $key))? 'error_field_to_focused' : ''; ?>
<tr class="<?php echo (isset($form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)])) ? 'with_superficie' : ''; ?>" >
    <td><?php echo $produit->getLibelleComplet() ?> <?php if (!$vtsgn && $produit->canHaveVtsgn()):?><small class="text-muted">(hors VT/SGN)</small><?php elseif($vtsgn) : ?><span>VT/SGN</span><?php endif; ?></td>
    <?php if ($drev->hasDR()): ?>
        <?php if (!$produit->get('detail'.(($vtsgn) ? "_vtsgn" : null))->superficie_total): ?>
            <td class="striped-success"></td>
            <td class="striped-success"></td>
            <td class="striped-success"></td>
            <?php if($drev->declaration->hasVci()): ?>
            <td class="striped-success"></td>
            <?php endif; ?>
        <?php else: ?>
            <td class="text-right striped-success small">
              <?php echoFloat($produit->get('detail'.(($vtsgn) ? "_vtsgn" : null))->volume_sur_place); ?>&nbsp;<small>hl</small>
            </td>
            <td class="text-right striped-success small">
              <?php echoFloat($produit->get('detail'.(($vtsgn) ? "_vtsgn" : null))->volume_total); ?>&nbsp;<small >hl</small>
            </td>
            <td class="text-right striped-success small">
              <?php echoFloat($produit->get('detail'.(($vtsgn) ? "_vtsgn" : null))->usages_industriels_total); ?>&nbsp;<small>hl</small>
            </td>
            <?php if($drev->declaration->hasVci()): ?>
            <td class="text-right striped-success small">
                <?php if ($produit->get('detail'.(($vtsgn) ? "_vtsgn" : null))->vci_total): ?>
                    <?php echoFloat($produit->get('detail'.(($vtsgn) ? "_vtsgn" : null))->vci_total); ?>&nbsp;<small>hl</small>
                <?php endif; ?>
            </td>
            <?php endif; ?>
    <?php endif; ?>
    <?php endif; ?>
    <td class="text-center">
        <?php if (isset($form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)])): ?>
        <?php
        $global_error_class = ((($global_error_class == 'error_field_to_focused') && $appellation_field == 'surface') ||
        ('drev_produits[produits]' . $global_error_id == $form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)]->renderName())) ?
        'error_field_to_focused' : '';
        ?>
        <div class="form-group <?php if ($global_error_class): ?>has-error<?php endif; ?>">
            <?php echo $form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)]->renderError() ?>
            <div class="col-xs-10 col-xs-offset-1">
                <?php echo $form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)]->render(array('class' => 'form-control text-right input-rounded num_float ' . $global_error_class, 'placeholder' => "ares")) ?>
            </div>
        </div>
        <?php else: ?>
            <div class="col-xs-10 text-right">
            <?php echoFloat($produit->get("detail".(($vtsgn) ? "_vtsgn" : null))->superficie_total); ?>
            </div>
        <?php endif; ?>
    </td>
    <td class="text-center">
    	<?php if (isset($form['superficie_vinifiee'.(($vtsgn) ? "_vtsgn" : null)])): ?>
        <?php
        $global_error_class = ((($global_error_class == 'error_field_to_focused') && $appellation_field == 'surface') ||
        ('drev_produits[produits]' . $global_error_id == $form['superficie_vinifiee'.(($vtsgn) ? "_vtsgn" : null)]->renderName())) ?
        'error_field_to_focused' : '';
        ?>
        <div class="form-group <?php if ($global_error_class): ?>has-error<?php endif; ?>">
            <?php echo $form['superficie_vinifiee'.(($vtsgn) ? "_vtsgn" : null)]->renderError() ?>
            <div class="col-xs-10 col-xs-offset-1">
                <?php echo $form['superficie_vinifiee'.(($vtsgn) ? "_vtsgn" : null)]->render(array('class' => 'disabled form-control text-right input-rounded num_float' . $global_error_class, 'placeholder' => "ares")) ?>
            </div>
        </div>
        <?php endif; ?>
    </td>
</tr>
