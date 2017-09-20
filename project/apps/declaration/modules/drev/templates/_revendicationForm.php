<?php $global_error_class = ($appellation && ($appellation_hash == $key))? 'error_field_to_focused' : ''; ?>

<tr class="<?php echo (isset($form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)])) ? 'with_superficie' : ''; ?>" >
    <td class="vertical-center"><?php echo $produit->getLibelleComplet() ?> <?php if (!$vtsgn && $produit->canHaveVtsgn()):?><small class="text-muted">(hors VT/SGN)</small><?php elseif($vtsgn) : ?><span>VT/SGN</span><?php endif; ?></td>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['detail'], 'produit' => $produit, 'name' => 'superficie_total', 'vtsgn' => $vtsgn, "placeholder" => "ares", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['detail'], 'produit' => $produit, 'name' => 'volume_total', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['detail'], 'produit' => $produit, 'name' => 'recolte_nette', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['detail'], 'produit' => $produit, 'name' => 'volume_sur_place', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form, 'produit' => $produit, 'name' => 'superficie_revendique', 'vtsgn' => $vtsgn, "placeholder" => "ares", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form, 'produit' => $produit, 'name' => 'volume_revendique_sans_vci', 'vtsgn' => $vtsgn, "placeholder" => "hl", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form, 'produit' => $produit, 'name' => 'vci_complement_dr', 'vtsgn' => $vtsgn, "placeholder" => "hl", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <td>
        <input readonly="readonly" type="text" class="form-control text-right input-rounded num_float" value="<?php echoFloat($produit->volume_revendique_avec_vci) ?>" />
    </td>
    <td class="text-center"><input type="checkbox" /></td>
</tr>
