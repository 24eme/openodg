<?php use_helper('Date') ?>
<?php $params = array("sf_subject" => $drev, "service" => isset($service) ? $service : null); if($regionParam): $params=array_merge($params,array('region' => $regionParam)); endif; ?>

<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('global/flash'); ?>
<?php if (isset($form)): ?>
    <form role="form" class="form-horizontal" action="<?php echo url_for('drev_visualisation', $drev) ?>" method="post" id="validation-form">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
<?php endif; ?>

<div class="page-header no-border">
    <h2>Déclaration de Revendication <?php echo $drev->periode ?>
    <small class="pull-right" style="font-size:55%; margin-top: 2px;">
    <?php if($drev->isPapier()): ?>
    <span class="glyphicon glyphicon-file"></span> Déclaration papier<?php if($drev->getDateDepot()): ?> reçue le <?php echo format_date($drev->getDateDepot(), "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?>
    <?php elseif($drev->validation): ?>
    Télédéclaration<?php if($drev->getDateDepot()): ?> signée le <?php echo format_date($drev->getDateDepot(), "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?><?php if($drev->validation_odg): ?> et approuvée le <?php echo format_date($drev->validation_odg, "dd/MM", "fr_FR"); ?><?php endif; ?>
    <?php endif; ?>
    <?php if ($sf_user->hasDrevAdmin() && $drev->exist('envoi_oi') && $drev->envoi_oi) { echo ", envoyée à l'InnovAgro le ".format_date($drev->envoi_oi, 'dd/MM') ; } ?>
    <?php if ($sf_user->isAdminODG() && $drev->validation_odg && DrevConfiguration::getInstance()->isSentToInnovagro()): ?><a href="<?php echo url_for('drev_send_oi', $drev); echo ($regionParam)? '?region='.$regionParam : ''; ?>" onclick="return confirm('Êtes vous sûr de vouloir envoyer la DRev à Innovagro ?');"  class="btn btn-default btn-xs btn-warning"><span class="glyphicon glyphicon-copy"></span> Envoyer à Innovagro</a>&nbsp;<small><a href="<?php  echo url_for('drev_export_xml', $drev) ?>" class=""></a></small><?php endif; ?>
    </small>
    </h2>
    <?php if ($drev->isFactures()): ?><div style="margin-top: -20px;" class="pull-right"><span class="text-muted">DRev facturée</span></div><?php endif; ?>
</div>

<?php if ($drev->isValidee()): ?>
<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', array('etablissement' => $drev->getEtablissementObject())); ?>
</div>
<?php endif ?>

<?php if(!$drev->validation): ?>
<div class="alert alert-warning">
    La saisie de cette déclaration n'est pas terminée elle est en cours d'édition
</div>
<?php endif; ?>

<?php if(!$drev->isMaster()): ?>
    <div class="alert alert-info">
      Ce n'est pas la <a class="" href="<?php echo ($drev->getMaster()->isValidee())? url_for('drev_visualisation', $drev->getMaster()) :  url_for('drev_edit', $drev->getMaster()) ?>"><strong>dernière version</strong></a> de la déclaration, le tableau récapitulatif n'est donc pas à jour.

    </div>
<?php endif; ?>

<?php if($drev->validation && !$drev->validation_odg): ?>
    <div class="alert alert-warning">
        Cette déclaration est en <strong>attente de validation</strong> par votre organisme.
    </div>
<?php endif; ?>

<?php if ($sf_user->isAdminODG() && $drev->isMiseEnAttenteOdg()): ?>
    <div class="alert alert-info">
        Cette déclaration a été <strong>mise en attente</strong> par l'ODG (<a href="<?php echo url_for("drev_enattente_admin", $params); ?>">annuler la mise en attente</a>)
    </div>
<?php endif; ?>

<?php if(isset($validation) && $validation->hasPoints()): ?>
    <?php include_partial('drev/pointsAttentions', array('drev' => $drev, 'validation' => $validation, 'noLink' => true)); ?>
<?php endif; ?>

<?php include_partial('drev/recap', array('drev' => $drev, 'form' => $form, 'dr' => $dr)); ?>

<?php if($drev->exist('documents') && count($drev->documents->toArray(true, false)) ): ?>
    <hr />
    <h3>&nbsp;Engagement(s)&nbsp;</h3>
    <?php foreach($drev->documents as $docKey => $doc): ?>
        <p>&nbsp;<span style="font-family: Dejavusans">☑</span>
            <?php
            if($doc->exist('libelle') && $doc->libelle):
                $libelle = preg_replace("#&gt;#",">",$doc->libelle);
                $libelle = preg_replace("#&lt;#","<",$libelle);
                echo($libelle);
            else:
                echo($drev->documents->getEngagementLibelle($docKey));
            endif;
            ?>
        </p>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($form)): ?>
</form>
<?php endif; ?>

<?php if(DRevSecurity::getInstance($sf_user, $drev->getRawValue())->isAuthorized(DRevSecurity::VALIDATION_ADMIN) && $drev->exist('commentaire')): ?>
  <?php $hasmodal = false; ?>
  <hr/>
  <h4>
    Commentaire interne
    <small>(seulement visible par l'ODG<?php if ($drev->getValidationOdg()): ?> - <a href="#" data-toggle="modal" data-target="#drev-edit-comment"><?php echo ($drev->commentaire) ? 'Éditer' : 'Ajouter' ?></a><?php endif ?>)</small>
  </h4>
  <?php if ($drev->getValidationOdg() && $drev->commentaire): ?>
      <pre><?php echo $drev->commentaire; ?></pre>
  <?php endif ?>

  <?php if ($drev->getValidationOdg()): ?>
  <div class="modal fade" id="drev-edit-comment" role="dialog" aria-labelledby="Edition du commentaire" aria-hidden="true">
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title" id="myModalLabel">Edition du commentaire</h4>
              </div>
              <div class="modal-body">
    <?php $hasmodal = true; ?>
  <?php endif; ?>
  <?php if($drevCommentaireValidationForm): ?>
                <form id="formUpdateCommentaire" action="<?php echo url_for('drev_update_commentaire', $drev) ?>" method="post">
                    <?php echo $drevCommentaireValidationForm->renderHiddenFields(); ?>
                    <?php echo $drevCommentaireValidationForm->renderGlobalErrors(); ?>
                    <?php echo $drevCommentaireValidationForm['commentaire']->render(['class' => 'form-control']) ?>
                    <div class="form-group text-right" style="margin-top: 10px">
                        <button type="submit" form="formUpdateCommentaire" class="btn btn-default">
                            <i class="glyphicon glyphicon-floppy-disk"></i> Enregistrer le commentaire
                        </button>
                    </div>
                </form>
  <?php endif; ?>
  <?php if($hasmodal): ?>
              </div>
          </div>
      </div>
  </div>
  <?php endif; ?>
<?php endif; ?>

<hr />

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php if(isset($service)): ?><?php echo $service ?><?php else: ?><?php echo url_for("declaration_etablissement", array('identifiant' => $drev->identifiant, 'campagne' => $drev->campagne)); ?><?php endif; ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
    <div class="col-xs-4 text-center">
        <div class="btn-group dropup">
            <?php if ($sf_user->hasDrevAdmin() && $drev->getDocumentDouanier()): ?>
            <a href="<?php echo url_for('drev_document_douanier', $drev); ?>" class="btn btn-default" >
              <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;<?php echo $drev->getDocumentDouanierType() ?>
            </a>
        <?php endif; ?>
            <a href="<?php echo url_for("drev_export_pdf", $drev) ?>" class="btn btn-default" id="lien-telechargement-pdf-drev">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;PDF de la DRev
            </a>
            <?php if ($drev->exist('lots') && count($drev->getNumerosDossier()) > 1): ?>
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="caret"></span>
                  <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                  <?php foreach ($drev->getNumerosDossier() as $dossier): ?>
                      <li><a href="<?php echo url_for('drev_export_pdf', ['id' => $drev->_id, 'numero_dossier' => $dossier]); ?>">
                        <i class="glyphicon glyphicon-file"></i> PDF du dossier <?php echo $dossier ?>
                      </a></li>
                  <?php endforeach ?>
                </ul>
            <?php endif ?>
        </div>
    </div>

    <div class="col-xs-4 text-right">
        <div class="btn-group">
        <?php if ($drev->validation && DRevSecurity::getInstance($sf_user, $drev->getRawValue())->isAuthorized(DRevSecurity::DEVALIDATION) && !$drev->hasLotsUtilises()):
                if (!$drev->validation_odg && !$drev->isFactures()): ?>
                    <a class="btn btn-default btn-sm" href="<?php echo url_for('drev_devalidation', $drev) ?>" onclick="return confirm('Êtes-vous sûr de vouloir réouvrir cette DRev ?');"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Réouvrir</a>
            <?php elseif (!$drev->isFactures() && !$drev->isLectureSeule() && $sf_user->isAdminODG() &&  !$drev->hasLotsUtilises() && $drev->isMaster()): ?>
                    <a class="btn btn-default btn-sm" href="<?php echo url_for('drev_devalidation', $drev) ?>" onclick="return confirm('Êtes-vous sûr de vouloir dévalider cette DRev ?');"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider</a>
            <?php endif; ?>
        <?php endif; ?>
        <?php if(!$drev->validation): ?>
                <a href="<?php echo url_for("drev_delete", $drev) ?>" class="btn btn-default alert-danger" onclick="return confirm('Souhaitez-vous vraiment SUPPRIMER la saisie de ce document ?')"><span class="glyphicon glyphicon-remove"></span> Supprimer</a>
                <a href="<?php echo url_for("drev_edit", $drev) ?>" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
        <?php elseif(!$drev->validation_odg && (
            $sf_user->isAdminODG() && !DrevConfiguration::getInstance()->hasValidationOdgRegion() || ($sf_user->hasDrevAdmin() && DrevConfiguration::getInstance()->hasValidationOdgRegion() && !$drev->isValidateOdgByRegion($regionParam))
        )): ?>
            <?php if (!$drev->isMiseEnAttenteOdg()): ?>
                    <a href="<?php echo url_for("drev_enattente_admin", $params); ?>" class="btn btn-default"><span class="glyphicon glyphicon-hourglass"></span>&nbsp;Mettre en attente</a>
            <?php endif; ?>
            <button type="button" name="validateOdg" id="btn-validation-document" data-target="#drev-confirmation-validation" <?php if($validation->hasErreurs() && $drev->isTeledeclare() && (!$sf_user->hasDrevAdmin() || $validation->hasFatales())): ?>disabled="disabled"<?php endif; ?> class="btn btn-success btn-upper"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;&nbsp;Approuver</button>
        <?php endif; ?>
        </div>
    </div>
</div>

<?php include_partial('drev/popupConfirmationValidation', array('approuver' => false)); ?>
<?php if (!$sf_user->isAdminODG() && MandatSepaConfiguration::getInstance()->isActive() && !$drev->getEtablissementObject()->getSociete()->hasMandatSepa()): ?>
<?php include_partial('mandatsepa/popupPropositionInscriptionPrelevement'); ?>
<?php endif; ?>
