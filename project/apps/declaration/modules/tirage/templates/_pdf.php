<?php use_helper("Date"); ?>
<?php use_helper('Parcellaire') ?>
<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<?php use_helper('Date') ?>
<style>
<?php echo styleTirage(); ?>
</style>

<span class="h3Alt">&nbsp;Exploitation&nbsp;</span><br/>
<table class="tableAlt"><tr><td>
            <table border="0">
                <tr>
                    <td style="width: 420px;">&nbsp;Nom : <i><?php echo $tirage->declarant->raison_sociale ?></i></td>
                    <td><?php echo $tirage->getDeclarantQualite(); ?></td>
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
                    <td>&nbsp;Lieu de stockage : <i><?php echo $tirage->declarant->code_postal ?> <?php echo $tirage->declarant->commune ?></i></td>
                    <td></td>
                </tr>
                <tr>
                    <td>&nbsp;Tel / Fax : <i><?php echo $tirage->declarant->telephone ?> / <?php echo $tirage->declarant->fax ?></i></td>
                    <td></td>
                </tr>
                <tr>
                    <td>&nbsp;Email : <i><?php echo $tirage->declarant->email ?></i></td>
                    <td></td>
                </tr>
            </table>
        </td></tr></table>
<br />
<br />
<br />
Déclare,
<ol>
  <li><strong>Avoir mis en bouteilles le volume suivant pour l'AOC Crémant d'Alsace&nbsp;:</strong><br/>
    <br/>
    <table>
      <tr><td style="vertical-align: top; width: 150px;">Pour le lot composé de&nbsp;:</td>
	<td style="text-align:left;">
	  <table>
      <?php foreach ($tirage->composition as $compo): ?>
      <tr><td class="border"><?php echo $compo->nombre; ?> &nbsp; </td><td> &nbsp; bouteilles de <?php echo $compo->contenance; ?>&nbsp;cl.</td></tr>
      <?php endforeach; ?>
	  </table>
      </td></tr>
    </table>
    <br/>
    <br/>
    
    <strong>Millesime&nbsp;:</strong> <?php echo $tirage->millesime_libelle; if ($tirage->millesime_ventilation) echo ' ('.$tirage->millesime_ventilation.')';?>
    <br/><br/>
    
    <strong>Couleur&nbsp;:</strong>
    <?php echoCheck("Blanc", ($tirage->couleur == TirageClient::COULEUR_BLANC)); echoCheck("Rosé", ($tirage->couleur == TirageClient::COULEUR_ROSE)); ?>
    <br/><br/>
    
    Cépages entrant dans la composition de la cuvée&nbsp;:
    <p style="margin-left: 40px;">
    <?php foreach($tirage->cepages as $cepage) {
    echoCheck(str_replace(' ', '&nbsp;', $cepage->libelle), $cepage->selectionne);
    } ?></p>
    <br/><br/>
    
    Vin de base ayant fait la fermantation mal-lactique&nbsp;:
    <?php echoCheck("Oui", ($tirage->fermentation_lactique)) ?>
    <?php echoCheck("Non", !($tirage->fermentation_lactique)) ?>
    <br/><br/>

    <strong>Date de mis en bouteilles&nbsp;:</strong>
    <?php if ($tirage->date_mise_en_bouteille_debut == $tirage->date_mise_en_bouteille_fin ) {
        echo "le ".format_date($tirage->date_mise_en_bouteille_debut, 'dd/MM/yyyy', 'fr_FR');
    }else{ 
        echo "du ".format_date($tirage->date_mise_en_bouteille_debut, 'dd/MM/yyyy', 'fr_FR');
        echo " au ".format_date($tirage->date_mise_en_bouteille_fin, 'dd/MM/yyyy', 'fr_FR');
    }?>
    <br/><br/>

  </li>
  <li><strong>Joindre une copie lisible de la déclaration de résolte.</strong>
    <p>Dans le cas d'une cave coopérative ou d'un négociant, joindre une copie du certificat de fabrication visé par les douanes ou une copie de la DRM visée par les Douanes.</p>
    </li>
</ol>
&nbsp;
<p style="text-align: right">Signé électroniquement le 12/02/2016</p>

<?php for($i = 0 ; $i < 16  - count($tirage->composition); $i++) : ?>
&nbsp;<br/>
<?php endfor; ?>
      
<hr/>
<p><strong>N.B.&nbsp;:</strong> Nous vous conseillons de faire procéder à l'identification des lots par un agent de prélèvement dès la mise en bouteille de vos différents lots.</p>
