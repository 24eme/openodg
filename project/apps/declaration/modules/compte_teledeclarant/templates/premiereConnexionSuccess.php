<div class="page-header">
    <h2>Confirmation de votre e-mail</h2>
</div>

<form action="" method="post" class="form-horizontal">
    <p>Pour votre première connexion sur le portail de l'Association des Viticulteurs d'Alsace, vous devez confirmer votre adresse e-mail.</p>
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row">
        <div class="row col-xs-12">
            <div class="col-xs-12">
                <div class="form-group">
                    <?php echo $form["email"]->renderError(); ?>
                    <?php echo $form["email"]->renderLabel(null, array("class" => "col-xs-2 control-label")); ?>
                    <div class="col-xs-10">
                        <?php echo $form["email"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider</button>
        </div>
    </div>
</form>
