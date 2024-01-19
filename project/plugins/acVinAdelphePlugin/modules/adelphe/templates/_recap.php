<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', array('etablissement' => $adelphe->getEtablissementObject())); ?>
</div>

<h3>Vos conditionnements</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-1 text-center"></th>
            <th class="col-xs-6 text-center">Volume conditionné<small> (hl)</small></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="col-xs-1 text-center">BIB</td>
            <td class="text-center"><?php echo $adelphe->volume_conditionne_bib ?></td>
        </tr>
        <tr>
            <td class="col-xs-1 text-center">Bouteille</td>
            <td class="text-center"><?php echo $adelphe->volume_conditionne_bouteille ?></td>
        </tr>
        <tr>
            <td class="col-xs-1 text-center"><strong>Total</strong></td>
            <td class="text-center"><?php echo $adelphe->volume_conditionne_total ?></td>
        </tr>
    </tbody>
</table>
