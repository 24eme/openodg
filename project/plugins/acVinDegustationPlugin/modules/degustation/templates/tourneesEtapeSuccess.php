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
    <div class="row" style="margin-top: 20px;">
    <div class="col-xs-3">
        <div class="panel panel-default">
        <div class="panel-heading">
            <h2 class="panel-title">
            Liste des tournées
            </h2>
        </div>
        <div class="list-group">
            <?php foreach (array_keys($lots->getRawValue()) as $region): ?>
            <?php if (!count($lots[$region]) && !$afficher_tous_les_secteurs && $region != $secteur && $region != DegustationClient::DEGUSTATION_SANS_SECTEUR): continue; endif; ?>
            <a href="<?php echo url_for('degustation_tournees_etape', array('sf_subject' => $degustation, 'secteur' => $region, 'afficher_tous_les_secteurs' => $afficher_tous_les_secteurs)); ?>" class="list-group-item <?php if($secteur == $region): ?>active<?php endif; ?>">
                <span class="glyphicon <?php if($region == DegustationClient::DEGUSTATION_SANS_SECTEUR): ?>glyphicon-ban-circle<?php else: ?>glyphicon-map-marker<?php endif; ?>"></span> <?php echo $region; ?> <span class="badge"><?php echo count($lots[$region]) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
            <div class="panel-footer text-center">
                <a href="<?php echo url_for('degustation_tournees_etape', array('sf_subject' => $degustation, 'secteur' => $secteur, 'afficher_tous_les_secteurs' => !$afficher_tous_les_secteurs)); ?>" class="btn-link"><?php if(!$afficher_tous_les_secteurs): ?>Afficher tous les secteurs<?php else: ?>Cacher les secteurs non utilisés<?php endif; ?> </a>
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
                <li><a id="btn_pdf_fiche_individuelle_lots_a_prelever" href="<?php echo url_for('degustation_fiche_individuelle_lots_a_prelever_pdf', array('sf_subject' => $degustation, 'secteur' => $secteur)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche de prélèvements</a></li>
                <li role="separator" class="divider"></li>
                <li>
                    <?php if(DegustationConfiguration::getInstance()->hasAnonymat4labo()) : ?>
                        <a id="btn_pdf_etiquettes_de_prelevement" href="<?php echo url_for('degustation_etiquette_pdf', ['id' => $degustation->_id, 'anonymat4labo' => true, 'secteur' => $secteur]) ?>"><span class="glyphicon glyphicon-th"></span>&nbsp;Étiquettes de prélèvement (avec anonymat labo)</a>
                    <?php else : ?>
                        <a id="btn_pdf_etiquettes_de_prelevement" href="<?php echo url_for('degustation_etiquette_pdf', ['sf_subject' => $degustation, 'secteur' => $secteur]) ?>"><span class="glyphicon glyphicon-th"></span>&nbsp;Étiquettes de prélèvement</a>
                    <?php endif ?>
                </li>
                <li role="separator" class="divider"></li>
                <li><a id="btn_csv_etiquette" href="<?php echo url_for('degustation_etiquette_csv', $degustation) ?>"><span class="glyphicon glyphicon-list"></span>&nbsp;Tableur des étiquettes</a></li>
            </ul>
        </div>
        <h2 style="margin-top: 0; margin-bottom: 20px;">Tournée <?php echo $secteur ?></h2>
        <table class="table table-bordered table-striped table-condensed">
        <thead>
            <tr>
            <th class="col-xs-3 text-left">Opérateur</th>
            <th class="col-xs-4 text-left">Adresse du logement</th>
            <th class="col-xs-1 text-left">Nombre de lots</th>
            <th class="col-xs-2 text-left">Heure</th>
            <th class="col-xs-2 text-center">Secteur</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($form as $logementKey => $subForm):
                $firstlot = $form->getFirstLot($logementKey);
                if ($logementKey == '_revision') continue;
                if (!$firstlot) continue;
            ?>
            <tr class="vertical-center">
                <td class="text-left"><?php echo $firstlot->getLogementNom(); ?></td>
                <td class="text-left"><?php echo $firstlot->getLogementAdresse(); ?><br /><?php echo $firstlot->getLogementCommune(); ?> (<?php echo $firstlot->getLogementCodePostal(); ?>)</td>
                <td class="text-center"><?php echo $form->getNbLots($logementKey); ?></td>
                <td class="text-center"><?php echo $subForm['heure']->render(); ?></td>
                <td class="text-center"><?php echo $subForm['logement']->render(); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </div>
    </div>


    <?php include_partial('degustation/pagination', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_TOURNEES, 'is_enabled' => true)); ?>

</form>
