<?php use_helper('Compte') ?>
<ol class="breadcrumb">
    <li><a href="<?php echo url_for('societe') ?>">Contacts</a></li>
    <li><a href="<?php echo url_for('societe_visualisation', array('identifiant' => $societe->identifiant)); ?>"><span class="<?php echo comptePictoCssClass($societe->getRawValue()) ?>"></span> <?php echo $societe->raison_sociale; ?></a></li>
    <li><a href="<?php echo url_for('etablissement_visualisation', array('identifiant' => $etablissement->identifiant)); ?>"><span class="<?php echo comptePictoCssClass($etablissement->getRawValue()) ?>"></span> <?php echo $etablissement->nom; ?></a></li>
    <li class="active"></li>
</ol>

<div class="row">
    <form action="<?php echo url_for("etablissement_ajout_relation", array('identifiant' => $etablissement->identifiant)) ?>" method="post" class="form-horizontal">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
        <div class="col-xs-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-9">
                            <h4>Ajouter une relation</h4>
                        </div>
                        <div class="col-xs-3 text-muted text-right">
                            <div class="btn-group">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-body" style="border-right: 6px solid #9f0038;">
                    <h2><span class="<?php echo comptePictoCssClass($etablissement->getRawValue()) ?>"></span>  <?php echo $etablissement->nom; ?></h2>
                    <hr/>
                    <div class="row" style="padding-top:10px;">
                        <div class="form-group">
                            <?php echo $form['type_liaison']->renderError(); ?>
                            <?php echo $form['type_liaison']->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                            <div class="col-xs-7"><?php echo $form['type_liaison']->render(array('class' => 'form-control')); ?></div>
                        </div>
                    </div>
                    <div class="row" style="padding-top:10px;">
                        <div class="form-group">
                            <?php echo $form['id_etablissement']->renderError(); ?>
                            <?php echo $form['id_etablissement']->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                            <div class="col-xs-7"><?php echo $form['id_etablissement']->render(array('class' => 'form-control select2autocompleteAjax input-md', 'placeholder' => 'Séléctionner un établissement')); ?></div>
                        </div>
                    </div>
                    <hr />
                    <div class="row">
                        <div class="col-xs-4">
                            <a href="<?php echo url_for('etablissement_visualisation', array('identifiant' => $etablissement->identifiant)); ?>" class="btn btn-default">Annuler</a>
                        </div>
                        <div class="col-xs-4 text-center">
                        </div>
                        <div class="col-xs-4 text-right">
                            <button id="btn_valider" type="submit" class="btn btn-success">
                                Ajouter la relation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="col-xs-4">
        <?php include_component('societe', 'sidebar', array('societe' => $societe, 'activeObject' => $etablissement)); ?>
    </div>
</div>
