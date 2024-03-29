<?php use_helper('Float'); ?>
<?php use_helper('Generation'); ?>
<table class="table table-striped table-condensed table-bordered">
    <thead>
        <tr>
            <th class="col-xs-2">Numéro</th>
            <th class="col-xs-2">Date</th>
            <th class="col-xs-2">Type</th>
            <th class="col-xs-2 text-right">Nombre de documents</th>
            <th class="col-xs-2 text-right">Total</th>
            <th class="col-xs-2 text-center">Statut</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($generations as $generation): ?>
            <?php $documents = $generation->value[GenerationClient::HISTORY_VALUES_DOCUMENTS]; ?>
            <tr>
                <td><?php echo link_to("n° ". $generation->key[GenerationClient::HISTORY_KEYS_TYPE_DATE_EMISSION], 'generation_view', ['id' => $generation->id]); ?></td>
                <td>
                    <?php echo GenerationClient::getInstance()->getDateFromIdGeneration($generation->key[GenerationClient::HISTORY_KEYS_TYPE_DATE_EMISSION]); ?>
                </td>
                <td ><?php echo $generation->key[GenerationClient::HISTORY_KEYS_TYPE_DOCUMENT]."S"; ?></td>
                <td class="text-right" ><?php echo $generation->value[GenerationClient::HISTORY_VALUES_NBDOC]; ?></td>
                <td class="text-right">
                    <?php if ($generation->value[GenerationClient::HISTORY_VALUES_SOMME]):
                    echoFloat($generation->value[GenerationClient::HISTORY_VALUES_SOMME]);
                    ?>&nbsp;€<?php endif; ?>
                </td>
                <td class="text-center"><a href="<?php echo url_for('generation_view', ['id' => $generation->id]) ?>" class="btn btn-xs btn-<?php echo statutToCssClass($generation->value[GenerationClient::HISTORY_VALUES_STATUT]) ?>"><span class="<?php echo statutToIconCssClass($generation->value[GenerationClient::HISTORY_VALUES_STATUT]) ?>"></span>&nbsp;&nbsp;<?php echo statutToLibelle($generation->value[GenerationClient::HISTORY_VALUES_STATUT]); ?></a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
