<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('facturation'); ?>">Facturation</a></li>
</ol>

<?php if (($sf_user->hasFactureAdmin()) && class_exists("EtablissementChoiceForm")): ?>
    <?php include_partial('etablissement/formChoice', array('form' => $formSociete, 'action' => url_for('facturation'), 'noautofocus' => true)); ?>
<?php else: ?>
<div class="row row-margin">
    <div class="col-xs-12">
        <form method="post" action="" role="form" class="form-horizontal">
            <?php echo $form->renderHiddenFields(); ?>
            <?php echo $form->renderGlobalErrors(); ?>
            <div class="form-group">
                <?php echo $form["login"]->renderError(); ?>
                <div class="col-sm-8 col-sm-offset-1 col-xs-12">
                    <?php echo $form["login"]->render(array("class" => "form-control input-lg select2 select2-offscreen select2autocompleteremote select2SubmitOnChange",
                                    "placeholder" => "Se connecter à un opérateur",
                                    "autofocus" => "autofocus",
                                    "data-url" => url_for('compte_recherche_json', array('type_compte' => CompteClient::TYPE_COMPTE_ETABLISSEMENT))
                                    )); ?>
                </div>
                <div class="col-sm-2 hidden-xs">
                    <button class="btn btn-default btn-lg" type="submit">Se connecter</button>
                </div>
            </div>

        </form>
    </div>
</div>
<?php endif; ?>

<h3>Historique des 10 dernières factures <small class="pull-right"><a href="<?php echo url_for("facturation_historique") ?>"><i class="glyphicon glyphicon-list"></i> Voir toutes les factures</a></small></h3>
<?php include_partial('facturation/lastFactures', array('factures' => $factures)); ?>
<small class="pull-right"><a href="<?php echo url_for("facturation_historique") ?>">Voir plus</a></small><br/>
<hr/>
<?php include_partial('facturation/generationForm', array('form' => $formFacturationMassive, 'massive' => true)); ?>


<h3>Historique des générations</h3>
<?php include_partial('generation/list', array('generations' => $generations)); ?>
