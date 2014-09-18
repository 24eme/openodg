<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2 class="panel-title">Récapitulatif</h2>
            </div>
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td>Période de distillation</td>
                        <td class="text-right"><?php echo 'du '.$drevmarc->debut_distillation.' au '.$drevmarc->fin_distillation ?> </td>
                    </tr>
                    <tr>
                        <td>Quantité de marc mise en oeuvre</td>
                         <td class="text-right"><?php echo $drevmarc->qte_marc ?> <small class="text-muted">kg</small></td>
                    </tr>
                    <tr>
                        <td>Volume total obtenu</td>
                        <td class="text-right"><?php echo $drevmarc->volume_obtenu ?> <small class="text-muted">hl d'alcool pur</small></td>
                    </tr>
                    <tr>
                        <td>Titre alcoométrique volumique</td>
                        <td class="text-right"><?php echo $drevmarc->titre_alcool_vol ?> <small class="text-muted">°</small></td>
                    </tr>
                    
                </tbody>
            </table>
        </div>
    </div>
</div>