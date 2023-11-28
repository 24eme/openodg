<?php use_helper('Float'); ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href="<?php echo url_for('degustation_declarant_lots_liste',array('identifiant' => $etablissement->identifiant)); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?> - <?php echo $etablissement->cvi ?>)</a></li>
  <li><a href="<?php echo url_for('degustation_declarant_lots_liste',array('identifiant' => $etablissement->identifiant, 'campagne' => $lot->campagne)); ?>" ><?php echo $lot->campagne ?></a>
  <li><a href="" class="active" >N° dossier : <?php echo $lot->numero_dossier ?> - N° archive : <?php echo $lot->numero_archive ?></a></li>
</ol>

<h2>Création d'un courrier pour <?php echo $etablissement->getNom(); ?></h2>

<?php include_partial('global/flash'); ?>

<?php include_partial('chgtdenom/infoLotOrigine', array('lot' => $lot, 'opacity' => false)); ?>

<form action="<?php echo url_for('courrier_extras', array('identifiant' => $etablissement->identifiant, 'unique_id' => $lot->unique_id, 'id_form' => $courrier->_id)) ?>" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>

    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>

    <div class="row">
        <div class="form-group">
              <div class="col-sm-4 control-label">
                  <strong>Type de courrier à générer :</strong>
              </div>
              <div class="col-sm-4">
                  <p class="form-control render" disabled><?php echo $courrier->courrier_titre ?></p>
              </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4 control-label">
              <?php echo $form["agent_nom"]->renderError(); ?>
              <?php echo $form["agent_nom"]->renderLabel(); ?> :
            </div>
            <div class="col-sm-4">
                <?php echo $form["agent_nom"]->render() ?>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4 control-label">
              <?php echo $form["representant_nom"]->renderError(); ?>
              <?php echo $form["representant_nom"]->renderLabel(); ?> :
            </div>
            <div class="col-sm-4">
                <?php echo $form["representant_nom"]->render() ?>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4 control-label">
              <?php echo $form["representant_fonction"]->renderError(); ?>
              <?php echo $form["representant_fonction"]->renderLabel(); ?> :
            </div>
            <div class="col-sm-4">
                <?php echo $form["representant_fonction"]->render() ?>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4 control-label">
              <?php echo $form["analytique_date"]->renderError(); ?>
              <?php echo $form["analytique_date"]->renderLabel("Date d'analyse"); ?>
            </div>
            <div class="col-sm-4">
                <div class="input-group date-picker">
                    <?php echo $form["analytique_date"]->render(array("class" => "form-control")); ?>
                    <div class="input-group-addon">
                        <span class="glyphicon-calendar glyphicon"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4 control-label">
              <?php echo $form["analytique_conforme"]->renderError(); ?>
              <?php echo $form["analytique_conforme"]->renderLabel(); ?> :
            </div>
            <div class="col-sm-2 checkbox ml-4">
                <?php echo $form["analytique_conforme"]->render() ?>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4 control-label">
              <?php echo $form["analytique_libelle"]->renderError(); ?>
              <?php echo $form["analytique_libelle"]->renderLabel(); ?> :
            </div>
            <div class="col-sm-4">
                <?php echo $form["analytique_libelle"]->render() ?>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4 control-label">
              <?php echo $form["analytique_code"]->renderError(); ?>
              <?php echo $form["analytique_code"]->renderLabel(); ?> :
            </div>
            <div class="col-sm-4">
                <?php echo $form["analytique_code"]->render() ?>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4 control-label">
              <?php echo $form["analytique_niveau"]->renderError(); ?>
              <?php echo $form["analytique_niveau"]->renderLabel(); ?> :
            </div>
            <div class="col-sm-4">
                <?php echo $form["analytique_niveau"]->render() ?>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4 control-label">
              <?php echo $form["organoleptique_code"]->renderError(); ?>
              <?php echo $form["organoleptique_code"]->renderLabel(); ?> :
            </div>
            <div class="col-sm-4">
                <?php echo $form["organoleptique_code"]->render() ?>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4 control-label">
              <?php echo $form["organoleptique_niveau"]->renderError(); ?>
              <?php echo $form["organoleptique_niveau"]->renderLabel(); ?> :
            </div>
            <div class="col-sm-4">
                <?php echo $form["organoleptique_niveau"]->render() ?>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4 control-label">
              <?php echo $form["vin_emplacement"]->renderError(); ?>
              <?php echo $form["vin_emplacement"]->renderLabel(); ?> :
            </div>
            <div class="col-sm-4">
                <?php echo $form["vin_emplacement"]->render() ?>
            </div>
        </div>


        <div class="col-xs-2">
        </div>
        <div class="col-xs-7">
        </div>
        <div class="col-xs-3">
            <button type="submit" class="btn btn-success">Générer le courrier</button>
        </div>
    </div>
</div>
