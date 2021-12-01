<?php use_helper('Float'); ?>
<?php use_helper('PointsAides');?>

<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => DrevEtapes::ETAPE_LOTS, 'drev' => $drev, 'ajax' => true)) ?>

    <div class="page-header"><h2>Revendication des Lots IGP</h2></div>



    <?php echo include_partial('global/flash'); ?>
    <form role="form" action="<?php echo url_for("drev_lots", $drev) ?>" method="post" id="form_drev_lots" class="form-horizontal">

    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php foreach($drev->lots as $lot): ?>
      <?php if(!$lot->hasBeenEdited()){ continue; } ?>
      <div class="panel panel-default" style="border-color:rgba(130, 147, 69, 0.4);">
          <div class="panel-body panel-body-success">
            <div class="row">
              <div class="col-md-2"><?php echo Date::francizeDate($lot->date); ?></div>
              <div class="col-md-6"><strong><?php echo $lot->produit_libelle; ?></strong>
                <?php if(count($lot->cepages)): ?>
                  &nbsp;<small>
                    <?php echo $lot->getCepagesLibelle(); ?>
                  </small>
                <?php endif; ?>
              </div>
              <div class="col-md-3"><?php echo $lot->millesime; ?></div>
              <div class="col-md-1 text-right">
                <?php if($isAdmin): ?>
                  <a href="<?php echo url_for("drev_lots_delete", array('id' => $drev->_id, 'numArchive' => $lot->numero_archive)) ?>" onclick='return confirm("Étes vous sûr de vouloir supprimer ce lot ?");' class="close" title="Supprimer ce lot" aria-hidden="true">×</a>
                <?php endif; ?>
              </div>
            </div>
            <div class="row">
              <div class="col-md-2"></div>
              <div class="col-md-3">Numéro cuve : <?php echo $lot->numero_logement_operateur; ?></div>
              <div class="col-md-3"><strong>Volume : <?php echo $lot->volume; ?><small class="text-muted">&nbsp;hl</small></strong></div>
              <div class="col-md-3"><?php echo ($lot->destination_type)? DRevClient::$lotDestinationsType[$lot->destination_type] : ''; echo ($lot->destination_date)? " (".Date::francizeDate($lot->destination_date).")" : ""; ?></div>
              <div class="col-md-1" >
              </div>
            </div>
          </div>
          <div class="row"></div>
      </div>
    <?php endforeach; ?>
    <?php foreach($form['lots'] as $key => $lotForm): ?>
        <?php $lotItem = $drev->lots->get($key); ?>
        <?php if($key == count($form['lots']) - 1): ?>
          <a name="dernier"></a>
        <?php endif; ?>
        <?php include_partial('degustation/lotForm', array('form' => $lotForm, 'lot' => $lotItem)); ?>
    <?php endforeach; ?>
    <div class="text-right">
        <button type="submit" name="submit" value="add" id="lots_ajout" class="btn btn-default btn-block"><span class="text-primary"><span class="glyphicon glyphicon-plus-sign"></span> Ajouter un lot</span></button>
    </div>
    <div style="margin-top: 36px;" class="row row-margin row-button">
        <div class="col-xs-4">
            <a tabindex="-1" href="<?php echo (count($drev->getProduitsVci())) ? url_for('drev_vci', $drev) : url_for('drev_revendication_superficie', $drev) ?>?prec=1" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-4 text-center">
            <?php if ($sf_user->hasDrevAdmin() && $drev->hasDocumentDouanier()): ?>
              <a href="<?php echo url_for('drev_document_douanier', $drev); ?>" class="btn btn-default pull-left <?php if(!$drev->hasDocumentDouanier()): ?>disabled<?php endif; ?>" >
                  <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;<?php echo $drev->getDocumentDouanierType() ?>
              </a>
            <?php endif; ?>
        </div>
        <div class="col-xs-4 text-right">
            <button id="lots_continue" type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
    </div>
</form>
