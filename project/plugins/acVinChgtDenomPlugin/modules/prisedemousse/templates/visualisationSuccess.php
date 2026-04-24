<?php use_helper('Float') ?>
<?php use_helper('Date') ?>
<?php use_helper('Lot') ?>

<?php include_partial('prisedemousse/breadcrumb', array('prisedemousse' => $prisedemousse )); ?>

    <div class="page-header no-border">
      <h2>Prise de mousse <?php if (!$prisedemousse->isTotal()): ?>partielle <?php endif; ?>n° <?php echo $prisedemousse->numero_archive; ?>
      <?php if ($prisedemousse->isValide()): ?>
      <small class="pull-right">Télédéclaration signée le <?php echo format_date($prisedemousse->validation, "dd/MM/yyyy", "fr_FR"); ?><?php if($prisedemousse->isApprouve()): ?> et approuvée le <?php echo format_date($prisedemousse->validation_odg, "dd/MM", "fr_FR"); ?><?php endif; ?></small>
      <?php endif; ?>
      </h2>
    </div>
    <?php include_partial('global/flash'); ?>

    <?php if ($prisedemousse->isValide()): ?>
    <div class="well mb-5">
        <?php include_partial('etablissement/blocDeclaration', array('etablissement' => $prisedemousse->getEtablissementObject())); ?>
    </div>
    <?php endif ?>

    <?php if($sf_user->isAdmin()): ?>
        <?php include_partial('prisedemousse/recap', array('prisedemousse' => $prisedemousse, 'form' => $form)); ?>
    <?php else:?>
        <?php include_partial('prisedemousse/recap', array('prisedemousse' => $prisedemousse)); ?>
    <?php endif; ?>

<?php if($prisedemousse->exist('documents') && count($prisedemousse->documents->toArray(true, false)) ): ?>
    <hr />
    <h3>&nbsp;Engagement(s)&nbsp;</h3>
    <?php foreach($prisedemousse->documents as $docKey => $doc): ?>
        <p>&nbsp;<span style="font-family: Dejavusans">☑</span>
            <?php
            if($doc->exist('libelle') && $doc->libelle):
                $libelle = preg_replace("#&gt;#",">",$doc->libelle);
                $libelle = preg_replace("#&lt;#","<",$libelle);
                echo($libelle);
            else:
                echo($prisedemousse->documents->getEngagementLibelle($docKey));
            endif;
            ?>
        </p>
    <?php endforeach; ?>
<?php endif; ?>

    <?php if (isset($form)): ?>
    <form role="form" action="<?php echo url_for("prisedemousse_visualisation", $prisedemousse) ?>" method="post" class="form-horizontal" id="validation-form">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
          <?php if (isset($form['deguster'])): ?>
          <div class="row">
              <div class="col-md-12 text-right">
                <label>
                  <?php echo $form['deguster']->render() ?>
                  <?php echo $form['deguster']->renderLabel('À déguster') ?>
                </label>
              </div>
          </div>
        <?php endif; ?>
          <div style="margin-top: 20px;" class="row row-margin row-button">
              <div class="col-xs-4">
                  <a tabindex="-1" href="<?php echo url_for("declaration_etablissement", array('identifiant' => $prisedemousse->identifiant, 'campagne' => $prisedemousse->campagne)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
              </div>
              <div class="col-xs-4 text-center">
                  <a href="<?php echo url_for('prisedemousse_pdf', ['id' => $prisedemousse->_id]) ?>" class="btn btn-default"><i class="glyphicon glyphicon-file"></i> Voir le PDF en attente</a>
              </div>
              <div class="col-xs-4 text-right">
                  <a href="<?php echo url_for("prisedemousse_devalidation", $prisedemousse); ?>" class="btn btn-default"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;Réouvrir</a>
                  <button type="submit" id="btn-validation-document" data-toggle="modal" data-target="#prisedemousse-confirmation-approbation" class="btn btn-success btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Approuver le changement</button>
              </div>
          </div>
      </form>
      <?php include_partial('prisedemousse/popupConfirmationApprobation'); ?>
    <?php else: ?>
    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-4">
            <a tabindex="-1" href="<?php echo url_for("declaration_etablissement", array('identifiant' => $prisedemousse->identifiant, 'campagne' => $prisedemousse->campagne)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
        </div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for('prisedemousse_pdf', ['id' => $prisedemousse->_id]) ?>" class="btn btn-default"><i class="glyphicon glyphicon-file"></i> Voir le PDF</a>
        </div>
        <div class="col-xs-4 text-right">
          <?php if ($prisedemousse->validation_odg && $sf_user->isAdmin() && !$prisedemousse->hasLotsUtilises()):?>
              <a class="btn btn-default" href="<?php echo url_for('prisedemousse_devalidation', $prisedemousse) ?>" onclick="return confirm('Êtes-vous sûr de vouloir dévalider cette prise de mousse ?');"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider</a>
          <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
