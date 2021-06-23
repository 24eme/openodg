<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_RESULTATS)); ?>


<div class="page-header no-border">
  <h2>Présence des dégustateurs</h2>
  <h3><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?> <small><?php echo $degustation->getLieuNom(); ?></small></h3>
</div>

<p>Cocher les dégustateurs présents à chacune des tables</p>

<ul class="nav nav-pills">
  <?php for ($i= 0; $i < $nb_tables; $i++): ?>
    <li role="presentation" class="<?php if($numero_table == ($i + 1)): echo "active"; endif; ?>"><a href="<?php echo url_for("degustation_presences", array('id' => $degustation->_id, 'numero_table' => ($i + 1))) ?>">Table <?php echo DegustationClient::getNumeroTableStr($i + 1); ?></a></li>
  <?php endfor;?>
</ul>

<div class="row row-condensed">
  <div class="col-xs-12">
    <div class="panel panel-default">
      <div class="panel-body">
        <div class="row row-condensed">
          <div class="col-xs-12">
            <form action="<?php echo url_for("degustation_presences", array('id' => $degustation->_id, 'numero_table' => $numero_table)) ?>" method="post" class="form-horizontal degustation">
              <?php echo $form->renderHiddenFields(); ?>
              <div class="bg-danger">
                <?php echo $form->renderGlobalErrors(); ?>
              </div>
              <table class="table table-bordered table-condensed table-striped">
                <thead>
                  <tr>
                    <th class="col-xs-3">Collège</th>
                    <th class="col-xs-6">Membre</th>
                    <th class="col-xs-3">Présent à cette table</th>

                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($degustation->getDegustateursConfirmesTableOrFreeTable($numero_table) as $id_compte => $degustateur):
                    $name = $form->getWidgetNameFromDegustateur($degustateur);
                     ?>
                    <tr <?php if($degustateur->exist('confirmation') && ($degustateur->confirmation === false)): ?>class="disabled text-muted" disabled="disabled" style="text-decoration:line-through;"<?php endif; ?>>
                      <td><?php echo DegustationConfiguration::getInstance()->getLibelleCollege($degustateur->getParent()->getKey()) ?></td>
                      <td><a href="<?php echo url_for('compte_visualisation', array('identifiant' => $id_compte)) ?>" target="_blank"><?php echo $degustateur->get('libelle','') ?></a></td>
                      <td class="text-center">
                        <div style="margin-bottom: 0;" class="form-group <?php if($form[$name]->hasError()): ?>has-error<?php endif; ?>">
                          <?php echo $form[$name]->renderError() ?>
                          <div class="col-xs-12">
                            <?php echo $form[$name]->render(array('class' => "bsswitch", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                          </div>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach;?>
                </tbody>
              </table>
              <div class="row row-margin row-button">
                <div class="col-xs-4"><a href="<?php echo url_for("degustation_resultats_etape", $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
                <div class="col-xs-4 text-center">
                  <a href="<?php echo url_for("degustation_ajout_degustateurPresence", array('id' => $degustation->_id, "table" => $numero_table)) ?>" class="btn btn-default btn-upper">Ajouter un dégustateur</a>
                </div>
                <div class="col-xs-4 text-right">
                  <button type="submit" class="btn btn-primary btn-upper">Valider</button>
                </div>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
