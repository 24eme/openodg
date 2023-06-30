<h2>Calcul du Potentiel de Production en AOC Côtes de Provence</h2>

<div class="intro">
  <p>Ce formulaire vous permet de calculer votre potentiel de production et de le télécharger au format PDF.</p>

  <p><strong>Le potentiel de production est :</strong></p>
  <ul>
    <li>exprimé en hectares</li>
    <li>calculé pour la récolte de l’année</li>
    <li>calculé à partir des surfaces de votre fiche CVI à jour</li>
    <li>calculé par exploitation et par contrat de métayage</li>
  </ul>

  <p><strong>Ces superficies sont établies de la manière suivante :</strong></p>
  <ul>
    <li>après calcul et vérification d’encépagement conforme et d’âge minimal requis</li>
    <li>sur des parcelles classées en CDP ou affectées en Dénomination Géographique Complémentaire (DGC) pour l’année</li>
    <li>en respectant les règles d’encépagement du cahier des charges pour l’AOC ou la DGC concernée</li>
  </ul>

  <p>Vous pouvez voir le cahier des charges de l’appellation d’origine protégée ici :  <a href="https://syndicat-cotesdeprovence.com/medias/2022/01/CDC-Cotes-de-Provence-homologue-par-arrete-du-22-decembre-2021.pdf">CDC-Cotes-de-Provence-homologue-par-arrete-du-22-decembre-2021.pdf</a> (voir V. Encépagement, 1° Encépagement et 2° Règles de proportion à l'exploitation)</p>

  <p><strong>À noter :</strong> Renseignez les surfaces propres à chaque cépages, sans arrondir les surfaces et sans prendre en compte le pourcentage de manquants.</p>
</div>

<div class="form" style="margin-top: 20px;">
  <h3>Formulaire de calcul du potentiel de production</h3>
  <form style="margin-top: 20px;" role="form" action="" method="post" id="calcul_pp" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <div class="row form-group">
      <div class="col-xs-12">
        <?php echo $form['dgc']->renderLabel("Dénomination Géographique Complémentaire (DGC), si applicable"); ?>
        <?php echo $form['dgc']->render(); ?>
        <?php echo $form['dgc']->renderError(); ?>
      </div>
    </div>

    <div class="form-group row row-margin">

      <div class="col-xs-12">
        <div class="panel panel-default">
          <div class="panel-heading">
              <h3 class="panel-title"><label>Superficies en hectare des parcelles par cépage</label></h3>
          </div>
          <div class="panel-body">
            <div class="row form-group">
              <?php foreach ($form->getCepages() as $cepage): ?>
                <?php $name = $form->getCepageKey($cepage); ?>
                <?php echo $form[$name]->renderLabel($cepage . " :", array('class' => "col-sm-3 control-label")); ?>
                <div class="col-sm-3" style="padding:10px;">
                  <?php echo $form[$name]->render(); ?>
                  <?php echo $form[$name]->renderError(); ?>
                  ha
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="form-group row row-margin row-button">
      <div class="col-xs-12 text-right">
          <button type="submit" class="btn btn-primary btn-upper">Calculer</button>
      </div>
    </div>

  </form>
</div>