<?php use_helper("Date"); ?>
<?php $college_libelle = ""; ?>
<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_DEGUSTATEURS)); ?>


<div class="page-header no-border">
  <h2>Sélection des dégustateurs</h2>
</div>

<ul class="nav nav-pills degustation collegeCounter">
  <?php foreach ($colleges as $college_local_key => $college_local): ?>
    <?php if($college_local_key == $college): $college_libelle = $college_local;  endif; ?>
    <li role="presentation" class="ajax <?php if($college_local_key == $college): echo "active"; endif; ?>"><a href="<?php echo url_for("degustation_selection_degustateurs", array('id' => $degustation->_id, 'college' => $college_local_key)) ?>"><?php echo $college_local; ?>
      &nbsp;<span class="badge <?php echo $college_local_key; ?>"><?php echo count($degustation->getOrAdd('degustateurs')->getOrAdd($college_local_key)); ?></span></a></li>
  <?php endforeach; ?>
</ul>
<div class="row row-condensed">
  <div class="col-xs-12">
    <div class="panel panel-default">
      <div class="panel-body">

          <form action="<?php echo url_for("degustation_selection_degustateurs", array('id' => $degustation->_id, 'college' => $college)) ?>" method="post" class="form-horizontal degustation degustateurs">
          <div class="row row-margin row-button">
            <div class="col-xs-offset-4 col-xs-4 text-center">
            </div>
            <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></button></div>
          </div>

        <p class="mb-4">Sélectionnez l'ensemble des <?php echo strtolower($college_libelle); ?> en vue de leurs participations à la dégustation :</p>

         <?php echo $form->renderHiddenFields(); ?>

          <div class="bg-danger">
            <?php echo $form->renderGlobalErrors(); ?>
          </div>
          <div class="input-group" style="margin-bottom: 0; position: relative;">
              <span class="input-group-addon">Filtrer le tableau</span>
              <input id="table_filtre" type="text" class="form-control" placeholder="par nom, commune, code postal, adresse, tags" autofocus="autofocus" autocomplete="off" />
              <a href="" id="btn_annuler_filtre" tabindex="-1" class="small hidden" style="z-index: 3; right: 10px; top: 10px; position: absolute; color: grey;"><span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span></a>
          </div>
          <?php foreach ($form['degustateurs'] as $college => $collegeForm): ?>
            <table id="table_college_<?php echo $college;?>" class="table table-bordered table-condensed table-striped table_college table_filterable" style="border-width: 0;">
              <thead>
                <tr>
                  <th class="col-xs-11">Membre</th>
                  <th class="col-xs-1">Sélectionner?</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($collegeForm as $idCompte => $compteForm):
                  $compte = $form->getCompteByIdentifiant($idCompte);
                  if (!$compte) {
                      continue;
                  }
                  ?>
                  <tr class="vertical-center cursor-pointer searchable" data-words='<?= $words ?>'>
                    <td>
                        <?php echo $compte->getLibelleWithAdresse() ?>
                        <div class="pull-right">
                        <?php foreach ($compte->getTagsDegustateur($college) as $tag) : ?>
                            <span class='btn btn-xs btn-default'><?= $tag ?></span>
                        <?php endforeach ?>
                        </div>
                    </td>
                    <td class="text-center">
                      <div style="margin-bottom: 0;" class="form-group <?php if($compteForm['selectionne']->hasError()): ?>has-error<?php endif; ?>">
                        <?php echo $compteForm['selectionne']->renderError() ?>
                        <div class="col-xs-12">
                          <?php echo $compteForm['selectionne']->render(array('class' => "bsswitch", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                        </div>
                      </div>
                    </td>
                  </tr>
                <?php  endforeach; ?>
              </tbody>
            </table>
          <?php endforeach; ?>

          <div class="row row-margin row-button">
            <div class="col-xs-4"><a href="<?php echo (!$previous_college)? url_for("degustation_selection_lots", $degustation) : url_for("degustation_selection_degustateurs", array('id' => $degustation->_id, 'college' => $previous_college)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
            <div class="col-xs-4 text-center">
            </div>
            <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></button></div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php use_javascript('hamza_style.js'); ?>
