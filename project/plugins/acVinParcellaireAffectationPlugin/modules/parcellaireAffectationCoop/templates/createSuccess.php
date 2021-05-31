<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $etablissement->identifiant, 'campagne' => $periode - 1)); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?>)</a></li>
  <li class="active"><a href="">Affectations parcellaires des apporteurs <?php echo $periode; ?></a></li>
</ol>

<div class="page-header no-border">
    <h2>Récupération des apporteurs depuis votre SV11</h2>
</div>

<p style="margin-top: 40px; margin-bottom: 40px;" class="text-center">En continuant vos apporteurs vont être récupérés depuis votre SV11.</p>
<form action="#" method="post" class="form-horizontal">
    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $etablissement->identifiant)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right">
            <button type="submit" class="btn btn-primary" id="btn_creation_affection_parcellaire_coop" >Continuer<span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
    </div>
</form>
