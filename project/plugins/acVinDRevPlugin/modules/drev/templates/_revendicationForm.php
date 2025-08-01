<?php $global_error_class = ($appellation && ($appellation_hash == $key))? 'error_field_to_focused' : ''; ?>

<tr class="vertical-center <?php echo (isset($form['superficie_revendique'.(($vtsgn) ? "_vtsgn" : null)])) ? 'with_superficie' : ''; ?>" >
    <td><?php echo $produit->getRawValue()->getLibelleCompletHTML() ?> <small class="pull-right" style="margin-top: 3px;"><?php echoFloat($produit->superficie_revendique, 4) ?> ha</small> <?php if (!$vtsgn && $produit->canHaveVtsgn()):?><small class="text-muted">(hors VT/SGN)</small><?php elseif($vtsgn) : ?><span>VT/SGN</span><?php endif; ?></td>
<?php if ($drev->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR): ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'volume_total', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
  <?php endif; ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'volume_sur_place', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
<?php if ($drev->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR): ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'recolte_nette', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
<?php endif; ?>
<?php if (($drev->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR) || ($drev->getDocumentDouanierType() == SV11CsvFile::CSV_TYPE_SV11)): ?>
    <?php if(isset($form['recolte']['vci_constitue'])): ?>
        <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'vci_constitue', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class, 'prefixe' => ($drev->hasVSI() && $form['recolte']['vci_constitue']->getValue() != null) ? 'VCI': null)); ?>
    <?php endif; ?>
    <?php if(isset($form['recolte']['vsi'])): ?>
        <?php include_partial('drev/revendicationFormInput', array('form' => $form['recolte'], 'produit' => $produit, 'name' => 'vsi', 'vtsgn' => $vtsgn, "placeholder" => "hl", "tdClass" => "info", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class, 'prefixe' => 'VSI')); ?>
    <?php endif; ?>
<?php endif; ?>
    <?php include_partial('drev/revendicationFormInput', array('form' => $form, 'produit' => $produit, 'name' => 'volume_revendique_issu_recolte', 'vtsgn' => $vtsgn, "placeholder" => "hl", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php if($drev->hasProduitWithMutageAlcoolique()): ?>
        <?php include_partial('drev/revendicationFormInput', array('form' => $form, 'produit' => $produit, 'name' => 'volume_revendique_issu_mutage', 'vtsgn' => $vtsgn, "placeholder" => "hl", 'global_error_id' => $global_error_id, 'global_error_class' => $global_error_class)); ?>
    <?php endif; ?>
<?php if (($drev->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR) || ($drev->getDocumentDouanierType() == SV11CsvFile::CSV_TYPE_SV11)): ?>
    <td style="position: relative;" class="text-right">
        <?php if($produit->getVolumeRevendiqueIssuVsi() > 0 && $produit->getVolumeRevendiqueIssuVsi() == $produit->getVolumeRevendiqueIssuVciVsi()): ?>
        <small style="position:absolute; top: 14px; left: 14px;" class="text-muted">VSI</small>
        <?php endif; ?>
        <?php if($drev->hasVSI() > 0 && $produit->getVolumeRevendiqueIssuVci() > 0 && $produit->getVolumeRevendiqueIssuVci() == $produit->getVolumeRevendiqueIssuVciVsi()): ?>
        <small style="position:absolute; top: 14px; left: 14px;" class="text-muted">VCI</small>
        <?php endif; ?>
        <?php if($produit->getVolumeRevendiqueIssuVci() > 0 && $produit->getVolumeRevendiqueIssuVsi()): ?>
        <small style="position:absolute; top: 14px; left: 14px;" class="text-muted">VCI/VSI</small>
        <?php endif; ?>
        <span class="input_sum_value"><?php if($produit->getVolumeRevendiqueIssuVciVsi()): ?><?php echoFloat($produit->getVolumeRevendiqueIssuVciVsi()) ?></span> <small class="text-muted">hl</small><?php endif; ?>
    </td>
<?php endif; ?>
    <td class="text-right">
        <span class="input_sum_total"></span> <small class="text-muted">hl</small>
    </td>
</tr>
