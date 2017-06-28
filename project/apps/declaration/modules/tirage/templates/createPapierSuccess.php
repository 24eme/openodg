<ol class="breadcrumb">
  <li><a href="<?php echo url_for('declaration'); ?>">Déclarations</a></li>
  <li class="active"><a href="<?php echo url_for('declaration_etablissement', $etablissement); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?>)</a></li>
</ol>

<div class="page-header">
    <h2>Choix de la campagne pour la création de la déclaration de Tirage papier</h2>
</div>

<form action="<?php echo url_for("tirage_create_papier", $etablissement) ?>" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <div class="row">
        <div class="col-sm-6 col-sm-offset-2">
            <div class="form-group">
                <?php echo $form['campagne']->renderLabel(null, array("class" => "col-sm-4 control-label")); ?>
                <div class="col-sm-8">
                    <?php echo $form['campagne']->render(array("class" => "form-control")); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("declaration_etablissement", $etablissement) ?>" class="btn btn-primary btn-lg btn-upper">Annuler</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-default btn-lg btn-upper">Créer</button></div>
    </div>
</form>
