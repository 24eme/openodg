<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>


<?php if ($sf_user->hasFlash('notice')): ?>
  <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<div class="page-header no-border">
  <h2>Saisie des résultats de conformité</h2>
  <h3><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?> <small><?php echo $degustation->getLieuNom(); ?></small></h3>
</div>

<p>Cocher les échantillons conformes à chaque tables</p>

<ul class="nav nav-pills">
  <?php for ($i= 0; $i < $nb_tables; $i++): ?>
    <li role="presentation" class="<?php if($numero_table == ($i + 1)): echo "active"; endif; ?>"><a href="<?php echo url_for("degustation_resultats", array('id' => $degustation->_id, 'numero_table' => ($i + 1))) ?>">Table <?php echo DegustationClient::getNumeroTableStr($i + 1); ?></a></li>
  <?php endfor;?>
</ul>

<div class="row row-condensed">
  <div class="col-xs-12">
    <div class="panel panel-default">
      <div class="panel-body">

        <div class="row row-condensed">
          <div class="col-xs-12">
            <form action="<?php echo url_for("degustation_resultats", array('id' => $degustation->_id, 'numero_table' => $numero_table)) ?>" method="post" class="form-horizontal degustation">
              <?php echo $form->renderHiddenFields(); ?>
              <div class="bg-danger">
                <?php echo $form->renderGlobalErrors(); ?>
              </div>

              <h3>Conformité des échantillons</h3>
              <table class="table table-bordered table-condensed">
                <thead>
                  <tr>
                    <th class="col-xs-10">Échantillons</th>
                    <th class="col-xs-1"></th>
                    <th class="col-xs-1">Conformité</th>
                    <th class="col-xs-1" style="display:none">Courrier</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($form->getTableLots() as $lot):
                    $name = $form->getWidgetNameFromLot($lot);
                    if (isset($form["conformite_".$name])): ?>
                      <tr class="vertical-center cursor-pointer <?php if($lot->isNonConforme()): ?>list-group-item-danger<?php elseif($lot->isConformeObs()): ?>list-group-item-warning<?php  endif; ?>" data-toggle="modal" data-target="#popupResultat_<?php echo $name; ?>">
                        <td>
                          <div class="row">
                            <div class="col-xs-4 text-right"><?php if ($lot->leurre === true): ?><em>Leurre</em> <?php endif ?>
                              <?php echo $lot->declarant_nom.' ('.$lot->numero_cuve.')'; ?></div>
                            <div class="col-xs-4 text-right"><?php echo $lot->produit_libelle;?></div>
                            <div class="col-xs-3 text-right"><small class="text-muted"><?php echo $lot->details; ?></small></div>
                            <div class="col-xs-1 text-right"><?php echo ($lot->millesime)? ' ('.$lot->millesime.')' : ''; ?></div>
                          </div>
                        </td>
                        <td class="text-center">
                          <div style="margin-bottom: 0;">
                            <div class="col-xs-12">
                              <a
                                class="label <?php if($lot->isNonConforme()): ?>label-danger<?php elseif($lot->isConformeObs()): ?>label-warning<?php else: ?>label-success<?php endif; ?>">
                                  <span class="glyphicon <?php if($lot->isNonConforme()): ?>glyphicon-remove<?php else: ?>glyphicon-ok<?php endif ?>"></span></a>
                            </div>
                          </div>
                        </td>
                        <td class="text-center">
                          <?php if (!$lot->leurre): ?>
                            <?php if(!$lot->isNonConforme() && !$lot->isConformeObs()): ?>
                              <span class="text-muted glyphicon glyphicon-pencil"></span>
                            <?php else: ?>
                              <?php echo $lot->getShortLibelleConformite(); ?>
                            <?php endif; ?>
                          <?php endif; ?>
                        </td>
                        <td class="text-center" hidden>
                          <?php if(!$lot->isNonConforme()): ?>
                          <a class="btn" href="<?php echo url_for('degustation_conformite_pdf',array('id' => $degustation->_id, 'identifiant' => $lot->declarant_identifiant)) ?>">PDF</a>
                          <?php else: ?>
                            <a class="btn" href="<?php echo url_for('degustation_non_conformite_pdf',array('id' => $degustation->_id, 'identifiant' => $lot->declarant_identifiant)) ?>">PDF</a>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php  endif; ?>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <div class="row row-margin row-button">
                <div class="col-xs-4"><a href="<?php echo url_for("degustation_visualisation", $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
                <div class="col-xs-4 text-center">
                </div>
                <div class="col-xs-4 text-right">
                  <button type="submit" class="btn btn-primary btn-upper">Valider</button>
                </div>
              </div>
              <?php
              foreach ($form->getTableLots() as $lot):
                $name = $form->getWidgetNameFromLot($lot);
                include_partial('degustation/popupResultats', array('form' => $form, 'name' => $name));
              endforeach;
              ?>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
