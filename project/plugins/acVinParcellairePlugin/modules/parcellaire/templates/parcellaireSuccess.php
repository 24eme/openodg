<?php use_javascript('hamza_style.js?20230328'); ?>
<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>
<?php
function echoSuperficie($s) {
    if (ParcellaireConfiguration::getInstance()->isAres()) {
        echo formatFloatFr($s * 100, 2, 2);
        return ;
    }
    echo formatFloatFr($s, 4, 4);
}
$parcellaire_client = ParcellaireClient::getInstance();
$last = null;
$list_communes = [];
$list_idu = [];
?>

<?php if($sf_user->hasTeledeclaration()): ?>
<ol class="breadcrumb">
  <li><a href="<?php echo url_for('parcellaire_declarant', $parcellaire->getEtablissementObject()); ?>">Parcellaire</a></li>
  <?php if($parcellaire): ?><li><a href="<?php echo url_for('parcellaire_declarant', $parcellaire->getEtablissementObject()); ?>">Parcellaire de <?php echo $parcellaire->getEtablissementObject()->getNom() ?> (<?php echo $parcellaire->getEtablissementObject()->identifiant ?>) </a></li><?php endif;?>
</ol>
<?php else: ?>
<ol class="breadcrumb">
  <li><a href="<?php echo url_for('parcellaire'); ?>">Parcellaire</a></li>
  <li><a href="<?php echo url_for('parcellaire_declarant', $etablissement); ?>">Parcellaire de <?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?>) </a></li>
</ol>
<?php endif; ?>

<?php if ($sf_user->isAdmin() && class_exists("EtablissementChoiceForm") && isset($form)): ?>
    <?php include_partial('etablissement/formChoice', array('form' => $form, 'action' => url_for('parcellaire_etablissement_selection'), 'noautofocus' => true)); ?>
<?php endif; ?>

<div class="page-header no-border">
    <?php if($parcellaire): ?>
    <h2>Parcellaire au <?php echo Date::francizeDate($parcellaire->date); ?> <small class="text-muted"><?= $parcellaire->source ?></small></h2>
    <?php else: ?>
    <h2>Parcellaire</h2>
    <?php endif;?>
</div>

<div class="clearfix">
  <a href="<?= url_for('parcellaire_scrape_douane', $etablissement) ?>" class="btn btn-warning pull-right" style="margin-bottom: 10px;">
      <i class="glyphicon glyphicon-refresh"></i> Mettre à jour via Prodouane
  </a>
</div>

<?php include_partial('global/flash'); ?>

<?php if($parcellaire): ?>
    <div class="well">
        <?php include_partial('etablissement/blocDeclaration', array('etablissement' => $parcellaire->getEtablissementObject())); ?>
    </div>
<?php endif; ?>

