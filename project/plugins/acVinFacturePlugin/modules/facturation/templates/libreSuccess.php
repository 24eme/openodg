<?php use_helper('Float'); ?>
<ol class="breadcrumb">
  <li class="visited"><a href="<?php echo url_for('facturation'); ?>">Facturation</a></li>
   <li class="active"><a href="<?php echo url_for('facturation_libre'); ?>">Facturation libre</a></li>
</ol>

<div class="row row-margin">
    <div class="col-xs-8">
        <h2>Liste des facturations libres</h2>

    </div>

    <div class="col-xs-4" style="padding-top: 20px;">
        <a href="<?php echo url_for("facturation_libre_creation"); ?>" class="btn btn-default pull-right ">Créer une nouvelle facturation libre</a>

    </div>
</div>
<div class="row row-margin">
    <div class="col-xs-8">
        <a href="<?php echo url_for("facturation_libre_comptabilite"); ?>" class="btn btn-default">Editer les données comptables</a>
    </div>
</div>
<br/>
<div class="row row-margin">
    <div class="col-xs-12">

        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="col-xs-4">Intitulé</th>
                    <th class="col-xs-1 text-center" >Date</th>
                    <th class="col-xs-2 text-center" >Nb mouvements (à facturer)</th>
                    <th class="col-xs-2 text-right">Montant (Restant à facturer)</th>
                    <th class="col-xs-2">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php
                  foreach ($facturationsLibre as $facturationLibre):
                ?>
                    <tr class="vertical-center">
                        <td class="col-xs-4 text-left"><?php echo $facturationLibre->libelle; ?></td>
                        <td class="col-xs-1 text-center"><?php echo Date::francizeDate($facturationLibre->date); ?></td>
                        <td class="col-xs-2 text-center"><?php echo $facturationLibre->getNbMvts() . ' (' . $facturationLibre->getNbMvtsAFacture() . ')'; ?></td>
                        <td class="col-xs-2 text-right"><?php echo sprintFloat($facturationLibre->getTotalHt()) . '&nbsp;&euro; (' . sprintFloat($facturationLibre->getTotalHtAFacture()) . '&nbsp;&euro;)'; ?></td>
                        <td class="col-xs-2 text-center">

                            <div class="col-xs-6 text-right">
                                <a href="<?php echo url_for('facturation_libre_edition', array('id' => $facturationLibre->identifiant)); ?>" class="btn btn-default">Modifier</a>
                            </div>
                            <?php if (!$facturationLibre->getNbMvtsAFacture()): ?>
                                <div class="col-xs-6 text-left">
                                    <a class="btn btn-default" href="<?php echo url_for('facturation_libre_suppression', array('id' => $facturationLibre->identifiant)); ?>">
                                        <span class="glyphicon glyphicon-remove"></span>
                                    </a>
                                </div>
                            <?php endif; ?>


                        </td>
                    <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<br/>
<div class="row">
    <div class="col-xs-12">
        <a href="<?php echo url_for("facturation"); ?>" class="btn btn-default">Retour à la facturation</a>

    </div>
</div>
