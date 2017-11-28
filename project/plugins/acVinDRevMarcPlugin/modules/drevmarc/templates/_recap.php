<?php use_helper("Date"); ?>
<?php use_helper('DRevMarc') ?>
<div class="row">
    <div class="col-xs-12">
        <table class="table table-striped">
            <tbody>
                <tr>
                    <td>Période de distillation</td>
                    <td class="text-right"><?php echo getDatesDistillation($drevmarc); ?> </td>
                </tr>
                <tr>
                    <td>Quantité de marc mise en oeuvre</td>
                    <td class="text-right"><?php echo getQtemarc($drevmarc); ?></td>
                </tr>
                <tr>
                    <td>Volume total obtenu</td>
                    <td class="text-right"><?php echo getVolumeObtenu($drevmarc); ?></td>
                </tr>
                <tr>
                    <td>Titre alcoométrique volumique</td>
                    <td class="text-right"><?php echo getTitreAlcoolVol($drevmarc); ?></td>
                </tr>
                
            </tbody>
        </table>
    </div>
</div>