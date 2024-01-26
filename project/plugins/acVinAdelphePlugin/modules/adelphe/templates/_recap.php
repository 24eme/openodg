<?php use_helper('Float'); ?>
<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', array('etablissement' => $adelphe->getEtablissementObject())); ?>
</div>

<?php if ($adelphe->redirect_adelphe): ?>

<p class="bg-warning text-center p-5 mb-5">
    <span class="glyphicon glyphicon-alert"></span><br />
    Votre volume conditionné déclaré est supérieur au seuil géré par votre syndicat. Vous devez déclarer directement sur le site de l'ADELPHE.
</p>

<?php else: ?>

<h3>Votre contribution Adelphe</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-2 text-center">Condtionnement</th>
            <th class="col-xs-4 text-center">Volume conditionné <small class="text-muted">(hl)</small></th>
            <th class="col-xs-2 text-center">Prix unitaire <small class="text-muted">(€)</small></th>
            <th class="col-xs-4 text-center">Prix total <small class="text-muted">(€)</small></th>
        </tr>
    </thead>
    <tbody>
        <?php if ($adelphe->getTauxBibCalcule() > 0): ?>
        <tr>
            <td class="text-left">BIB <?php if($adelphe->isRepartitionForfaitaire()): ?><small>(volume forfaitaire)</small><?php endif; ?><small class="pull-right"><?php echo $adelphe->getTauxBibCalcule() ?>%</small></td>
            <td class="text-right"><?php echo sprintFloat($adelphe->volume_conditionne_bib) ?> <small class="text-muted">hl</small></td>
            <td class="text-right"><?php echo sprintf("%.4f", $adelphe->prix_unitaire_bib*1) ?> <small class="text-muted">€</small></td>
            <td class="text-right"><?php echo sprintFloat($adelphe->getPrixBib()) ?> <small class="text-muted">€</small></td>
        </tr>
        <?php endif ?>
        <?php if ($adelphe->getTauxBouteilleCalcule() > 0): ?>
        <tr>
            <td class="text-left">Bouteille<small class="pull-right"><?php echo $adelphe->getTauxBouteilleCalcule() ?>%</small></td>
            <td class="text-right"><?php echo sprintFloat($adelphe->volume_conditionne_bouteille) ?> <small class="text-muted">hl</small></td>
            <td class="text-right"><?php echo sprintf("%.4f", $adelphe->prix_unitaire_bouteille) ?> <small class="text-muted">€</small></td>
            <td class="text-right"><?php echo sprintFloat($adelphe->getPrixBouteille()) ?> <small class="text-muted">€</small></td>
        </tr>
      <?php endif; ?>
        <tr>
            <td class="text-left"><strong>Total</strong></td>
            <td class="text-right"><strong><?php echo sprintFloat($adelphe->volume_conditionne_total) ?> <small class="text-muted">hl</small></strong></td>
            <td class="text-right"></td>
            <td class="text-right"><strong><?php echo sprintFloat($adelphe->getPrixTotal()) ?> <small class="text-muted">€</small></strong></td>
        </tr>
    </tbody>
</table>

<?php endif; ?>
