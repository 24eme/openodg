<?php use_helper('Date'); ?>
<?php use_helper('TemplatingPDF'); ?>

<style>
<?php echo style(); ?>
.bg-white{
  background-color:white;
}
</style>
        <table>
        <tbody>
          <tr>
            <td>Commission: <?= $degustation->_id ?></td>
            <td>Date : <?php echo format_datetime($degustation->date, "P", "fr_FR") ?></td>
          </tr>

          <tr>
            <td colspan="2"><?php echo tdStart() ?> Lieu : <?php echo $degustation->lieu; ?></td>
          </tr>
        </tbody>
      </table>
    <div></div>
    <table border="0.5px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;">
      <thead>
      <tr>
        <th style="width:50%;">Nom et prénom<br/>Téléphones</th>
        <th style="width:20%;">Collège</th>
        <th style="width:10%;">Table</th>
        <th style="width:20%;">Signature</th>
      </tr>
      </thead>
      <tbody>
        <?php foreach ($degustateurs as $degustateur): ?>
          <?php if ($degustateur['confirme']): ?>
            <tr>
                <td style="width: 50%">
                    <?php echo $degustateur['degustateur']->nom_a_afficher ?><br/>
                    <?php echo join(' - ', array_filter([$degustateur['degustateur']->telephone_bureau, $degustateur['degustateur']->telephone_mobile, $degustateur['degustateur']->telephone_perso])); ?>
                </td>
                <td style="width: 20%"><?= DegustationConfiguration::getInstance()->getLibelleCollege($degustateur['college']) ?></td>
                <td style="width: 10%"></td>
                <td style="width: 20%"></td>
            </tr>
          <?php endif ?>
        <?php endforeach ?>
      </tbody>
    </table>

  <p>Nom, prénom et signature du responsable de l'ODG : </p>
