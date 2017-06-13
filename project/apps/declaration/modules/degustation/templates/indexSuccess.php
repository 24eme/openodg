<?php use_helper("Date"); ?>
<?php use_javascript("lib/chart.min.js", "last") ?>

<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
</ol>

<form action="" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row">
        <div class="col-xs-10">
            <div class="form-group <?php if($form["date"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["date"]->renderError(); ?>
                <?php echo $form["date"]->renderLabel("Date de dégustation", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-6">
                    <div class="input-group date-picker">
                        <?php echo $form["date"]->render(array("class" => "form-control")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group <?php if($form["appellation"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["appellation"]->renderError(); ?>
                <?php echo $form["appellation"]->renderLabel("Appellation / Mention", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-6">
                    <?php echo $form["appellation"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
            <div class="form-group bloc_condition" data-condition-cible="#bloc_date_prelevement">
                <div class="col-xs-8 col-xs-offset-4">
                    <?php foreach(TourneeCreationForm::getActionChoices() as $key => $libelle): ?>
                        <label class="radio-inline">
                          <input type="radio" name="<?php echo $form["action"]->renderName(); ?>" <?php if($form["action"]->getValue() == $key): ?>checked="checked"<?php endif; ?> value="<?php echo $key ?>"> <?php echo $libelle ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div id="bloc_date_prelevement" data-condition-value="organiser" class="form-group <?php if($form["date"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["date_prelevement_debut"]->renderError(); ?>
                <?php echo $form["date_prelevement_debut"]->renderLabel("Date de début des prélévements", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-6">
                    <div class="input-group date-picker">
                        <?php echo $form["date_prelevement_debut"]->render(array("class" => "form-control")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group text-right">
                <div class="col-xs-4 col-xs-offset-6">
                    <button type="submit" class="btn btn-default btn-lg btn-block btn-upper">Créer</button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="row">
    <div class="col-xs-12">
        <legend><small>Demandes de prélevements dans le temps</small></legend>
        <canvas id="graphique" width="920" class="col-xs-12" height="200"></canvas>
    </div>
</div>
<script type="text/javascript">
window.onload = function () {
        var ctx = document.getElementById("graphique").getContext("2d");
        var myNewChart = new Chart(ctx).Bar({
            labels: <?php echo json_encode(array_keys($demandes_alsace->getRawValue())) ?>,
            datasets: [
                {
                    label: "AOC Alsace",
                    fillColor: "rgba(120,120,220,0.2)",
                    strokeColor: "rgba(120,120,220,1)",
                    pointColor: "rgba(120,120,220,1)",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(120,120,220,1)",
                    data: <?php echo json_encode(array_values($demandes_alsace->getRawValue())) ?>
                },
                {
                    label: "VT / SGN",
                    fillColor: "rgba(0,220,220,0.2)",
                    strokeColor: "rgba(0,220,220,1)",
                    pointColor: "rgba(0,220,220,1)",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(0,220,220,1)",
                    data: <?php echo json_encode(array_values($demandes_vtsgn->getRawValue())) ?>
                },
            ]
        }, {multiTooltipTemplate: "<%= datasetLabel %> - <%= value %>"} );
};
</script>

<h3>Liste des tournées de dégustation</h3>
<table class="table table-bordered table-striped table-condensed">
    <thead>
        <tr>
            <th class="col-xs-3">Date</th>
            <th class="col-xs-4">Produit</th>
            <th class="col-xs-3">Infos</th>
            <th class="col-xs-2">Statut</th>
        </tr>
    </thead>
    <?php foreach($tournees as $tournee): ?>
        <?php $t = $tournee->getRawValue(); ?>
        <?php $nb_operateurs = count((array) $t->degustations); ?>
        <?php $nb_degustateurs = 0; foreach($t->degustateurs as $degustateursType): $nb_degustateurs += count((array) $degustateursType); endforeach; ?>
        <?php $nb_tournees = 0; foreach($t->agents as $agent): $nb_tournees += count((array) $agent->dates); endforeach; ?>
        <?php $nb_prelevements = 0; ?>
    <tr>
        <td><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></td>
        <td><?php echo (isset($tournee->libelle)) ? $tournee->libelle : $tournee->appellation; ?></td>
        <td><?php echo $tournee->nombre_prelevements ?> prélèvements<br />
            <span class="text-muted"><?php echo $nb_operateurs ?> opérateurs <small>(<?php echo $nb_tournees; ?> tournées)</small></span>
        </td>
        <td class="text-center"><a href="<?php if (in_array($tournee->statut, array(TourneeClient::STATUT_SAISIE, TourneeClient::STATUT_ORGANISATION))): ?><?php echo url_for('degustation_edit', $tournee) ?><?php else: ?><?php echo url_for('degustation_visualisation', $tournee) ?><?php endif; ?>" class="btn btn-block btn-sm btn-<?php echo TourneeClient::$couleursStatut[$tournee->statut] ?>"><?php echo TourneeClient::$statutsLibelle[$tournee->statut] ?></a></td>
    </tr>
    <?php endforeach; ?>
</table>
