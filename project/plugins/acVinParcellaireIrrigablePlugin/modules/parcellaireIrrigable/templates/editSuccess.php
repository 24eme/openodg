<?php use_helper('Float'); ?>


<form action="#" method="post" class="form-horizontal parcellaireForm">

    <div class="row">
        <div class="col-xs-12">
            <div id="listes_cepages">
                <?php if (count($parcellesLast)) : ?>
                  <?php foreach ($parcellesLast as $produitKey => $parcellesProduit): ?>
                    <h2><?php echo $parcellesProduit->libelle; ?></h2>
                    <table class="table table-bordered table-condensed table-striped">
                        <thead>
                            <tr>
                                <th class="col-xs-1">Irrigable</th>
                                <th class="col-xs-3" >Commune/Identifiant</th>
                                <th class="col-xs-2">Cépage</th>
                                <th class="col-xs-1">Superficie</th>
                                <th class="col-xs-2">Campagne Plantation</th>
                                <th class="col-xs-1">Écart Pieds</th>
                                <th class="col-xs-1">Écart Rang</th>
                                <th class="col-xs-2"style="text-align: right;" >Mode Savoir-faire</th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($parcellesProduit->detail as $parcelleKey => $detail): ?>
                                <tr >
                                    <td class="text-center">
                                      X
                                    </td>
                                    <td class="col-xs-3" >
                                        <?php echo $detail->getParcelleIdentifiant(); ?>
                                    </td>
                                    <td class="col-xs-2" >
                                        <?php echo $detail->getCepageLibelle();  ?>
                                    </td>
                                    <td class="col-xs-1 " style="text-align: right; ">
                                      <?php printf("%0.2f&nbsp;ares", $detail->superficie); ?>
                                    </td>
                                    <td class="col-xs-2" style="text-align: center;"><?php echo ($detail->exist('campagne_plantation'))? $detail->get('campagne_plantation') : '&nbsp;'; ?> </td>
                                    <td class="col-xs-1" style="text-align: center;"><?php echo ($detail->exist('ecart_pieds'))? $detail->get('ecart_pieds') : '&nbsp;'; ?> </td>
                                    <td class="col-xs-1" style="text-align: center;"><?php echo ($detail->exist('ecart_rang'))? $detail->get('ecart_rang') : '&nbsp;'; ?> </td>
                                    <td class="col-xs-2 <?php echo $classparcelle ?>" style="text-align: right; <?php echo $styleparcelle; ?>"><?php echo ($detail->exist('mode_savoirfaire') && array_key_exists($detail->get('mode_savoirfaire'),ParcellaireClient::$modes_savoirfaire))? ParcellaireClient::$modes_savoirfaire[$detail->get('mode_savoirfaire')] : '&nbsp;'; ?></td>
                                </tr>
                                <?php
                            endforeach;
                            ?>
                        </tbody>
                    </table>

                    <?php
                endforeach;
                ?>
                </div>
            <?php else : ?>
                <p class="text-muted">Vous n'avez affecté aucune parcelle pour cette appellation.</p><br/>
            <?php endif; ?>
        </div>
    </div>
    <div class="row row-margin row-button">
        <div class="col-xs-6">
                <a href="#" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Précédent</a>
        </div>
        <div class="col-xs-6 text-right">
              <button type="submit" class="btn btn-default btn-lg btn-upper btn-default-step">Continuer&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
        </div>
    </div>
</form>
