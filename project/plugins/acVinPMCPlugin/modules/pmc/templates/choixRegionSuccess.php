<?php include_partial('pmc/breadcrumb', array('pmc' => $pmc )); ?>
<?php include_partial('pmc/step', array('step' => PMCEtapes::ETAPE_REGION, 'pmc' => $pmc)) ?>

<div class="page-header">
    <h2>Choix de la région viticole</h2>
</div>

<?php echo include_partial('global/flash') ?>

<form action="<?php echo url_for('pmc_region', $pmc) ?>" method="post">
    <?php echo $form->renderHiddenFields() ?>
    <div class="radio">
        <?php echo $form['region']->render() ?>
    </div>
    <div class="row row-margin row-button">
        <div class="col-xs-4">
            <a tabindex="-1" href="<?php echo url_for(PMCEtapes::getInstance()->getPreviousLink(PMCEtapes::ETAPE_REGION), $pmc)  ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-4 col-xs-offset-4 text-right">
            <button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
    </div>

</form>
