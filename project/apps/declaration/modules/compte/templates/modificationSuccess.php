<div id="mon_compte">
    <div class="panel" id="modification_compte">
        <div class="panel-heading"><h2 class="titre_section">Mon compte</h2></div>
        <div class="panel-body" >
            <div class="col-xs-6 presentation" <?php if ($form->hasErrors()) echo ' style="display:none;"'; ?> >
                <div class="row">
                    <p class="text-center text-primary">Informations correspondant à votre compte :</p>
                </div>
                <div class="row">
                    <div class="col-xs-3 col-xs-offset-2">
                        <strong>Login/CVI : </strong>
                    </div>
                    <div class="col-xs-3">
                        <strong><?php echo $etablissement->identifiant; ?></strong>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3 col-xs-offset-2">
                        <strong>Email :</strong>
                    </div>
                    <div class="col-xs-3">
                        <strong><?php echo $etablissement->email; ?></strong>
                    </div>
                </div>
                <div class="btn_row">
                    <div class="col-xs-6 text-left">

                    </div>
                    <div class="col-xs-6 text-right">
                        <a href="#" class="modifier btn btn-default btn-next"><img src="/images/boutons/btn_modifier_infos.png" alt="Modifier les informations" /></a>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 modification clearfix" <?php if (!$form->hasErrors()) echo ' style="display:none;"'; ?> >
                <div class="row">
                    <p class="text-center text-primary">Modification des informations de votre compte :</p>
                </div>
                <form method="post" action="<?php echo url_for("@mon_compte") ?>">
                    <div class="row">
                        <div class="col-xs-3 col-xs-offset-2">
                            <strong>Login/CVI : </strong>
                        </div>
                        <div class="col-xs-3">
                            <strong><?php echo $etablissement->identifiant; ?></strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-3 col-xs-offset-2">
                            <strong>Email :</strong>
                        </div>
                        <div class="col-xs-3">
                            <?php echo $form->renderHiddenFields(); ?>
                            <?php echo $form->renderGlobalErrors(); ?>

                            <?php echo $form['email']->renderError() ?>
                            <?php echo $form['email']->render() ?>
                        </div>
                    </div>
                    <div class="btn_row">
                        <div class="col-xs-6 text-left">
                            <a href="#" class="annuler btn btn-default btn-danger">Annuler</a>
                        </div>
                        <div class="col-xs-6 text-right">
                            <input type="submit" class="annuler btn btn-default btn-next" alt="Valider" value="Valider"/>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-xs-6">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        Modification du mot de passe sur le portail du CIVA
                    </div>
                    <div class="panel-body">
                        <p>Afin de modifier votre mot de passe de connexion, veuillez vous rendre sur le portail du CIVA (le bouton ci-dessous permet de vous rendre immédiatement sur le portail du CIVA pour un changement de mot de passe).</p>
                        <a href="<?php echo url_for('redirect_to_mon_compte_civa', array('return_mon_compte' => true)); ?>" class="btn btn-default btn-default-step">Changer de mot de passe</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

