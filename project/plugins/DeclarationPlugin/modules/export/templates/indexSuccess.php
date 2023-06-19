<ol class="breadcrumb">
    <li class="active"><a href="<?php echo url_for('export'); ?>">Génération</a></li>
</ol>

<p class="alert alert-warning text-center" style="margin-bottom: 40px;">
    Cette page est dépréciée, il est préférable d'utiliser directement les exports réalisés automatiquement 2 fois par jour.
    <br /><br />
    <?php if(NavConfiguration::getInstance()->hasStatLinks()): ?>
        <?php foreach(NavConfiguration::getInstance()->getStatLinks() as $i => $navItem): ?>
         <?php if(isset($navItem['title']) && $navItem['title'] == 'Exports'): ?><a href="<?php echo $navItem['url'] ?>" class="btn btn-default">Voir les exports</a><?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</p>

<form method="post" action="" role="form" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row">
        <div class="col-xs-6 col-xs-offset-3">
            <div class="form-group <?php if($form["generation"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["generation"]->renderError() ?>
                <div class="col-xs-12">
                <?php echo $form["generation"]->render(array("class" => "form-control select2 select2-offscreen select2autocomplete", "autofocus" => "autofocus", "placeholder" => "Sélectionner un export")); ?>
                </div>
            </div>

            <div class="form-group <?php if($form["search"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["search"]->renderError() ?>
                <div class="col-xs-12">
                <?php echo $form["search"]->render(array("class" => "form-control","placeholder" => "Ajouter une recherche pour l'export")); ?>
                </div>
            </div>

            <div class="form-group text-right">
                <div class="col-xs-6 col-xs-offset-6">
                    <button class="btn btn-default btn-block btn-upper" type="submit">Générer l'export</button>
                </div>
            </div>
        </div>
    </div>
</form>


<?php use_helper('Float'); ?>
<?php use_helper('Generation'); ?>
<table class="table table-striped table-condensed table-bordered">
    <thead>
        <tr>
            <th class="col-xs-2">Numéro</th>
            <th class="col-xs-2">Date</th>
            <th class="col-xs-3">Libellé</th>
            <th class="col-xs-1 text-right">Nombre de documents</th>
            <th class="col-xs-2 text-center">Statut</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($generationsList as $generation): ?>
            <?php $documents = $generation->value[GenerationClient::HISTORY_VALUES_DOCUMENTS]; ?>
            <tr>
                <td><?php echo link_to("n° ". $generation->key[GenerationClient::HISTORY_KEYS_TYPE_DATE_EMISSION], 'generation_view', ['id' => $generation->id]); ?></td>
                <td>
                    <?php echo GenerationClient::getInstance()->getDateFromIdGeneration($generation->key[GenerationClient::HISTORY_KEYS_TYPE_DATE_EMISSION]); ?>
                </td>
                <td>
                    <?php echo $generation->value[GenerationClient::HISTORY_VALUES_LIBELLE] ?>
                </td>
                <td class="text-right" ><?php echo $generation->value[GenerationClient::HISTORY_VALUES_NBDOC]; ?></td>
                <td class="text-center"><a href="<?php echo url_for('generation_view', ['id' => $generation->id]) ?>" class="btn btn-xs btn-<?php echo statutToCssClass($generation->value[GenerationClient::HISTORY_VALUES_STATUT]) ?>"><span class="<?php echo statutToIconCssClass($generation->value[GenerationClient::HISTORY_VALUES_STATUT]) ?>"></span>&nbsp;&nbsp;<?php echo statutToLibelle($generation->value[GenerationClient::HISTORY_VALUES_STATUT]); ?></a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