<?php if ($parcellaire && count($parcellaire->declaration) > 0): ?>
    <?php $parcellesByCommune = $parcellaire->declaration->getParcellesByCommune();
    $import = $parcellaire->getGeoJson(); ?>

    <?php if($parcellaire && $parcellaire->getGeoJson() != false): ?>
        <div id="jump">
            <a name="carte"></a>
            <?php include_partial('parcellaire/parcellaireMap', array('parcellaire' => $parcellaire)); ?>
        </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-xs-5">
          <h3>Accès rapide</h3>
          <ul>
            <?php foreach (array_keys($parcellesByCommune->getRawValue()) as $commune): ?>
              <li style="list-style-type: disclosure-closed"><a href="#parcelles_<?php echo $commune ?>">Parcelles de <?php echo ucwords(strtolower($commune), "- \t\r\n\f\v") ?></a></li>
            <?php endforeach ?>
            <li style="list-style-type: disclosure-closed"><a href="#synthese_cepage">Synthèse par cépages</a></li>
            <li style="list-style-type: disclosure-closed"><a href="#synthese_produit">Synthèse par produits</a></li>
          </ul>
      </div>
    </div>

    <?php if(!empty($import)): ?>
    <div class="row">
        <div class="col-xs-12">
            <h3>Filtrer</h3>
            <div class="form-group">
                <input id="hamzastyle" onchange="filterMap()" type="hidden" data-placeholder="Saisissez un Cépage, un numéro parcelle ou une compagne :" data-hamzastyle-container=".tableParcellaire" data-mode="OR" class="hamzastyle form-control" />
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-xs-12">
            <?php foreach ($parcellesByCommune as $commune => $parcelles): ?>
                <h3 id="parcelles_<?php echo $commune ?>"><?php echo $commune ?></h3>
            <?php
                $superficie = 0;
                $nb_parcelles = 0;
                ?>
                <table class="table table-bordered table-condensed table-striped tableParcellaire">
                  <thead>
		        	<tr>
		                <th class="col-xs-1">Lieu-dit</th>
                    <th class="col-xs-1">Section / N° parcelle</th>
                    <th class="col-xs-4">Cépage</th>
                    <th class="col-xs-1" style="text-align: center;">Année plantat°</th>
                    <th class="col-xs-1" style="text-align: right;">Superficie <span class="text-muted small"><?php echo (ParcellaireConfiguration::getInstance()->isAres()) ? "(a)" : "(ha)" ?></span></th>
                    <th class="col-xs-1">Écart Pieds/Rang</th>
                    <?php if(!empty($import)): ?>
                    <th class="col-xs-1">Carte</th>
                    <?php endif; ?>
		            </tr>
                  </thead>
                    <tbody>
                        <?php foreach ($parcelles as $detail):
                            $classline = '';
                            $styleparcelle = '';
                            $classecart = '';
                            $classcepage = '';
                            if ($detail->hasProblemExpirationCepage()) {
                              $classline .=  ' warning';
                              $classcepage .= ' text-warning strong hasProblemExpirationCepage';
                            }
                            if ($detail->hasProblemEcartPieds()) {
                              $classline .=  ' danger';
                              $classecart .= ' text-danger strong hasProblemEcartPieds';
                            }
                            if ($detail->hasProblemCepageAutorise()) {
                              $classline .= ' danger';
                              $classcepage .= ' text-danger strong hasProblemCepageAutorise';
                            }
                            ?>
                            <?php
                                $lieu = $detail->lieu;
                                $compagne = $detail->campagne_plantation;
                                $section = $detail->section;
                                $num_parcelle = $detail->numero_parcelle;
                                $ecart_pieds = ($detail->exist('ecart_pieds')) ? $detail->get('ecart_pieds'):'&nbsp;';
                                $ecart_rang = ($detail->exist('ecart_rang')) ? $detail->get('ecart_rang'):'&nbsp;';
                                $cepage = $detail->cepage;
                                if (ParcellaireConfiguration::getInstance()->isTroisiemeFeuille() && !$detail->hasTroisiemeFeuille()) {
                                    $cepage .= ' - jeunes vignes';
                                }
                            ?>
                            <tr data-words='<?php echo json_encode(array_merge(array(strtolower($lieu), strtolower($section.$num_parcelle),strtolower($compagne), strtolower($cepage), $ecart_pieds.'x'.$ecart_rang)), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>' class="<?php echo $classline ?> hamzastyle-item">
                            <?php $list_idu[]=$detail->idu; $list_communes[$detail["code_commune"]] = $detail["code_commune"]; ?>
                                <td><?php echo $lieu; ?></td>
                                <td class="" style="text-align: center;">
                                    <?php echo $section; ?> <?php echo $num_parcelle; ?></br>
                                    <span class="text-muted"><?php echo $detail->idu; ?></span>
                                </td>
                                <td class="<?php echo $classcepage; ?>">
                                    <span class="text-muted"><?php echo $detail->getProduitLibelle(); ?></span> <?php echo $cepage; ?><br/>
                                    <?php $aires = $detail->getIsInAires(); if ($aires): ?>
                                    <span class="text-muted">Aire(s):</span>
                                    <?php
                                    $separateur = '';
                                    foreach($aires as $nom => $a) {
                                        echo "$separateur ";
                                        if ($a != AireClient::PARCELLAIRE_AIRE_TOTALEMENT){
                                            echo '<span class="text-danger">';
                                        }else{
                                            echo '<span class="text-muted">';
                                        }
                                        if($a == AireClient::PARCELLAIRE_AIRE_HORSDELAIRE) {
                                            echo "Hors de l'aire ".$nom;
                                        } elseif($a == AireClient::PARCELLAIRE_AIRE_PARTIELLEMENT) {
                                            echo "Partiellement ".$nom;
                                        } elseif($a == AireClient::PARCELLAIRE_AIRE_EN_ERREUR) {
                                            echo "Erreur interne sur ".$nom;
                                        } else {
                                            echo $nom;
                                        }
                                        echo "</span>";
                                        $separateur = ',';
                                    }?>
                                    <?php endif; ?>
                                </td>
                                <td class="" style="text-align: center;"><?php echo $compagne; ?></td>
                                <td class="" style="text-align: right;"><?php echoSuperficie($detail->superficie); ?>
                                </td>
                                <td class="<?php echo $classecart; ?>" style="text-align: center;" ><?php echo $ecart_pieds; ?> / <?php echo $ecart_rang; ?></td>

                                <?php if(!empty($import)): ?>
                                <td style="text-align: center;">
                                    <div id="<?php echo $detail->idu; ?>">
                                        <button class="btn btn-link" onclick="showParcelle('<?php echo $detail->idu; ?>')"><i class="glyphicon glyphicon-map-marker"></i></button>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php $superficie = $superficie + $detail->superficie; ?>
                            <?php $nb_parcelles++; ?>
                            <?php endforeach; ?>
                    </tbody>
                    <tr><th colspan="4"  style="text-align: right;">Superficie totale</th><td style="text-align: right;"><strong><?php echoSuperficie($superficie); ?></strong></td><td colspan="4" style="text-align: left;"><?php echo $nb_parcelles; ?> parcelles</td></tr>
                </table>
    <?php endforeach; ?>
        </div>
    </div>
