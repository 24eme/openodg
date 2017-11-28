<?php include_partial('travauxmarc/breadcrumb', array('travauxmarc' => $travauxmarc )); ?>
<?php include_partial('travauxmarc/step', array('step' => 'fournisseurs', 'travauxmarc' => $travauxmarc)) ?>

<div class="page-header">
    <h2>Fournisseurs de Marcs</h2>
</div>

<form role="form" action="<?php echo url_for("travauxmarc_fournisseurs", $travauxmarc) ?>" method="post" class="ajaxForm" id="form_travauxmarc_fournisseurs">
    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>

    <table class="table table-bordered table-condensed table-striped">
        <thead>
            <tr>
                <th class="col-xs-5">Nom du fournisseur</th>
                <th class="col-xs-3">Date de livraison</th>
                <th class="col-xs-3">Quantité de marc (kg)</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="travaux_marc_fournisseurs_container">
            <?php foreach($form as $item): ?>
                <?php if($item->isHidden()): continue; endif; ?>
                <?php include_partial('travauxmarc/fournisseurItemForm', array('form' => $item)); ?>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><button tabindex="-1" type="button" data-container="#travaux_marc_fournisseurs_container" data-template="#template_travaux_marc_fournisseurs_item" class="btn btn-xs btn-default-step dynamic-element-add"><span class="glyphicon-plus"></span> Ajouter</button></td>
            </tr>
        </tfoot>
    </table>
    <script id="template_travaux_marc_fournisseurs_item" type="text/x-jquery-tmpl">
        <?php echo include_partial('travauxmarc/fournisseurItemForm', array('form' => $form->getFormTemplate())); ?>
    </script>

    <div class="row row-margin row-button">
        <div class="col-xs-6"><a href="<?php echo url_for("travauxmarc_exploitation", $travauxmarc) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a></div>
        <div class="col-xs-6 text-right"><button type="submit" class="btn btn-default btn-lg btn-upper btn-dynamic-element-submit">Valider et continuer&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button></div>
    </div>
</form>
