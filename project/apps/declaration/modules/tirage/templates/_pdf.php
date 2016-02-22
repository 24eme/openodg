<?php use_helper("Date"); ?>
<?php use_helper('Parcellaire') ?>
<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<style>
<?php echo styleTirage(); ?>
</style>

<span class="h3Alt">&nbsp;Exploitation&nbsp;</span><br/>
<table class="tableAlt"><tr><td>
            <table border="0">
                <tr>
                    <td style="width: 420px;">&nbsp;Nom : <i><?php echo $tirage->declarant->raison_sociale ?></i></td>

                    <td><?php if ($tirage->declarant->cvi): ?>N° CVI : <i><?php echo $tirage->declarant->cvi ?></i><?php else: ?>&nbsp;<?php endif; ?></td>
                </tr>
                <tr>
                    <td>&nbsp;Adresse : <i><?php echo $tirage->declarant->adresse ?></i></td>
                    <td>N° SIRET : <i><?php echo $tirage->declarant->siret ?></i></td>
                </tr>
                <tr>
                    <td>&nbsp;Commune : <i><?php echo $tirage->declarant->code_postal ?>, <?php echo $tirage->declarant->commune ?></i></td>
                    <td></td>
                </tr>
                <tr>
                    <td>&nbsp;Lieu de stockage : <i><?php echo $tirage->declarant->code_postal ?>, <?php echo $tirage->declarant->commune ?></i></td>
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
        </td></tr><tr><td>
<hr/>
Qualité du Déclarant : 
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
	  <tr><td class="border">10 &nbsp; </td><td> &nbsp; bouteilles de 31,5&nbsp;cl.</td></tr>
	  </table>
      </td></tr>
    </table>
    <br/>
    <br/>
    
    <strong>Millesime&nbsp;:</strong> 2015
    <br/><br/>
    
    <strong>Couleur&nbsp;:</strong>
    <span style="font-family: Dejavusans">☒</span>&nbsp;Blanc &nbsp;
    <span style="font-family: Dejavusans">☐</span>&nbsp;Rosé
    <br/><br/>
    
    Cépages entrant dans la composition de la cuvée&nbsp;:
    <span style="font-family: Dejavusans">☒</span>&nbsp;PB
    <span style="font-family: Dejavusans">☐</span>&nbsp;AUX
    <span style="font-family: Dejavusans">☐</span>&nbsp;CH
    <span style="font-family: Dejavusans">☐</span>&nbsp;RI
    <span style="font-family: Dejavusans">☐</span>&nbsp;PG
    <span style="font-family: Dejavusans">☐</span>&nbsp;PN
    <br/><br/>
    
    Vin de base ayant fait la fermantation mal-lactique&nbsp;:
    <span style="font-family: Dejavusans">☒</span>&nbsp;Oui
    &nbsp;
    <span style="font-family: Dejavusans">☐</span>&nbsp;Non
    <br/><br/>

    <strong>Date de mis en bouteilles&nbsp;:</strong>
    du 11/12/2015 au 11/01/2016
    <br/><br/>

  </li>
  <li><strong>Joindre une copie lisible de la déclaration de résolte.</strong>
    <p>Dans le cas d'une cave coopérative ou d'un négociant, joindre une copie du certificat de fabrication visé par les douanes ou une copie de la DRM visée par les Douanes.</p>
    </li>
</ol>
&nbsp;
<p style="text-align: right">Signé électroniquement le 12/02/2016</p>

<?php for($i = 0 ; $i < 18 ; $i++) : ?>
&nbsp;<br/>
<?php endfor; ?>
      
<hr/>
<p><strong>N.B.&nbsp;:</strong> Nous vous conseillons de faire procéder à l'identification des lots par un agent de prélèvement dès la mise en bouteille de vos différents lots.</p>
