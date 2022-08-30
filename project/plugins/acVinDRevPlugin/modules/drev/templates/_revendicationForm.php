<?php $global_error_class = ($appellation && ($appellation_hash == $key))? 'error_field_to_focused' : ''; ?>

<tr class="vertical-center <?php echo (isset($form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)])) ? 'with_superficie' : ''; ?>" >
    <td><?php echo $produit->getRawValue()->getLibelleCompletHTML() ?> <small class="pull-right" style="margin-top: 3px;"><?php echoFloat(round($produit->recolte->superficie_total, 2)) ?> ha</small> <?php if (!$vtsgn && $produit->canHaveVtsgn()):?><small class="text-muted">(hors VT/SGN)</small><?php elseif($vtsgn) : ?><span>VT/SGN</span><?php endif; ?></td>
<?php if ($drev->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR): ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'volume_total', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
  <?php endif; ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'volume_sur_place', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
<?php if ($drev->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR): ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'recolte_nette', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
<?php endif; ?>
<?php if (($drev->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR) || ($drev->getDocumentDouanierType() == SV11CsvFile::CSV_TYPE_SV11)): ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'vci_constitue', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
<?php endif; ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form, 'produit' => $produit, 'name' => 'volume_revendique_issu_recolte', 'vtsgn' => $vtsgn, "placeholder" => "hl", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php if($drev->hasProduitWithMutageAlcoolique()): ?>
        <?php include_partial('drev/revendicationFormInput', array('form' => $form, 'produit' => $produit, 'name' => 'volume_revendique_issu_mutage', 'vtsgn' => $vtsgn, "placeholder" => "hl", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php endif; ?>
<?php if (($drev->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR) || ($drev->getDocumentDouanierType() == SV11CsvFile::CSV_TYPE_SV11)): ?>
    <td class="text-right">
        <span class="input_sum_value"><?php if($produit->volume_revendique_issu_vci): ?><?php echoFloat($produit->volume_revendique_issu_vci) ?></span> <small class="text-muted">hl</small><?php endif; ?>
    </td>
<?php endif; ?>
    <td class="text-right">
        <span class="input_sum_total"></span> <small class="text-muted">hl</small>
    </td>
</tr>
