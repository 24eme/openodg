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

<h3>Votre volume conditionné</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-4 text-center">Condtionnement</th>
            <th class="col-xs-8 text-center">Volume conditionné <small class="text-muted">(hl)</small></th>
        </tr>
    </thead>
    <tbody>
        <?php if ($adelphe->getTauxBibCalcule() > 0): ?>
        <tr>
            <td class="text-left">BIB <?php if($adelphe->isRepartitionForfaitaire()): ?><small>(volume forfaitaire)</small><?php endif; ?><small class="pull-right"><?php echo $adelphe->getTauxBibCalcule() ?>%</small></td>
            <td class="text-right"><?php echo sprintFloat($adelphe->volume_conditionne_bib) ?> <small class="text-muted">hl</small></td>
        </tr>
        <?php endif ?>
        <?php if ($adelphe->getTauxBouteilleCalcule() > 0): ?>
        <tr>
            <td class="text-left">Bouteille<small class="pull-right"><?php echo $adelphe->getTauxBouteilleCalcule() ?>%</small></td>
            <td class="text-right"><?php echo sprintFloat($adelphe->volume_conditionne_bouteille) ?> <small class="text-muted">hl</small></td>
        </tr>
      <?php endif; ?>
        <tr>
            <td class="text-left"><strong>Total</strong></td>
            <td class="text-right"><strong><?php echo sprintFloat($adelphe->volume_conditionne_total) ?> <small class="text-muted">hl</small></strong></td>
        </tr>
    </tbody>
</table>
<div class="well">
  <h3 class="text-center">Montant estimé de votre cotisation Adelphe</h3>
  <h3 class="text-center"><?php echo sprintFloat($adelphe->cotisation_prix_total) ?> <small class="text-muted">€</small></h3>
</div>
<?php endif; ?>
