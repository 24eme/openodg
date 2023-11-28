<div id="principal" class="clearfix" style="margin-top: 20px;">
    <form action="" method="post" class="form-horizontal" name ="firstConnection">
        <div class="row">
            <div class="col-xs-8 col-xs-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2 style="margin-top: 10px;">Première connexion</h2>
                    </div>
                    <div class="panel-body">
                        <p>Afin de créer de votre compte pour la <strong>plateforme ODG</strong>, veuillez remplir les champs suivants :</p>
                        <div id="nouvelle_declaration" style="margin-top: 30px;">
                            <?php echo $form->renderHiddenFields(); ?>
                            <?php echo $form->renderGlobalErrors(); ?>
                            <div class="form-group">
                                <?php echo $form['login']->renderError() ?>
                                <?php echo $form['login']->renderLabel(null, ['class' => 'control-label col-sm-5']) ?>
                                <div class="col-sm-4">
                                    <?php echo $form['login']->render() ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo $form['mdp']->renderError() ?>
                                <?php echo $form['mdp']->renderLabel(null, ['class' => 'control-label col-sm-5']) ?>
                                <div class="col-sm-4">
                                    <?php echo $form['mdp']->render() ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <button class="btn btn-success" type="submit">Valider</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
