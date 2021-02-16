<div class="alert alert-info" role="alert" style="padding:10px;padding-top:5px;">
  <h3><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?></h3>
  <h4>Lieu : <?php echo $degustation->getLieuNom(); ?></h4>
  <table class="table table-condensed">
    <tbody>
      <tr class="vertical-center">
        <td class="col-xs-3" >Nombre total de <strong>lots prévus&nbsp;:</strong></td>
        <td class="col-xs-9"><strong><?php echo $infosDegustation["nbLotsSansLeurre"]; ?></strong></td>
      </tr>
      <tr class="vertical-center">
        <td class="col-xs-3" >Nombre total <strong>d'adhérents prélevés&nbsp;:</strong></td>
        <td class="col-xs-9"><strong id="nbAdherentsAPrelever"><?php echo $infosDegustation["nbAdherents"]; ?></strong></td>
      </tr>
    </tbody>
  </table>
</div>
