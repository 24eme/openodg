<?php use_helper('Float'); ?>
<?php use_helper('Generation'); ?>

<div class="row row-margin">
    <div class="col-xs-12">
        <div class="list-group">
            <?php foreach ($generations as $generation) : ?>
            <?php $documents = $generation->value[GenerationClient::HISTORY_VALUES_DOCUMENTS]; ?>
                <a href="<?php echo url_for('generation_view', array('type_document' => GenerationClient::TYPE_DOCUMENT_FACTURES, 'date_emission' => $generation->key[GenerationClient::HISTORY_KEYS_TYPE_DATE_EMISSION])) ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-3 text-muted"><?php echo GenerationClient::getInstance()->getDateFromIdGeneration($generation->key[GenerationClient::HISTORY_KEYS_TYPE_DATE_EMISSION]); ?></span>
                    <span class="col-xs-3 text-muted-alt">N° <?php echo $generation->key[GenerationClient::HISTORY_KEYS_TYPE_DATE_EMISSION] ?></span>
                    <span class="col-xs-2 text-muted text-right"><?php echo $generation->value[GenerationClient::HISTORY_VALUES_NBDOC]; ?> Factures</span>
                    <span class="col-xs-2 text-muted text-right">
                    <?php echoFloat(($generation->value[GenerationClient::HISTORY_VALUES_SOMME]) ? $generation->value[GenerationClient::HISTORY_VALUES_SOMME]: 0);?>&nbsp;€
                    </span>
                    <span class="col-xs-2 text-muted text-right"><span class="label label-<?php echo statutToCssClass($generation->value[GenerationClient::HISTORY_VALUES_STATUT]) ?>"><span class="<?php echo statutToIconCssClass($generation->value[GenerationClient::HISTORY_VALUES_STATUT]) ?>"></span>&nbsp;&nbsp;<?php echo statutToLibelle($generation->value[GenerationClient::HISTORY_VALUES_STATUT]); ?></span></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>