<div class="page-header">
    <h2>Saisie d'une dégustation</h2>
</div>


<form action="<?php echo url_for("degustation_saisie", array("appellation" => $tournee->appellation, "date" => $tournee->date)) ?>" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th class="col-xs-1">Numéro</th>
                <th class="col-xs-5">Produit</th>
                <th class="col-xs-5">Opérateur</th>
                <th class="col-xs-1"></th>
            </tr>
        </tfoot>
        <tbody id="saisie_container">
            <?php foreach($form as $key => $formPrelevement): ?>
                <?php if(!preg_match("/^prelevement_/", $key)): continue; endif;?>
                <?php echo include_partial('degustation/saisieItemForm', array('form' => $formPrelevement)); ?>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><button tabindex="-1" type="button" data-container="#saisie_container" data-template="#template_prevement_item" class="btn btn-xs btn-default-step dynamic-element-add"><span class="glyphicon-plus"></span> Ajouter</button></td>
            </tr>
        </tfoot>
    </table>
    <script id="template_prevement_item" type="text/x-jquery-tmpl">
        <?php echo include_partial('degustation/saisieItemForm', array('form' => $form->getFormTemplate())); ?>
    </script>

    <div class="row">
        <div class="col-xs-6">
        </div>
        <div class="col-xs-6 text-right">
            <button class="btn btn-default btn-lg btn-dynamic-element-submit" type="submit">Valider</button>
        </div>
    </div>
</form>
