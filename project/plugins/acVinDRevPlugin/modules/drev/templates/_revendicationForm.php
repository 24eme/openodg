<?php $global_error_class = ($appellation && ($appellation_hash == $key))? 'error_field_to_focused' : ''; ?>

<tr class="vertical-center <?php echo (isset($form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)])) ? 'with_superficie' : ''; ?>" >
    <td><?php echo $produit->getLibelleComplet() ?> <small class="text-muted">(<?php echoFloat($produit->recolte->superficie_total) ?> ha)</small> <?php if (!$vtsgn && $produit->canHaveVtsgn()):?><small class="text-muted">(hors VT/SGN)</small><?php elseif($vtsgn) : ?><span>VT/SGN</span><?php endif; ?></td>
<?php if ($drev->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR): ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'volume_total', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'recolte_nette', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
<?php endif; ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'volume_sur_place', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'vci_constitue', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form, 'produit' => $produit, 'name' => 'volume_revendique_issu_recolte', 'vtsgn' => $vtsgn, "placeholder" => "hl", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <td class="text-right">
        <span class="input_sum_value"><?php if($produit->volume_revendique_issu_vci): ?><?php echoFloat($produit->volume_revendique_issu_vci) ?></span> <small class="text-muted">hl</small><?php endif; ?>
    </td>
    <td class="text-right">
        <span class="input_sum_total"><?php echoFloat($produit->volume_revendique_issu_vci + $form['volume_revendique_issu_recolte']->getValue()) ?></span> <small class="text-muted">hl</small>
    </td>
</tr>
