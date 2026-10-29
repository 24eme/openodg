<?php include_partial('degustation/breadcrumb'); ?>

<?php if(isset($formEtablissement)): ?>
    <?php include_partial('etablissement/formChoice', array('form' => $formEtablissement, 'action' => url_for('degustation_etablissement_selection'))); ?>
<?php endif; ?>

<?php if(DegustationConfiguration::getInstance()->isTourneeAutonome()): ?>
<div style="margin-top: 0px;" class="page-header no-border">
    <h2>Création d'une tournée</h2>
</div>
<form action="<?php echo url_for('degustation_create_tournee'); ?>" method="post" class="form-horizontal">
    <?php echo $formCreationTournee->renderHiddenFields(); ?>

    <div class="bg-danger">
    <?php echo $formCreationTournee->renderGlobalErrors(); ?>
    </div>

    <div class="row">
        <div class="col-sm-10 col-xs-12">
            <div class="form-group">
              <div class="col-sm-9 col-xs-9 text-right">
                  Nombre de lots en attente de prélevemnt :
              </div>
              <div class="col-sm-3 col-xs-3">
              <strong><?php echo count(TourneeClient::getInstance()->getLotsEnAttente(Organisme::getInstance()->getCurrentRegion())); ?></strong> <a href="<?= url_for('degustation_attente', ['active' => 'tournee']) ?>" class="pull-right"><i class="glyphicon glyphicon-eye-open"></i> Voir les lots</a>
              </div>
            </div>
            <div class="form-group <?php if($formCreationTournee["date"]->getError()): ?>has-error<?php endif; ?>">
                <?php echo $formCreationTournee["date"]->renderError(); ?>
                <?php echo $formCreationTournee["date"]->renderLabel("Date de la tournée", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-sm-5 col-xs-5">
                    <div class="input-group date-picker">
                        <?php echo $formCreationTournee["date"]->render(array("class" => "form-control")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3 col-xs-3">
                  <div class="input-group">

                  </div>
                </div>
            </div>
            <div class="form-group text-right">
                <div class="col-sm-4 col-sm-offset-8 col-xs-12">
                    <button type="submit" class="btn btn-primary">Créer une tournée</button>
                </div>
            </div>
        </div>
    </div>
</form>
<div style="margin-top: 0px;" class="page-header no-border">
    <h2 style="margin-top: 0px;">Les dernières tournées</h2>
</div>
<?php include_partial('degustation/listeTournees', ['tournees' => $tournees]) ?>
<?php endif; ?>

<div class="page-header no-border">
    <h2>Création d'une dégustation</h2>
</div>
<form action="<?php echo url_for('degustation') ?>" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>

    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>

    <div class="row">
        <div class="col-sm-10 col-xs-12">
            <div class="form-group">
              <div class="col-sm-9 col-xs-9 text-right">
                  Nombre de lots ne faisant l'objet d'aucune dégustation :
              </div>
              <div class="col-sm-3 col-xs-3">
              <strong><?php echo count($lotsEnAttenteDegustation); ?></strong> <a href="<?= url_for('degustation_attente') ?>" class="pull-right"><i class="glyphicon glyphicon-eye-open"></i> Voir les lots</a>
              </div>
            </div>
            <div class="form-group <?php if($form["date"]->getError()): ?>has-error<?php endif; ?> <?php if($form["time"]->getError()): ?>has-error<?php endif; ?>">
                <?php echo $form["date"]->renderError(); ?>
                <?php echo $form["time"]->renderError(); ?>
                <?php echo $form["date"]->renderLabel("Date de dégustation", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-sm-5 col-xs-5">
                    <div class="input-group date-picker">
                        <?php echo $form["date"]->render(array("class" => "form-control")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3 col-xs-3">
                  <div class="input-group">
                      <?php echo $form["time"]->render(array("class" => "form-control")); ?>
                      <div class="input-group-addon">
                          <span class="glyphicon-time glyphicon"></span>
                      </div>
                  </div>
                </div>
            </div>
            <div class="form-group <?php if($form["lieu"]->getError()): ?>has-error<?php endif; ?> <?php if($form["max_lots"]->getError()): ?>has-error<?php endif; ?>">
                <?php echo $form["lieu"]->renderError(); ?>
                <?php echo $form["max_lots"]->renderError(); ?>
                <?php echo $form["lieu"]->renderLabel("Lieu de dégustation", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-sm-5 col-xs-5">
                    <?php echo $form["lieu"]->render(array("class" => "form-control", 'required' => true)); ?>
                </div>
                <div class="col-sm-3 col-xs-3">
                  	<?php echo $form["max_lots"]->render(array("class" => "form-control", "placeholder" => 'Nombre max de lots')); ?>
                </div>
            </div>
            <div class="form-group text-right">
                <div class="col-sm-4 col-sm-offset-8 col-xs-12">
                    <button type="submit" class="btn btn-primary">Créer une dégustation</button>
                </div>
            </div>
        </div>
    </div>
</form>

<div style="margin-top: 0px;" class="page-header no-border">
    <a style="margin-top: 20px;" class="pull-right" href="<?= url_for('degustation_liste', ['campagne' => $lastAnnee]) ?>"><i class="glyphicon glyphicon-list"></i> Voir toutes les dégustations</a>

    <h2 style="margin-top: 0px;">Les dernières dégustations</h2>
</div>

<?php include_partial('degustation/liste', ['degustations' => $degustations]) ?>

<div>

</div>

<div class="page-header no-border">
    <h2>Elevages</h2>
</div>
<p><strong><?php echo count($lotsElevages); ?><?php if(count($lotsElevages)>1):?> lots sont<?php else: ?> lot est<?php endif; ?></strong> actuellement en élevage<?php if(count($lotsElevages)>1):?>s<?php endif; ?> : <a href="<?php echo url_for('degustation_elevages')?>">Voir la liste</a></p>

<div class="page-header no-border">
    <h2>Non conformités</h2>
</div>
<p><strong><?php echo count($lotsManquements); ?><?php if(count($lotsManquements)>1):?> lots sont<?php else: ?> lot est<?php endif; ?></strong> actuellement non conforme<?php if(count($lotsManquements)>1):?>s<?php endif; ?> <a href="<?php echo url_for('degustation_nonconformites')?>">Voir la liste</a></p>
