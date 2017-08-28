<?php $global_error_class = ($appellation && ($appellation_hash == $key))? 'error_field_to_focused' : ''; ?>
<tr class="<?php echo (isset($form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)])) ? 'with_superficie' : ''; ?>" >
    <td><?php echo $produit->getLibelleComplet() ?> <?php if (!$vtsgn && $produit->canHaveVtsgn()):?><small class="text-muted">(hors VT/SGN)</small><?php elseif($vtsgn) : ?><span>VT/SGN</span><?php endif; ?></td>
    <?php if ($drev->hasDR()): ?>
        <?php if (!$produit->get('detail'.(($vtsgn) ? "_vtsgn" : null))->superficie_total): ?>
            <td class="info"><input name="" value="" class="form-control text-right input-rounded num_float  num_light num_float" placeholder="ares" autocomplete="off" id="" type="text"></td>
            <td class="info"><input name="" value="" class="form-control text-right input-rounded num_float  num_light num_float" placeholder="hl" autocomplete="off" id="" type="text"></td>
            <td class="info"><input name="" value="" class="form-control text-right input-rounded num_float  num_light num_float" placeholder="hl" autocomplete="off" id="" type="text"></td>
            <td class="info"><input name="" value="" class="form-control text-right input-rounded num_float  num_light num_float" placeholder="hl" autocomplete="off" id="" type="text"></td>
        <?php else: ?>
            <td class="text-right info small">
              <?php echoFloat($produit->get('detail'.(($vtsgn) ? "_vtsgn" : null))->volume_sur_place); ?>&nbsp;<small>hl</small>
            </td>
            <td class="text-right info small">
              <?php echoFloat($produit->get('detail'.(($vtsgn) ? "_vtsgn" : null))->volume_total); ?>&nbsp;<small >hl</small>
            </td>
            <td class="text-right info small">
              <?php echoFloat($produit->get('detail'.(($vtsgn) ? "_vtsgn" : null))->usages_industriels_total); ?>&nbsp;<small>hl</small>
            </td>
    <?php endif; ?>
    <?php endif; ?>
    <td class="text-center">
        <?php if (isset($form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)])): ?>
        <?php
        $global_error_class = ((($global_error_class == 'error_field_to_focused') && $appellation_field == 'surface') ||
        ('drev_produits[produits]' . $global_error_id == $form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)]->renderName())) ?
        'error_field_to_focused' : '';
        ?>
        <div class="<?php if ($global_error_class): ?>has-error<?php endif; ?>">
            <?php echo $form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)]->renderError() ?>
            <?php echo $form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)]->render(array('class' => 'form-control text-right input-rounded num_float ' . $global_error_class, 'placeholder' => "ares")) ?>
        </div>
        <?php else: ?>
            <?php echoFloat($produit->get("detail".(($vtsgn) ? "_vtsgn" : null))->superficie_total); ?>
        <?php endif; ?>
    </td>
    <td class="text-center">
    	<?php if (isset($form['superficie_vinifiee'.(($vtsgn) ? "_vtsgn" : null)])): ?>
        <?php
        $global_error_class = ((($global_error_class == 'error_field_to_focused') && $appellation_field == 'surface') ||
        ('drev_produits[produits]' . $global_error_id == $form['superficie_vinifiee'.(($vtsgn) ? "_vtsgn" : null)]->renderName())) ?
        'error_field_to_focused' : '';
        ?>
        <div class="<?php if ($global_error_class): ?>has-error<?php endif; ?>">
            <?php echo $form['superficie_vinifiee'.(($vtsgn) ? "_vtsgn" : null)]->renderError() ?>
                <?php echo $form['superficie_vinifiee'.(($vtsgn) ? "_vtsgn" : null)]->render(array('class' => 'disabled form-control text-right input-rounded num_float' . $global_error_class, 'placeholder' => "hl")) ?>
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
        <div class="<?php if ($global_error_class): ?>has-error<?php endif; ?>">
            <?php echo $form['superficie_vinifiee'.(($vtsgn) ? "_vtsgn" : null)]->renderError() ?>
                <?php echo $form['superficie_vinifiee'.(($vtsgn) ? "_vtsgn" : null)]->render(array('class' => 'disabled form-control text-right input-rounded num_float' . $global_error_class, 'placeholder' => "hl")) ?>
        </div>
        <?php endif; ?>
    </td>
    <td class=""><input name="" value="" class="form-control text-right input-rounded num_float  num_light num_float" placeholder="hl" autocomplete="off" id="" type="text"></td>
</tr>
