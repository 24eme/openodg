<?php use_helper('TemplatingPDF'); ?>
<style>
<?php echo style(); ?>
.bg-white{
  background-color:white;
}

</style>
    <div>
      <table>
          <tr>
            <td style="width:33%;">
              <p>Code Commission: _ _ _ _ </p>
            </td>
            <td style="width:60%;">
              <p>Responsable : _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _</p>
            </td>
            <td style="width:2%">
            </td>
          </tr>

          <tr>
            <td style="width:33%;">
              <p>Date : <?php $date = explode("-", substr($degustation->date, 0, 10));echo "$date[2]/$date[1]/$date[0]"; ?></p>
            </td>
            <td style="width:33%;">
              <p>Heure : <?php echo substr($degustation->date, -5); ?></p>
            </td>
            <td style="width:33%">
              <p>Lieu : <?php echo $degustation->lieu; ?> </p>
            </td>
          </tr>
      </table>
    </div>

    <table border="0.5px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;">
     <?php  foreach($degustateursByCollegeComptes as $college => $degustateurs): ?>
          <?php foreach ($degustateurs as $id_degustateur => $degustateur): ?>
            <tr>
              <td style="width:40%;">
                <small><?php echo $degustateur->nom_a_afficher ?></small><br/>
                <small><?php echo $degustateur->telephone_bureau.($degustateur->telephone_mobile ? ' / '.$degustateur->telephone_mobile : null) ?></small>
              </td>
              <td style="width:20%;"><br/><br/><small><?php echo DegustationConfiguration::getInstance()->getLibelleCollege($college) ?></small></td>
              <td style="width:20%;"></td>
              <td style="width:20%;"><small><?php echo "Présent(e)"  ?></small></td>
            </tr>
          <?php endforeach; ?>
    <?php endforeach; ?>
  </table>

  <p>Nom, prénom et signature du responsable de l'ODG : </p>
