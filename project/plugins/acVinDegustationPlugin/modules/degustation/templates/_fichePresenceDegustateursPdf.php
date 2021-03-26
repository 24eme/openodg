<?php use_helper('Date'); ?>
<?php use_helper('TemplatingPDF'); ?>

<style>
<?php echo style(); ?>
.bg-white{
  background-color:white;
}
</style>

    <div>
      <table>
        <tbody>
          <tr>
            <td>
              <p>Code Commission: <?= $degustation->_id ?></p>
            </td>
            <td>
              <p>Responsable :</p>
            </td>
          </tr>

          <tr>
            <td>
                <p>Date : <?php echo format_datetime($degustation->date, "P", "fr_FR") ?></p>
            </td>
            <td>
              <p>Lieu : <?php echo $degustation->lieu; ?> </p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <table border="0.5px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;">
      <thead>
      <tr>
        <th style="width:60%;">Nom et prénom<br/>Téléphone / portable</th>
        <th style="width:20%;">Collège</th>
        <th style="width:20%;">Signature</th>
      </tr>
      </thead>
      <tbody>
        <?php foreach ($degustateurs as $college => $degustateurs_college): ?>
          <?php foreach ($degustateurs_college as $degustateur): ?>
          <?php if ($degustation->degustateurs->{$college}->{$degustateur->_id}->exist('confirmation') && $degustation->degustateurs->{$college}->{$degustateur->_id}->confirmation): ?>
            <tr>
                <td style="width: 60%">
                    <?php echo tdStart() ?>
                    <?php echo $degustateur->nom_a_afficher ?> <small><?php echo $degustateur->telephone_bureau; echo ($degustateur->telephone_bureau && $degustateur->telephone_mobile) ? ' / ' : ''; echo $degustateur->telephone_mobile ?></small>
                </td>
                <td style="width: 20%"><?= DegustationConfiguration::getInstance()->getLibelleCollege($college) ?></td>
                <td style="width: 20%"></td>
            </tr>
          <?php endif ?>
          <?php endforeach ?>
        <?php endforeach ?>
      </tbody>
    </table>

  <p>Nom, prénom et signature du responsable de l'ODG : </p>
