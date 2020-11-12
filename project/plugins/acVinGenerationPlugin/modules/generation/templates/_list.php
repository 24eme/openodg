<?php use_helper('Float'); ?>
<?php use_helper('Generation'); ?>

<div class="row row-margin">
    <div class="col-xs-12">
        <div class="list-group">
          <form class="" action="action.class.php" method="GET"><input class="form-control" id="inpNumeroFacture" type="text" name="numeroFacture" placeholder="Recherche par numéro de facture" ></form>
          <button id="btnRechercheFacture" class="col-xs-12 btn btn-default btn-lg" type="button" name="button">Recherche par numéro de facture</button>
            <?php foreach ($generations as $generation) : ?>
            <?php $documents = $generation->value[GenerationClient::HISTORY_VALUES_DOCUMENTS]; ?>
                <a href="<?php echo url_for('generation_view', array('type_document' => $generation->key[GenerationClient::HISTORY_KEYS_TYPE_DOCUMENT], 'date_emission' => $generation->key[GenerationClient::HISTORY_KEYS_TYPE_DATE_EMISSION])) ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-3 text-muted"><?php echo GenerationClient::getInstance()->getDateFromIdGeneration($generation->key[GenerationClient::HISTORY_KEYS_TYPE_DATE_EMISSION]); ?></span>
                    <span class="col-xs-3 text-muted"><?php echo Anonymization::hideIfNeeded($generation->value[GenerationClient::HISTORY_VALUES_LIBELLE]); ?></span>
                    <span class="col-xs-2 text-muted text-right"><?php echo $generation->value[GenerationClient::HISTORY_VALUES_NBDOC]; ?> document<?php if($generation->value[GenerationClient::HISTORY_VALUES_NBDOC]):?>s<?php endif; ?></span>
                    <span class="col-xs-2 text-muted text-right">
                    <?php if($generation->value[GenerationClient::HISTORY_VALUES_SOMME]): ?><?php echoFloat(Anonymization::hideIfNeeded(($generation->value[GenerationClient::HISTORY_VALUES_SOMME]) ? $generation->value[GenerationClient::HISTORY_VALUES_SOMME]: 0));?>&nbsp;€<?php endif; ?>
                    </span>
                    <span class="col-xs-2 text-muted text-right"><span class="label label-<?php echo statutToCssClass($generation->value[GenerationClient::HISTORY_VALUES_STATUT]) ?>"><span class="<?php echo statutToIconCssClass($generation->value[GenerationClient::HISTORY_VALUES_STATUT]) ?>"></span>&nbsp;&nbsp;<?php echo statutToLibelle($generation->value[GenerationClient::HISTORY_VALUES_STATUT]); ?></span></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<script type="text/javascript">
var factures = [];
<?php foreach ($generations as $generation) :?>
var url = "<?php echo url_for('generation_view', array('type_document' => $generation->key[GenerationClient::HISTORY_KEYS_TYPE_DOCUMENT], 'date_emission' => $generation->key[GenerationClient::HISTORY_KEYS_TYPE_DATE_EMISSION])) ?>";
url = url.substring(url.lastIndexOf("/") + 1, url.length) ;
factures.push(url);
<?php endforeach; ?>
</script>
