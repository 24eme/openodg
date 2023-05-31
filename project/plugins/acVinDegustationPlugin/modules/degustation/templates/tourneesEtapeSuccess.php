<?php use_helper('Float') ?>
<?php use_javascript('hamza_style.js'); ?>
<?php use_javascript('degustation.js'); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_TOURNEES)); ?>

<form action="<?php echo url_for("degustation_tournees_etape", $degustation) ?>" method="post" class="ajaxForm form-horizontal degustation tournees">
<?php
echo $form->renderHiddenFields();
echo $form->renderGlobalErrors();
?>
    <div class="row">
    <div class="col-xs-3">
        <div class="panel panel-default" style="min-height: 160px">
        <div class="panel-heading">
            <h2 class="panel-title">
            Liste des tournées
            </h2>
        </div>
        <div class="list-group">
            <?php foreach ($form->getRegions() as $region): ?>
            <a href="#" class="list-group-item <?php if($secteur == $region): ?>active<?php endif; ?>">
                <?php echo $region; ?>
            </a>
            <?php endforeach; ?>
            <a href="#" class="list-group-item">
                Récapitulatif
            </a>
        </div>
        </div>
    </div>
    <div class="col-xs-9">
        <div class="btn-group pull-right">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                Export PDF <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a href="#tournees">Tournées</a></li>
                <li><a href="#etiquettes">Etiquettes</a></li>
            </ul>
        </div>
        <h2 style="margin-top: 0; margin-bottom: 20px;">Tournée <?php echo $secteur ?></h2>
        <table class="table table-bordered table-striped table-condensed">
        <thead>
            <tr>
            <th class="col-xs-3 text-left">Opérateur</th>
            <th class="col-xs-4 text-left">Adresse du logement</th>
            <th class="col-xs-2 text-left">Commune du logement</th>
            <th class="col-xs-1 text-left">Nombre de lots</th>
            <th class="col-xs-2 text-left">Secteur</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($operators as $key => $operator):?>
            <tr class="vertical-center">
                <td class="text-left"><?php echo $operator['nom']; ?></td>
                <td class="text-left"><?php echo $operator['adresse']; ?></td>
                <td class="text-left"><?php echo $operator['commune']; ?> (<?php echo $operator['code_postal']; ?>)</td>
                <td class="text-center"><?php echo $operator['nbLots']; ?></td>
                <td class="text-center"><?php echo $operator['select']->render(['class' => "degustation bsswitch",'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success"]); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </div>
    </div>

    <div class="row row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("degustation_prelevements_etape",$degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
    <div class="col-xs-4 text-center"></div>
    <div class="col-xs-4 text-right">
        <!-- <a id="btn_suivant"
        class="btn btn-primary btn-upper"
        href="<?php echo ($infosDegustation["nbLotsPrelevesSansLeurre"]) ? url_for('degustation_tables_etape', $degustation) : "#"; ?>"
        <?php if (!$infosDegustation["nbLotsPrelevesSansLeurre"]): echo 'disabled="disabled"'; endif; ?>
        >
        Valider&nbsp;<span class="glyphicon glyphicon-chevron-right"></span>
        </a> -->
        <button type="submit" class="btn btn-primary btn-upper">Valider</button>
    </div>
    </div>
</form>