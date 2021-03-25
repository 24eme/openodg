<?php include_partial('degustation/breadcrumb'); ?>

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
              <strong><?php echo count($lotsPrelevables); ?></strong> <a href="<?= url_for('degustation_prelevables') ?>" class="pull-right"><i class="glyphicon glyphicon-eye-open"></i> Voir les lots</a>
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
                  	<?php echo $form["lieu"]->render(array("class" => "form-control")); ?>
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
<div class="page-header no-border">
    <h2>Les dernières dégustations</h2>
</div>

<?php include_partial('degustation/liste', ['degustations' => $degustations]) ?>

<div>
    <a href="<?= url_for('degustation_liste', ['campagne' => $campagne]) ?>"><i class="glyphicon glyphicon-list"></i> Voir toutes les dégustations</a>
</div>

<div class="page-header no-border">
    <h2>Elevages</h2>
</div>
<p><strong><?php echo count($lotsElevages); ?><?php if(count($lotsElevages)>1):?> lots sont<?php else: ?> lot est<?php endif; ?></strong> actuellement en élevage<?php if(count($lotsElevages)>1):?>s<?php endif; ?> : <a href="<?php echo url_for('degustation_elevages')?>">Voir la liste</a></p>

<div class="page-header no-border">
    <h2>Manquements</h2>
</div>
<p><strong><?php echo count($lotsManquements); ?><?php if(count($lotsManquements)>1):?> lots sont<?php else: ?> lot est<?php endif; ?></strong> actuellement non conforme<?php if(count($lotsManquements)>1):?>s<?php endif; ?> <a href="<?php echo url_for('degustation_manquements')?>">Voir la liste</a></p>
