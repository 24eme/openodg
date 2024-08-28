<?php use_helper('Date'); ?>
<?php use_helper('Float'); ?>
<?php use_helper('Generation'); ?>
<?php use_javascript('degustation.js') ?>

<ol class="breadcrumb">
    <li class="active"><a href="<?php echo url_for('facturation'); ?>">Facturation</a></li>
</ol>

<div class="page-header no-border">
    <div class="pull-right">
        <?php if ($sf_user->hasDrevAdmin()): ?>
            <form method="GET" class="form-inline hidden-print" action="">
                Exercice comptable :
                <select class="select2SubmitOnChange form-control" name="campagne">
                    <?php foreach($campagnes as $campagne_i): ?>
                        <option <?php if($campagne == $campagne_i): ?>selected="selected"<?php endif; ?> value="<?php echo $campagne_i; ?>"><?php echo $campagne_i; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-default">Changer</button>
            </form>
        <?php else: ?>
            <span style="margin-top: 8px; display: inline-block;" class="text-muted">Campagne <?php echo $campagne ?></span>
        <?php endif; ?>
    </div>
    <h2>Historique des factures pour l'année <?php echo $campagne; ?></h2>
</div>

<div class="input-group" style="margin-bottom: 0; position: relative;">
    <span class="input-group-addon">Filtrer le tableau</span>
    <input id="table_filtre" type="text" class="form-control" placeholder="par date, numéro de facture, type de document, opérateur ou montants" autofocus="autofocus" />
    <a href="" id="btn_annuler_filtre" tabindex="-1" class="small hidden" style="z-index: 3; right: 10px; top: 10px; position: absolute; color: grey;"><span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span></a>
</div>
<table class="table table-bordered table-striped table_filterable" style="border-width: 0;" id ="table_factures">
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
        <?php foreach ($factures as $id => $facture) : ?>
            <tr class="searchable">
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
                <td class="text-right"><?php echo Anonymization::hideIfNeeded(echoFloat($facture->doc->total_ttc)); ?>&nbsp;€<span hidden><?php echo Anonymization::hideIfNeeded($facture->doc->total_ttc); ?></span></td>
                <td class="text-right"><?php (!isset($facture->doc->montant_paiement) || $facture->doc->montant_paiement == 0) ? $amount = "" : $amount = echoFloat((float)$facture->doc->montant_paiement) . "€"; ?>&nbsp;<?php echo $amount ?><span hidden><?php echo $facture->doc->montant_paiement ?></span></td>
            </tr>
        <?php endforeach; ?>
        <tr class="hidden"><td colspan="7">Aucun lot trouvé</td></tr>
        <?php if(!count($factures)): ?>
            <tr>
                <td colspan="<?php echo intval($sf_user->hasFactureAdmin())*2+6 ?>">Aucune facture éditée</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
