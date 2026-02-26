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
                            if (ParcellaireConfiguration::getInstance()->isJeunesVignesEnabled() && $detail->isJeunesVignes()) {
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
                                <?php else: ?>
                                    <span class="text-danger">Aucune aire<span>
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
