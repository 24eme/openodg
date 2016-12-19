<?php use_helper('Date') ?>

<?php if (isset($form)): ?>
<form action="<?php echo url_for('tirage_visualisation', $tirage) ?>" method="post">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
<?php endif; ?>

<div class="page-header no-border">
    <h2>Déclaration de Tirage <?php echo $tirage->campagne; ?>
        <br />
        <?php if ($tirage->isPapier()): ?>
            <small><span class="glyphicon glyphicon-file"></span> Déclaration papier<?php if ($tirage->validation && $tirage->validation !== true): ?> reçue le <?php echo format_date($tirage->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?></small>
        <?php elseif ($tirage->validation): ?>
            <small>Télédéclaration<?php if ($tirage->validation && $tirage->validation !== true): ?> validée le <?php echo format_date($tirage->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?></small>
        <?php endif; ?>
    </h2>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<?php if (!$tirage->validation): ?>
    <div class="alert alert-warning">
        La saisie de cette déclaration n'est pas terminée elle est en cours d'édition
    </div>
<?php endif; ?>

<?php if ($tirage->validation && !$tirage->validation_odg): ?>
    <div class="alert alert-warning">
        Cette déclaration est en <strong>attente de validation</strong> par l'AVA
    </div>
<?php endif; ?>

<?php if (isset($validation) && $validation->hasPoints()): ?>
    <?php include_partial('tirage/pointsAttentions', array('tirage' => $tirage, 'validation' => $validation)); ?>
<?php endif; ?>

<?php include_partial('tirage/recap', array('tirage' => $tirage)); ?>

<div class="row">
    <div class="col-xs-12">
        <?php if (count($tirage->getOrAdd('documents')->toArray()) > 0 || $tirage->hasDr()): ?>
            <h3>Documents à joindre</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="text-left col-md-9">Documents</th>
                            <th class="text-center col-md-3">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($form)): ?>
                            <?php foreach ($form->getEmbeddedForms() as $key => $documentForm): ?>
                                <tr>
                                    <td class="text-left"><?php echo TirageDocuments::getDocumentLibelle($key) ?></td>
                                    <td class="text-left">
                                        <div class="checkbox">
                                            <label>
                                                <?php echo $form[$key]['statut']->render(); ?>
                                                <?php echo $form[$key]['statut']->renderLabel(); ?>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach ($tirage->getOrAdd('documents') as $document): ?>
                                <tr>
                                    <td class="text-left"><?php echo TirageDocuments::getDocumentLibelle($document->getKey()) ?></td>
                                    <?php $href = '' ; if ($tirage->hasDr()) { $href = url_for("tirage_dr_pdf", $tirage); } ?>
                                    <td class="text-center">
                                         <<?php if ($href) {echo 'a href="'.$href.'"';} else { echo 'span';}
                                        ?>  class="<?php if ($document->statut == TirageDocuments::STATUT_RECU): ?>text-success<?php else: ?>text-warning<?php endif;
                                        ?>"><?php echo ($href) ? "Télécharger" : TirageDocuments::getStatutLibelle($document->statut)
                                        ?></<?php echo ($href) ? 'a' : 'span'; ?>></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
        <?php endif; ?>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php echo url_for("home") ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
    <div class="col-xs-4 text-center">
        <?php if ($tirage->validation && $sf_user->isAdmin()): ?>
        <a href="<?php echo url_for("tirage_devalidation", $tirage) ?>" class="btn btn-danger btn-lg">Dévalider</a>&nbsp;&nbsp;&nbsp;
        <?php endif; ?>
        <?php if (!$tirage->validation): ?>
            <a href="<?php echo url_for("tirage_delete", $tirage) ?>" class="btn btn-danger btn-lg">Supprimer</a>&nbsp;&nbsp;&nbsp;
        <?php endif ; ?>
            <a href="<?php echo url_for("tirage_export_pdf", $tirage) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
            </a>
    </div>
    <?php if (!$tirage->validation): ?>
        <div class="col-xs-4 text-right">
            <a href="<?php echo url_for("tirage_edit", $tirage) ?>" class="btn btn-warning btn-lg"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
        </div>
    <?php elseif (!$tirage->validation_odg && $sf_user->isAdmin()): ?>
        <div class="col-xs-4 text-right">
            <?php if($tirage->hasCompleteDocuments()): ?>
                <a href="<?php echo url_for("tirage_validation_admin", array("sf_subject" => $tirage, "service" => isset($service) ? $service : null)) ?>" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;&nbsp;Approuver</a>
            <?php else: ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Enregistrer</button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php if (isset($form)): ?>
    </form>
<?php endif; ?>
