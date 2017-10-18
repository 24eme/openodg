<?php $global_error_class = ($appellation && ($appellation_hash == $key))? 'error_field_to_focused' : ''; ?>

<tr class="vertical-center <?php echo (isset($form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)])) ? 'with_superficie' : ''; ?>" >
    <td><?php echo $produit->getLibelleComplet() ?> <small class="text-muted">(<?php echoFloat($produit->recolte->superficie_total) ?> ha)</small> <?php if (!$vtsgn && $produit->canHaveVtsgn()):?><small class="text-muted">(hors VT/SGN)</small><?php elseif($vtsgn) : ?><span>VT/SGN</span><?php endif; ?></td>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'volume_total', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'recolte_nette', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'volume_sur_place', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form, 'produit' => $produit, 'name' => 'volume_revendique_issu_recolte', 'vtsgn' => $vtsgn, "placeholder" => "ha", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <td>
        <input readonly="readonly" type="text" class="form-control text-right input-float input_sum_total" value="<?php echoFloat($produit->volume_revendique_issu_vci) ?>" />
    </td>
    <td>
        <input readonly="readonly" type="text" class="form-control text-right input-float input_sum_total" value="<?php echoFloat($produit->volume_revendique_total) ?>" />
    </td>
</tr>
