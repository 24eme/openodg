<?php include_partial('step', array('step' => 'parcelles', 'parcellaire' => $parcellaire)); ?>

<div class="page-header">
    <h2>Saisie des parcelles</h2>
</div>

<ul class="nav nav-tabs">
    <?php foreach ($parcellaireAppellations as $appellationKey => $appellationName) : ?>
        <li role="presentation"  <?php echo ($appellation == $appellationKey) ? 'class="active"' : '' ?> ><a href="<?php echo url_for('parcellaire_parcelles', array('id' => $parcellaire->_id, 'appellation' => $appellationKey)) ?>"><?php echo $appellationName; ?></a></li>
    <?php endforeach; ?>
</ul>

<form action="<?php echo url_for('parcellaire_parcelles', array('id' => $parcellaire->_id, 'appellation' => $appellation)); ?>" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <div class="row">       
        <div class="col-xs-12">
            <div id="listes_cepages" class="list-group">
                <table class="table table-striped">
                    <tr>
                        <?php if ($appellation != 'LIEUDIT'): ?>
                            <th>Nom <?php echo $parcellaireAppellations[$appellation]; ?></th>      
                        <?php endif; ?>
                        <th>Identifiant parcelle</th>        
                        <th>Cépage</th>        
                        <th>Superficie</th>                 
                    </tr>
                    <?php foreach ($parcelles as $key => $parcelle):
                        ?>
                        <tr>
                            <?php if ($appellation != 'LIEUDIT'): ?>
                                <td><?php echo $parcelle->getLieuLibelle(); ?></td>        
                            <?php endif; ?>
                            <td><?php echo $parcelle->getParcelleIdentifiant(); ?></td>           
                            <td><?php echo $form['produits'][$parcelle->getHashForKey()]['cepage']->render(); ?></td>        
                            <td><?php echo $form['produits'][$parcelle->getHashForKey()]['superficie']->render(); ?></td>                 
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <div class="text-left">
                <button class="btn btn-warning ajax btn-sm" data-toggle="modal" data-target="#popupForm" type="button">Ajouter<span class="eleganticon icon_plus"></span></button>
            </div>
        </div>
    </div>
    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for("parcellaire_exploitation", $parcellaire) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>

        </div>
        <div class="col-xs-6 text-right">
            <?php if ($parcellaire->exist('etape') && $parcellaire->etape == ParcellaireEtapes::ETAPE_VALIDATION): ?>
                <button id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span> Retourner <small>à la validation</small>&nbsp;&nbsp;</button>
            <?php else: ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer <small>vers la validation</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
                <?php endif; ?>

        </div>
    </div>
</form>