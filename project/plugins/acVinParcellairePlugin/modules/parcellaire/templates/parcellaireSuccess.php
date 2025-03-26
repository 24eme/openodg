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

<ol class="breadcrumb">
<?php if($sf_user->hasTeledeclaration()): ?>
  <li><a href="<?php echo url_for('parcellaire_declarant', $etablissement); ?>">Parcellaire</a></li>
<?php else: ?>
    <li><a href="<?php echo url_for('parcellaire'); ?>">Parcellaire</a></li>
<?php endif; ?>
  <li><a href="<?php echo url_for('parcellaire_declarant', $etablissement); ?>">Parcellaire de <?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?>) </a></li>
  <?php if($parcellaire): ?>
  <li><span class="text-muted"><?php echo $parcellaire->_id; ?></span></li>
  <?php endif; ?>
</ol>

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
        <?php include_partial('etablissement/blocDeclaration', array('etablissement' => $etablissement)); ?>
    </div>
<?php endif; ?>

<?php if ($parcellaire && count($parcellaire->getParcelles()) > 0): ?>
    <?php $parcellesByCommune = $parcellaire->getParcellesByCommune(false);
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
            <?php if (ParcellaireConfiguration::getInstance()->hasShowFilterProduitsConfiguration()): ?>
            <div class="form-group">
                <input type=checkbox id="voirnongere" onchange="if(document.querySelector('#voirnongere').checked){console.log('checked');document.querySelectorAll('.produitnongere').forEach(e => e.classList.remove('hidden'));}else{document.querySelectorAll('.produitnongere').forEach(e => e.classList.add('hidden'));}; console.log(document.querySelector('.produitnongere')); "> <label for="voirnongere">Voir toutes les parcelles (même celles déclarées au CVI sous une dénomination non gérée)</label>
            </div>
            <?php endif; ?>
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
                $superficie_tout = 0;
                $nb_parcelles_tout = 0;
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
                            if (!$detail->isRealProduit() && ParcellaireConfiguration::getInstance()->hasShowFilterProduitsConfiguration()) {
                                $classline .= ' hidden produitnongere';
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
                                if (ParcellaireConfiguration::getInstance()->isJeunesVignesEnabled() && !$detail->hasJeunesVignes()) {
                                    $cepage .= ' - jeunes vignes';
                                }
                            ?>
                            <tr data-words='<?php echo json_encode(array_merge(array(strtolower($lieu), strtolower($section.$num_parcelle),strtolower($compagne), strtolower($cepage), $ecart_pieds.'x'.$ecart_rang)), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>' class="<?php echo $classline ?> hamzastyle-item">
                            <?php $list_idu[]=$detail->idu; $list_communes[$detail["code_commune"]] = $detail["code_commune"]; ?>
                                <td><?php echo $lieu; ?></td>
                                <td class="" style="text-align: center;">
                                    <?php echo $section; ?> <?php echo $num_parcelle; ?><br/>
                                    <span class="text-muted"><?php echo $detail->getParcelleId(); ?></span>
                                </td>
                                <td class="<?php echo $classcepage; ?>">
                                    <span class="text-muted"><?php echo $detail->getProduitLibelle(); ?></span> <?php echo $cepage; ?><br/>
                                    <?php $aires = $detail->getIsInAires()->getRawValue(); if ($aires): ?>
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
                                            printf(' (%d&percnt; hors de l\'aire)', (1 - $detail->getPcAire($nom)) * 100);
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
                            <?php
                                if ($detail->isRealProduit()) {
                                    $superficie = $superficie + $detail->superficie;
                                    $nb_parcelles++;
                                }
                                $superficie_tout += $detail->superficie;
                                $nb_parcelles_tout++;
                            ?>
                            <?php endforeach; ?>
                    </tbody>
                <?php if (ParcellaireConfiguration::getInstance()->hasShowFilterProduitsConfiguration()): ?>
                    <?php if (!$nb_parcelles): ?>
                        <tr><td colspan="10"  style="text-align: center;"><i>L'opérateur ne possède pas sur cette commune de parcelle déclarée au CVI à un produit géré</i></td></tr>
                    <?php endif; ?>
                    <tr class="produitgere"><th colspan="4"  style="text-align: right;">Superficie des produits gérés</th><td style="text-align: right;"><strong><?php echoSuperficie($superficie); ?></strong></td><td colspan="4" style="text-align: left;"><?php echo $nb_parcelles; ?> parcelles</td></tr>
                    <tr class="hidden produitnongere"><th colspan="4"  style="text-align: right;">Superficie totale</th><td style="text-align: right;"><strong><?php echoSuperficie($superficie_tout); ?></strong></td><td colspan="4" style="text-align: left;"><?php echo $nb_parcelles_tout; ?> parcelles</td></tr>
                <?php else: ?>
                    <tr><th colspan="4"  style="text-align: right;">Superficie totale</th><td style="text-align: right;"><strong><?php echoSuperficie($superficie_tout); ?></strong></td><td colspan="4" style="text-align: left;"><?php echo $nb_parcelles_tout; ?> parcelles</td></tr>
                <?php endif; ?>
                </table>
    <?php endforeach; ?>
        </div>
    </div>
<?php
    $synthese = array();

    if($parcellaire) {
        $synthese = $parcellaire->getSyntheseCepages(ParcellaireConfiguration::getInstance()->hasShowFilterProduitsConfiguration());
    }

    if (count($synthese)):
?>
<h3 id="synthese_cepage">
    Synthèse par cépages
<?php if (ParcellaireConfiguration::getInstance()->hasShowFilterProduitsConfiguration()): ?>
    des produits reconnus au CVI
<?php endif; ?>
</h3>

<table class="table table-bordered table-condensed table-striped tableParcellaire">
  <thead>
    <tr>
        <th class="col-xs-4">Cépage <small class="text-muted">(jeunes vignes séparées)</small></th>
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
        <td class="text-right"><strong><?php echoSuperficie(array_sum(array_column($synthese->getRawValue(), 'superficie'))); ?></strong></td>
    </tr>
  </tbody>
</table>
<?php endif; ?>

<?php
    $synthese = array();
    if($parcellaire) {
        $synthese = $parcellaire->getSyntheseProduitsCepages(ParcellaireConfiguration::getInstance()->hasShowFilterProduitsConfiguration());
    }
    if (count($synthese)):
?>
<h3 id="synthese_produit">
    Synthèse par produits habilités
<?php if (ParcellaireConfiguration::getInstance()->hasShowFilterProduitsConfiguration()): ?>
    et reconnus au CVI
<?php endif; ?>
</h3>

<?php if (! HabilitationClient::getInstance()->getLastHabilitation($etablissement->identifiant)): ?>
    <div class="alert alert-warning" role="alert">
    L'opérateur n'a pas d'<a href="<?php echo url_for('habilitation_declarant', ['identifiant' => $etablissement->identifiant]) ?>">habilitation</a>
    </div>
<?php endif ?>

<table class="table table-bordered table-condensed table-striped tableParcellaire">
  <thead>
    <tr>
        <th class="col-xs-3">Produit</th>
        <th class="col-xs-8">Cépages autorisés <small class="text-muted">(hors jeunes vignes)</small></th>
        <th class="col-xs-1 text-center">Superficie max. <span class="text-muted small"><?php echo (ParcellaireConfiguration::getInstance()->isAres()) ? "(a)" : "(ha)" ?></span></th>
    </tr>
  </thead>
  <tbody>
<?php
    $cepages_autorises = [];
    foreach($synthese as $produit_libelle => $sous_synthese):
        $cepages_autorises = [];
        foreach($sous_synthese as $totalcepage => $cepages):
        foreach($cepages as $cepage_libelle => $s):
            if ($cepage_libelle == 'Total' || strpos($produit_libelle, 'XXXX') !== false): ?>
            <tr>
                <td><?php echo str_replace('XXXX', '', $produit_libelle); ?></td>
                <td><?php echo implode(', ', $cepages_autorises); ?></td>
                <td class="text-right"><?php echoSuperficie($s['superficie_max']); ?></td>
            </tr>
<?php       elseif(strpos($cepage_libelle, 'XXXX') === false):
                $cepages_autorises[] = $cepage_libelle;
            endif;
        endforeach;
        endforeach;
    endforeach;
?>
  </tbody>
</table>
<?php endif; ?>

<?php else: ?>
    <div class="row" style="min-height: 370px;">
        <div class="col-xs-12 text-center">
            <p>Aucune parcellaire n'existe pour <?php echo $etablissement->getNom() ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if ($parcellaire): ?>
<?php
    //Permet aux différentes régions d'avoir des liens personnalisables
    include_partial('downloadLinks', array('parcellaire' => $parcellaire));
?>
<?php endif; ?>

<?php if($sf_user->hasTeledeclaration()): ?>
<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $etablissement->identifiant)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
</div>
<?php endif;?>
<script type="text/javascript">
    var all_idu = JSON.parse('<?php echo json_encode(($list_idu)); ?>');
</script>
