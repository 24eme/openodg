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
              <p>Date : <?php $date = date_create($degustation->date); echo $date->format("d/m/Y"); ?></p>
            </td>
            <td style="width:33%;">
              <p>Heure : <?php echo $date->format("H:i"); ?></p>
            </td>
            <td style="width:33%">
              <p>Lieu : <?php echo $degustation->lieu; ?> </p>
            </td>
          </tr>
      </table>
    </div>

    <table border="0.5px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;">
      <tr>
        <td style="width:40%;">Nom et prénom<br/>Téléphone / portable</td>
        <td style="width:20%;">Collège</td>
        <td style="width:20%;">Jury</td>
        <td style="width:20%;">Signature</td>
      </tr>
     <?php  foreach($degustateursByCollegeComptes as $college => $degustateurs): ?>

          <?php foreach ($degustateurs as $id_degustateur => $degustateur): ?>
            <?php if($degustateursATable[$id_degustateur]->confirmation): ?>
              <tr>
                <td>
                  <small><?php echo $degustateur->nom_a_afficher ?></small><br/>
                  <small><?php echo $degustateur->telephone_bureau.($degustateur->telephone_mobile ? ' / '.$degustateur->telephone_mobile : null) ?></small>
                </td>
                <td><br/><br/><small><?php echo DegustationConfiguration::getInstance()->getLibelleCollege($college) ?></small></td>
                <td></td>
                <td>
                  <small>
                    <?php if(substr($degustateur->nom_a_afficher, 0, 3) === "Mme"): ?>Présente
                    <?php else: ?>Présent
                    <?php endif; ?>
                    </small>
                  </td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
    <?php endforeach; ?>
  </table>

  <p>Nom, prénom et signature du responsable de l'ODG : </p>
