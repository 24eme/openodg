<?php use_helper('Float'); ?>
<?php use_helper('Date'); ?>
<?php use_javascript('facture.js?20231009'); ?>

<ol class="breadcrumb">
    <li class="visited"><a href="<?php echo url_for('facturation'); ?>">Facturation</a></li>
    <li class="visited"><a href="<?php echo url_for('facturation_libre'); ?>">Facturation libre</a></li>
    <li class="active"><a href="<?php echo url_for('facturation_libre_edition', array('id' => $form->getObject()->_id)) ?>" class="active">Edition n°&nbsp;<?php echo $form->getObject()->identifiant; ?></a></li>
</ol>

<h2>Facturation libre n°&nbsp;<?php echo $form->getObject()->identifiant; ?><?php if ($form->getObject()->getDate()): ?> du <?php echo format_date($form->getObject()->getDate(), "dd/MM/yyyy", "fr_FR") ?><?php endif; ?></h2>

<form id="form_mouvement_edition_facture" action="" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <div class="row row-margin">
        <div class="col-xs-12">
            <div class="row">
              <div class="col-xs-8 col-xs-offset-4"><?php echo $form['libelle']->renderError(); ?></div>
              <div class="col-xs-4 h4 text-right"><?php echo $form['libelle']->renderLabel(); ?></div>
              <div class="col-xs-8"><?php echo $form['libelle']->render(array('class' => 'form-control')); ?>  </div>
            </div>
        </div>
    </div>
    <hr />

    <div class="row row-margin">
        <div class="col-xs-12" style="border-bottom: 1px dotted #d2d2d2;" id="mouvementsfacture_list">
            <div class="row">
                <div class="col-xs-3 text-center">Société</div>
                <div class="col-xs-1 text-center">Compta</div>
                <div class="col-xs-3 text-center">Opération</div>
                <div class="col-xs-3 text-center">Libellé article</div>
                <div class="col-xs-1 text-center">Prix&nbsp;U.</div>
                <div class="col-xs-1 text-center" style="padding-left: 0;">Quantité</div>
            </div>
            <?php
            foreach ($form['mouvements'] as $k => $mvtForm) {
              $kExploded = explode('_', $k);
              $object = ($kExploded[1] != 'nouveau')? $form->getObject()->mouvements->get($kExploded[1])->get($kExploded[2]) : null;
              include_partial('itemMouvementFacture', array('mvtForm' => $mvtForm, 'object' => $object));
            }
            ?>
        </div>
    </div>
    <br/>
    <div class="row row-margin">
        <div class="col-xs-6 text-left">
            <a class="btn btn-default btn-upper" tabindex="-1" href="<?php echo url_for('facturation_libre') ?>"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
        </div>
        <div class="col-xs-6 text-right">
            <input type="submit" class="btn btn-success btn-upper" value="Valider / Ajouter une ligne" />
        </div>
    </div>

</form>
