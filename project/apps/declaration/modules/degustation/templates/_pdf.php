<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>
<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<style>
<?php echo styleDegustation(); ?>
</style>
<br/>
<br/>
<br/>
<br/>
<table border="0">
    <tr>
        <td style="width: 350px;" >&nbsp;</td>
        <td style="width: 200px; padding-right: 40px; font-weight: bolder; ">
            <?php echo $operateur->raison_sociale ?>
        </td>
        <td style="width: 150px;">&nbsp;</td>
    </tr>
    <tr>
        <td style="width: 350px;" >&nbsp;</td>
        <td style="width: 200px; padding-right: 40px; font-weight: bolder; ">
            <?php echo $operateur->adresse; ?>
        </td>
        <td style="width: 150px;">&nbsp;</td>
    </tr>
    <tr>
        <td style="width: 350px;" >&nbsp;</td>
        <td style="width: 200px; padding-right: 40px; font-weight: bolder; ">
            <?php echo $operateur->code_postal . ' ' . $operateur->commune; ?>
        </td>
        <td style="width: 150px; ">&nbsp;</td>
    </tr>
</table>
<br/>
<br/>
<br/>
<br/>
<table>
    <tr>
        <td style="width: 350px;" >&nbsp;</td>
        <td style="width: 350px; padding-right: 40px; font-weight: bolder; ">            
            <?php echo $degustation->lieu . ', le ' . ucfirst(format_date(date('Y-m-d'), "P", "fr_FR")); ?>
        </td>
    </tr>
</table>

<br/>
<br/>
<br/>

<table>
    <tr>
        <td>N/Réf.: <?php
            echo str_replace('-', '', $degustation->date) . '/' . $prelevement->anonymat_degustation;
            echo ($prelevement->type_courrier == DegustationClient::COURRIER_TYPE_OPE) ? '/OPE' : '';
            ?></td>
    </tr>
    <tr>
        <td>Clé Identité : <?php echo $operateur->cvi; ?></td>
    </tr>
    <tr>
        <td>Cuve : <?php echo $prelevement->cuve; ?></td>
    </tr>
    <tr>
        <td>Objet : Gestion locale : dégustation conseil <?php echo $degustation->appellation_libelle . ' ' . substr($degustation->validation, 0, 4); ?></td>
    </tr>
</table>
<br/>
<br/>
<p>Madame, Monsieur,</p>
<br/>
<p>Vous avez présenter vos vins à la degustation conseil de la Gestion locale du <strong><?php echo $degustation->appellation_libelle . ' ' . $prelevement->libelle; ?></strong>. Celle-ci a eu lieu le <strong><?php echo ucfirst(format_date($operateur->date, "P", "fr_FR")); ?></strong>.</p>
<p>Les experts dégustateurs ont fait les commentaires suivants  sur votre vin : </p>

<div><span class="h3">&nbsp;Rapport de notes&nbsp;</span></div>
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: left; width: 350px; font-weight: bold;">&nbsp;Produit</th>    
        <th class="th" style="text-align: center; width: 140px; font-weight: bold;">Lot N°</th>  
        <th class="th" style="text-align: center; width: 140px; font-weight: bold;">N° Ech</th>
    </tr>
    <tr>
        <td class="td" style="text-align:left; font-weight: bold;"><?php echo tdStart() ?>&nbsp;<?php echo $prelevement->libelle; ?></td>
        <td class="td" style="text-align:center; font-weight: bold;"><?php echo tdStart() ?>&nbsp;<?php echo $prelevement->getKey() + 1; ?></td>        
        <td class="td" style="text-align:center; font-weight: bold;"><?php echo tdStart() ?>&nbsp;<?php echo $prelevement->anonymat_degustation; ?></td>

    </tr>    
    <?php foreach ($prelevement->notes as $type_note => $note): ?>
        <tr>
            <td class="td" style="text-align:left;"><?php echo tdStart() ?>&nbsp;<?php echo getLibelleTypeNote($type_note) ?></td>
            <td class="td" style="text-align:left; font-weight: bold;" colspan="2"><?php echo tdStart() ?>&nbsp;<?php echo $note->note ?></td>        
        </tr>
        <?php
        $defaults = "";
        $cpt = 0;
        foreach ($note->defauts as $default) {
            $defaults.=$default;
            if ($cpt < count($note->defauts) - 1) {
                $defaults.=",";
            }
            $cpt++;
        }
        ?>
        <tr>
            <td class="td" style="text-align:left;"><?php echo tdStart() ?>&nbsp;Remarque (s) :</td>
            <td class="td" style="text-align:left; font-weight: bold;" colspan="2"><?php echo tdStart() ?>&nbsp;<?php echo $defaults ?></td>

        </tr>
    <?php endforeach; ?>
</table>
<br/>
<?php echo getExplicationsPDF($prelevement); ?>
<br/>
<p>A votre disposition pour tout complément d'information, nous vous prions d'agréer, Madame, Monsieur, nos cordiales salutations.</p>

<br/>
<p style="width: 350px; font-size: 11pt; font-weight: bold; font-style: italic; text-align: right;">L'Appui Technique de l'Ava</p>
<br/>
<br/>
<p style="width: 350px; font-weight: bold; font-style: italic">Rappel du barème des notes :</p>
<br/>
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <?php
    foreach (DegustationClient::$note_type_libelles as $noteType => $noteLibelle):
        $notesDesc = "";
        foreach (DegustationClient::$note_type_notes[$noteType] as $noteDesc):
            $notesDesc.=$noteDesc . ' / ';
        endforeach;
        $notesDesc = substr($notesDesc, 0, strlen($notesDesc) - 2);
        ?>
        <tr>
            <th class="th" style="text-align: left; width: 140px; font-weight: bold;"><small>&nbsp;<?php echo $noteLibelle; ?></small></th>    
            <td class="td" style="text-align: left; width: 490px; font-size: 9pt;"><small>&nbsp;<?php echo $notesDesc; ?></small></td>

        </tr>            
    <?php endforeach; ?>
</table>
