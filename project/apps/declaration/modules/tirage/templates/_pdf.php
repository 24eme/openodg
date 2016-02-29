<?php use_helper("Date"); ?>
<?php use_helper('Parcellaire') ?>
<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<?php use_helper('Date') ?>
<style>
<?php echo styleTirage(); ?>
</style>

<span class="h3Alt">&nbsp;Déclarant&nbsp;</span><br/>
<table class="tableAlt"><tr><td>
            <table border="0">
                <tr>
                    <td style="width: 420px;">&nbsp;Nom : <i><?php echo $tirage->declarant->raison_sociale ?></i></td>
                    <td><i><?php echo $tirage->getQualite(); ?></i></td>
                </tr>
                <tr>
                    <td>&nbsp;Adresse : <i><?php echo $tirage->declarant->adresse ?></i></td>
                    <td>N° SIRET : <i><?php echo $tirage->declarant->siret ?></i></td>
                </tr>
                <tr>
                    <td>&nbsp;Commune : <i><?php echo $tirage->declarant->code_postal ?> <?php echo $tirage->declarant->commune ?></i></td>
                    <td><?php if ($tirage->declarant->cvi): ?>N° CVI : <i><?php echo $tirage->declarant->cvi ?></i><?php else: ?>&nbsp;<?php endif; ?></td>
                </tr>
                <tr>
                  <td collspan="2">&nbsp;Lieu de stockage : <i><?php echo ($tirage->lieu_stockage) ? $tirage->lieu_stockage : "même lieu que l'adresse"; ?></i></td>
                </tr>
                <tr>
                    <td>&nbsp;Tel / Fax : <i><?php echo $tirage->declarant->telephone ?> / <?php echo $tirage->declarant->fax ?></i></td>
                    <td>&nbsp;Email : <i><?php echo $tirage->declarant->email ?></i></td>
                </tr>
            </table>
        </td></tr></table>
<br />
<br />
<ol>
 <p>Déclare,</p>
  <br/>
  <p>Avoir mis en bouteille le volume suivant pour l'AOC Crémant d'Alsace&nbsp;:</p> 
    <ol>
    <table>
      <tr><td style="vertical-align: top; width: 150px;">
                              <p>Pour le lot composé de&nbsp;:</p></td>
	  <td style="text-align:left;">
      <?php foreach ($tirage->composition as $compo): ?>
      <p><?php echo $compo->nombre; ?>&nbsp;bouteilles de <?php echo $compo->contenance; ?></p>
      <?php endforeach; ?>
      </td></tr>
      <tr><td colspan="2">
      <p>Millésime&nbsp;: <?php echo $tirage->millesime_libelle; if ($tirage->millesime_ventilation) echo ' ('.$tirage->millesime_ventilation.')';?></p>
      </td></tr>
      <tr><td colspan="2">
    <p>Couleur&nbsp;:
    <?php echo $tirage->couleur_libelle; ?></p>
      </td></tr>
      <tr><td colspan="2">
    <p>Cépages entrant dans la composition de la cuvée&nbsp;:</p>
        <ol><?php $cpt = 0; foreach($tirage->cepages as $cepage) {
        if ($cepage->selectionne)  {
            if ($cpt++)
                echo ' ; ';
            echo str_replace(' ', '&nbsp;', $cepage->libelle);
        }
    } ?></ol>
      </td></tr>
      <tr><td colspan="2">
    <p>Vin de base ayant fait la fermentation malo-lactique&nbsp;:
    <?php echo ($tirage->fermentation_lactique) ? 'Oui' : 'Non' ; ?></p>
      </td></tr>
      <tr><td colspan="2">
    <p>Date de mise en bouteilles
    <?php if ($tirage->date_mise_en_bouteille_debut == $tirage->date_mise_en_bouteille_fin ) {
        echo "le ".format_date($tirage->date_mise_en_bouteille_debut, 'dd/MM/yyyy', 'fr_FR');
    }else{ 
        echo "du ".format_date($tirage->date_mise_en_bouteille_debut, 'dd/MM/yyyy', 'fr_FR');
        echo " au ".format_date($tirage->date_mise_en_bouteille_fin, 'dd/MM/yyyy', 'fr_FR');
    }?></p>
    </td></tr></table>
    </ol>
</ol>
&nbsp;<br/>
&nbsp;<br/>
&nbsp;<br/>
<p style="text-align: right">Signé électroniquement le 12/02/2016</p>

<?php for($i = 0 ; $i < 8  - count($tirage->composition); $i++) : ?>
&nbsp;<br/>
<?php endfor; ?>
      
<hr/>
<p><strong>N.B.&nbsp;:</strong> Nous vous conseillons de faire procéder à l'identification des lots par un agent de prélèvement dès la mise en bouteille de vos différents lots.</p>
