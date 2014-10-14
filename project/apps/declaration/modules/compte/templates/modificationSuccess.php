<div id="" class="clearfix">

    <div id="mon_compte">



        <div class="panel panel-default" id="modification_compte">
            <div class="panel-heading"><h2 class="titre_section">Mon compte</h2></div>
            <div class="panel-body" >
                <div class="presentation <?php if ($form->hasErrors()) echo ' style="display:none;"'; ?>" >
                    <div class="row" style="padding-top: 50px; ">
                        <p class="text-center text-primary">Informations correspondant à votre compte :</p>
                    </div>
                    <div class="row" style="padding-top: 20px; ">
                        <div class="col-xs-3 col-xs-offset-2">
                            <strong>Login/CVI : </strong>
                        </div>
                        <div class="col-xs-3">
                            <strong><?php echo $etablissement->cvi; ?></strong>
                        </div>
                    </div>
                    <div class="row" style="padding-top: 20px; ">
                        <div class="col-xs-3 col-xs-offset-2">
                            <strong>Email :</strong>
                        </div>
                        <div class="col-xs-3">
                            <strong><?php echo $etablissement->email; ?></strong>
                        </div>
                    </div>
                    <div class="row" style="padding-top: 50px; ">
                        <div class="col-xs-6 text-left">
                            <a href="<?php echo url_for('redirect_to_mon_compte_civa'); ?>" class="btn btn-default btn-warning">Modifier le mot de passe sur le portail du CIVA</a>
                        </div>
                        <div class="col-xs-6 text-right">
                            <a href="#" class="modifier btn btn-default btn-next"><img src="/images/boutons/btn_modifier_infos.png" alt="Modifier les informations" /></a>
                        </div>
                    </div>
                </div>


                <div class="modification clearfix"<?php if (!$form->hasErrors()) echo ' style="display:none;"'; ?>>
                    <div class="row" style="padding-top: 50px; ">
                        <p class="text-center text-primary">Modification des informations de votre compte :</p>
                    </div>
                    <form method="post" action="<?php echo url_for("@mon_compte") ?>">
                        <div class="row" style="padding-top: 20px; ">
                            <div class="col-xs-3 col-xs-offset-2">
                                <strong>Login/CVI : </strong>
                            </div>
                            <div class="col-xs-3">
                                <strong><?php echo $etablissement->cvi; ?></strong>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 20px; ">
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
                        <div class="row" style="padding-top: 50px; ">
                            <div class="col-xs-6 text-left">
                                <a href="<?php echo url_for('redirect_to_mon_compte_civa'); ?>" class="btn btn-default btn-warning">Modifier le mot de passe sur le portail du CIVA</a>
                            </div>
                            <div class="col-xs-6 text-right">
                                <a href="#" class="annuler btn btn-default btn-danger">Annuler</a>
                                <input type="submit" class="annuler btn btn-default btn-next" alt="Valider" value="Valider"/>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>
