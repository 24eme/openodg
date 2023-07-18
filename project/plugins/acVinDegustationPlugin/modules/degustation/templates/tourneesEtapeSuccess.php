<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>
<?php use_javascript('hamza_style.js'); ?>
<?php use_javascript('degustation.js'); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_TOURNEES)); ?>

<form action="<?php echo url_for("degustation_tournees_etape", array('sf_subject' => $degustation, 'secteur' => $secteur)) ?>" method="post" class="ajaxForm form-horizontal degustation tournees">
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
            <?php foreach (array_keys($lots->getRawValue()) as $region): ?>
                <?php if($region == 'SANS_SECTEUR'): continue; endif; ?>
            <a href="<?php echo url_for('degustation_tournees_etape', array('sf_subject' => $degustation, 'secteur' => $region)); ?>" class="list-group-item <?php if($secteur == $region): ?>active<?php endif; ?>">
                <span class="glyphicon glyphicon-map-marker"></span> <?php echo $region; ?> <span class="badge"><?php echo count($lots[$region]) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        </div>
    </div>
    <div class="col-xs-9">
        <div class="btn-group pull-right">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                Télécharger les PDF <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a id="btn_pdf_fiche_tournee_prelevement" href="<?php echo url_for('degustation_fiche_lots_a_prelever_pdf', array('sf_subject' => $degustation, 'secteur' => $secteur)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche tournée</a></li>
                <li role="separator" class="divider"></li>
                <li><a id="btn_pdf_fiche_individuelle_lots_a_prelever" href="<?php echo url_for('degustation_fiche_individuelle_lots_a_prelever_pdf', array('sf_subject' => $degustation, 'secteur' => $secteur)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche de prélèvement</a></li>
                <li role="separator" class="divider"></li>
                <li>
                    <?php if(DegustationConfiguration::getInstance()->hasAnonymat4labo()) : ?>
                        <a id="btn_pdf_etiquettes_de_prelevement" href="<?php echo url_for('degustation_etiquette_pdf', ['id' => $degustation->_id, 'anonymat4labo' => true, 'secteur' => $secteur]) ?>"><span class="glyphicon glyphicon-th"></span>&nbsp;Étiquettes de prélèvement (avec anonymat labo)</a>
                    <?php else : ?>
                        <a id="btn_pdf_etiquettes_de_prelevement" href="<?php echo url_for('degustation_etiquette_pdf', ['sf_subject' => $degustation, 'secteur' => $secteur]) ?>"><span class="glyphicon glyphicon-th"></span>&nbsp;Étiquettes de prélèvement</a>
                    <?php endif ?>
                </li>
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
            <th class="col-xs-2 text-center">Secteur</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($form as $key => $item):
                if($key == '_revision') { continue; }
                $thelots = isset($lots[$secteur][$key]) ? $lots[$secteur][$key] : $lots['SANS_SECTEUR'][$key];
                $firstlot = array_values($thelots->getRawValue())[0];
            ?>
            <tr class="vertical-center">
                <td class="text-left"><?php echo $firstlot->getLogementNom(); ?></td>
                <td class="text-left"><?php echo $firstlot->getLogementAdresse(); ?></td>
                <td class="text-left"><?php echo $firstlot->getLogementCommune(); ?> (<?php echo $firstlot->getLogementCodePostal(); ?>)</td>
                <td class="text-center"><?php echo count($thelots); ?></td>
                <td class="text-center"><?php echo $item->render(['class' => "degustation bsswitch",'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success"]); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </div>
    </div>

    <div class="row" style="margin-bottom: 20px;">
        <div class="col-xs-3"></div>
        <div class="col-xs-9 text-center">
            <button type="submit" class="btn btn-primary">Enregistrer la tournée du secteur <?php echo $secteur; ?></button>
        </div>
    </div>

    <?php include_partial('degustation/pagination', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_TOURNEES, 'is_enabled' => true)); ?>

</form>
