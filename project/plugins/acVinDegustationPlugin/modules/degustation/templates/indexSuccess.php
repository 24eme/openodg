<?php include_partial('degustation/breadcrumb'); ?>

<style>
  ul{
    list-style: none;
    display: flex;
  }
</style>

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
                  <span class=""><strong><?php echo count($lotsPrelevables); ?></strong></span>
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
            <div class="form-group">
              <?php echo $form["provenance"]->renderError(); ?>
              <?php echo $form["lieu"]->renderLabel("Provenance", array("class" => "col-xs-4 control-label")); ?>
              <div class="col-sm-5 col-xs-5">
                <?php echo $form["provenance"]->render(); ?>
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
<?php if (count($degustations)): ?>
<div class="row">
<table class="table table-condensed table-striped">
<thead>
    <th class="col-sm-2 text-center">Date de dégustation</th>
    <th class="col-sm-6">Lieu de la dégustation</th>
    <th class="col-sm-2">Infos</th>
    <th class="col-sm-2 text-center"></th>
</thead>
<tbody>
<?php foreach($degustations as $d): ?>
    <tr>
        <td class="col-sm-2 text-center"><?php echo $d->date; ?></td>
        <td class="col-sm-6"><?php echo $d->lieu; ?></td>
        <td class="col-sm-2">
            <?php echo ($d->lots) ? count($d->lots) : '0'; ?> <span class="text-muted">lots</span> -
            <?php echo ($d->degustateurs) ? count($d->degustateurs) : '0'; ?> <span class="text-muted">degust.</span>
        </td>
        <td class="col-sm-2 text-right">
            <?php if ($d->isValidee()): ?>
              <a href="<?php echo url_for('degustation_redirect', $d)?>"class="btn btn-success" >Suivi de la dégustation</a>
          <?php else: ?>
            <a href="<?php echo url_for('degustation_redirect', $d)?>" class="btn btn-success">Reprendre la création de la dégustation</a>
          <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
<tbody>
</table>
<?php endif; ?>
<div class="page-header no-border">
    <h2>Elevages</h2>
</div>
<p><strong><?php echo count($lotsElevages); ?><?php if(count($lotsElevages)>1):?> lots sont<?php else: ?> lot est<?php endif; ?></strong> actuellement en élevage<?php if(count($lotsElevages)>1):?>s<?php endif; ?> : <a href="<?php echo url_for('degustation_elevages')?>">Voir la liste</a></p>

<div class="page-header no-border">
    <h2>Manquements</h2>
</div>
<p><strong><?php echo count($lotsManquements); ?><?php if(count($lotsManquements)>1):?> lots sont<?php else: ?> lot est<?php endif; ?></strong> actuellement non conforme<?php if(count($lotsManquements)>1):?>s<?php endif; ?> <a href="<?php echo url_for('degustation_manquements')?>">Voir la liste</a></p>
</div>
