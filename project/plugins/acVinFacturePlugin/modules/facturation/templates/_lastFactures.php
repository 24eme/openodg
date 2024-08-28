<?php use_helper('Date'); ?>
<?php use_helper('Float'); ?>
<?php use_helper('Generation'); ?>

<table class="table table-bordered table-striped" style="margin-bottom: 0;">
    <thead>
        <tr>
            <th class="col-xs-1">Date</th>
            <th class="col-xs-2">Numéro</th>
            <th class="col-xs-1">Type</th>
            <th class="col-xs-4">Opérateur</th>
            <th class="col-xs-2 text-right">Montant TTC Facture</th>
            <th class="col-xs-2 text-right">Montant payé</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($factures as $id => $facture): ?>
        <tr>
            <td><?php echo format_date($facture->doc->date_facturation, "dd/MM/yyyy", "fr_FR"); ?></td>
            <td>N°&nbsp;<?php echo $facture->doc->numero_odg ?></td>
            <td><?php if($facture->doc->total_ht < 0.0): ?>AVOIR<?php else: ?>FACTURE<?php endif; ?></td>
            <td>
                <?php if(FactureConfiguration::getInstance()->isListeDernierExercice()): ?>
                    <a href="<?php echo url_for('facturation_declarant', array('identifiant' => $facture->doc->identifiant, "campagne" => $facture->doc->campagne)); ?>"><?php echo $facture->doc->declarant->nom ?></a>
                <?php else: ?>
                    <a href="<?php echo url_for('facturation_declarant', array('identifiant' => $facture->doc->identifiant)); ?>"><?php echo $facture->doc->declarant->nom ?></a>
                <?php endif; ?>
            </td>
            <td class="text-right"><?php echo Anonymization::hideIfNeeded(echoFloat($facture->doc->total_ttc)); ?>&nbsp;€</td>
            <td class="text-right"><?php (!isset($facture->doc->montant_paiement) || $facture->doc->montant_paiement == 0) ? $amount = "" : $amount = $facture->doc->montant_paiement . "€"; ?>&nbsp;<?php echo $amount ?></td>
        </tr>
    <?php endforeach;
    if(!count($factures)):
        ?>
        <tr>
            <td colspan="<?php echo intval($sf_user->hasFactureAdmin())*2+6 ?>">Aucune facture éditée</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
