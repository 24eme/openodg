<?php include_partial('admin/menu', array('active' => 'tournees')); ?>

<?php include_partial('degustation/stepSaisie', array('tournee' => $tournee, 'active' => TourneeSaisieEtapes::ETAPE_SAISIE)); ?>

<div class="page-header">
    <h2>Saisie des prélévements</h2>
</div>

<form action="<?php echo url_for("degustation_saisie", $tournee) ?>" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th class="col-xs-1">Numéro</th>
                <th class="col-xs-3">Produit</th>
                <th class="col-xs-6">Opérateur</th>
                <th class="col-xs-1">Commiss°</th>
                <th class="col-xs-1"></th>
            </tr>
        </thead>
        <tbody id="saisie_container">
            <?php foreach($form as $key => $formPrelevement): ?>
                <?php if(!preg_match("/^prelevement_/", $key)): continue; endif;?>
                <?php echo include_partial('degustation/saisieItemForm', array('form' => $formPrelevement)); ?>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right"><button tabindex="-1" type="button" data-container="#saisie_container" data-template="#template_prevement_item" class="btn btn-xs btn-default-step dynamic-element-add"><span class="glyphicon-plus"></span> Ajouter</button></td>
            </tr>
        </tfoot>
    </table>
    <script id="template_prevement_item" type="text/x-jquery-tmpl">
        <?php echo include_partial('degustation/saisieItemForm', array('form' => $form->getFormTemplate())); ?>
    </script>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for('degustation') ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
        </div>
        <div class="col-xs-6 text-right">
            <button class="btn btn-default btn-lg btn-dynamic-element-submit" type="submit">Continuer</button>
        </div>
    </div>
</form>