<?php
    $synthese = array();

    if($parcellaire) {
        $synthese = $parcellaire->getSyntheseCepages();
    }

    if (count($synthese)):
?>
<h3 id="synthese_cepage">Synthèse par cépages</h3>

<table class="table table-bordered table-condensed table-striped tableParcellaire">
  <thead>
    <tr>
        <th class="col-xs-4">Cépage</th>
        <th class="col-xs-4 text-center" colspan="2">Superficie <span class="text-muted small"><?php echo (ParcellaireConfiguration::getInstance()->isAres()) ? "(a)" : "(ha)" ?></span></th>
    </tr>
  </thead>
  <tbody>
<?php

    foreach($synthese as $cepage_libelle => $s): ?>
        <tr>
            <td><?php echo $cepage_libelle ; ?></td>
            <td class="text-right"><?php echoSuperficie($s['superficie']); ?></td>
<?php
    endforeach;
?>
    <tr>
        <td><strong>Total</strong></td>
        <td class="text-right"><strong><?php echo array_sum(array_column($synthese->getRawValue(), 'superficie')) ?></strong></td>
    </tr>
  </tbody>
</table>
<?php endif; ?>

<?php
    $synthese = array();

    if($parcellaire) {
        $synthese = $parcellaire->getSyntheseProduitsCepages();
    }
    if (count($synthese)):
?>
<h3 id="synthese_produit">Synthèse par produits habilités</h3>

<?php if (! HabilitationClient::getInstance()->getLastHabilitation($etablissement->identifiant)): ?>
    <div class="alert alert-warning" role="alert">
    L'opérateur n'a pas d'<a href="<?php echo url_for('habilitation_declarant', ['identifiant' => $etablissement->identifiant]) ?>">habilitation</a>
    </div>
<?php endif ?>

<table class="table table-bordered table-condensed table-striped tableParcellaire">
  <thead>
    <tr>
        <th class="col-xs-4">Produit</th>
        <th class="col-xs-4">Cépage</th>
        <th class="col-xs-4 text-center" colspan="2">Superficie <span class="text-muted small"><?php echo (ParcellaireConfiguration::getInstance()->isAres()) ? "(a)" : "(ha)" ?></span></th>
    </tr>
  </thead>
  <tbody>
<?php

    foreach($synthese as $produit_libelle => $sous_synthese):
        foreach($sous_synthese as $cepage_libelle => $s): ?>
        <tr>
            <?php if ($cepage_libelle == 'Total'): ?>
                <th><?php echo $produit_libelle ; ?></th>
                <th><?php echo $cepage_libelle ; ?></th>
                <?php if ($s['superficie_min'] == $s['superficie_max']): ?>
                <th class="text-right" colspan="2"><?php echoSuperficie($s['superficie_min']); ?></th>
                <?php else: ?>
                <th class="text-right"><?php echoSuperficie($s['superficie_min']); ?></th><th class="text-right"><?php echoSuperficie($s['superficie_max']); ?></th>
                <?php endif; ?>
            <?php else: ?>
                <td><?php echo $produit_libelle ; ?></td>
                <td><?php echo $cepage_libelle ; ?></td>
                <?php if ($s['superficie_min'] == $s['superficie_max']): ?>
                <td class="text-right" colspan="2"><?php echoSuperficie($s['superficie_min']); ?></td>
                <?php else: ?>
                <td class="text-right"><?php echoSuperficie($s['superficie_min']); ?></td><td class="text-right"><?php echoSuperficie($s['superficie_max']); ?></td>
                <?php endif; ?>
            <?php endif; ?>
        </tr>
<?php
        endforeach;
    endforeach;
?>
  </tbody>
</table>
<?php endif; ?>

<?php else: ?>
    <div class="row">
        <div class="col-xs-12">
            <p>Aucun parcellaire n'existe pour <?php echo $etablissement->getNom() ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if ($parcellaire): ?>
<?php include_partial('downloadLinks', array('parcellaire' => $parcellaire)); ?>
<?php endif; ?>

<?php if($sf_user->hasTeledeclaration()): ?>
<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $parcellaire->identifiant)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
</div>
<?php endif;?>
<script type="text/javascript">
    var all_idu = JSON.parse('<?php echo json_encode(($list_idu)); ?>');
</script>
