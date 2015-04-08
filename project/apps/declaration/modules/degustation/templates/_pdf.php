<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>
<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<?php $adresse_ava = sfConfig::get('app_degustation_courrier_adresse'); ?>
<style>
<?php echo styleDegustation(); ?>
</style>
<br/>
<br/>
<br/>
<br/>
<table border="0">
    <tr>
        <td style="width: 300px;" >&nbsp;</td>
        <td style="width: 400px; padding-right: 40px; font-weight: bolder; ">
            <?php echo $degustation->raison_sociale ?>
        </td>
    </tr>
    <tr>
        <td style="width: 300px;" >&nbsp;</td>
        <td style="width: 400px; padding-right: 40px; font-weight: bolder; ">
            <?php echo $degustation->adresse; ?>
        </td>
    </tr>
    <tr>
        <td style="width: 300px;" >&nbsp;</td>
        <td style="width: 400px; padding-right: 40px; font-weight: bolder; ">
            <?php echo $degustation->code_postal . ' ' . $degustation->commune; ?>
        </td>
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
            <?php echo 'Colmar, le ' . ucfirst(format_date(date('Y-m-d'), "P", "fr_FR")); ?>
        </td>
    </tr>
</table>

<br/>
<br/>
<br/>

<table>
    <tr>
        <td>N/Réf.: <?php
            echo str_replace('-', '', $degustation->date_degustation) . '/' . $prelevement->anonymat_degustation;
            echo ($prelevement->type_courrier == DegustationClient::COURRIER_TYPE_OPE) ? '/OPE' : '';
            ?></td>
    </tr>
    <tr>
        <td>N° CVI : <?php echo $degustation->cvi; ?></td>
    </tr>
    <tr>
        <td>Cuve : <?php echo $prelevement->cuve; ?></td>
    </tr>
    <tr>
        <td>Objet : Dégustation conseil <?php echo $degustation->appellation_libelle . ' millésime ' . ((int) substr($degustation->date_degustation, 0, 4) - 1); ?></td>
    </tr>
</table>
<br/>
<br/>
<p>Madame, Monsieur,</p>
<br/>
<p>Vous avez présenté un échantillon d'<strong><?php echo $degustation->appellation_libelle . ' ' . $prelevement->libelle; ?></strong> à une dégustation conseil organisée par l'ODG-AVA. Celle-ci a eu lieu le <strong><?php echo ucfirst(format_date($degustation->date_degustation, "P", "fr_FR")); ?></strong>.</p>
<p>Les experts dégustateurs ont fait les commentaires suivants  sur votre vin : </p>

<div><span class="h3">&nbsp;Rapport de notes&nbsp;</span></div>
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: left; width: 350px; font-weight: bold;">&nbsp;Produit</th>    
        <th class="th" style="text-align: center; width: 140px; font-weight: bold;">Lot N°</th>  
        <th class="th" style="text-align: center; width: 140px; font-weight: bold;">N° de pélèvement</th>
    </tr>
    <tr>
        <td class="td" style="text-align:left; font-weight: bold;"><?php echo tdStart() ?>&nbsp;<?php echo $prelevement->libelle; ?></td>
        <td class="td" style="text-align:center; font-weight: bold;"><?php echo tdStart() ?>&nbsp;<?php echo $prelevement->getKey() + 1; ?></td>        
        <td class="td" style="text-align:center; font-weight: bold;"><?php echo tdStart() ?>&nbsp;<?php echo $prelevement->anonymat_prelevement; ?></td>

    </tr>    
    <?php foreach ($prelevement->notes as $type_note => $note): ?>
        <tr>
            <td class="td" style="text-align:center;"><?php echo tdStart() ?>&nbsp;<?php echo getLibelleTypeNote($type_note) ?></td>
            <td class="td" style="text-align:center; font-weight: bold;" colspan="2"><?php echo tdStart() ?>&nbsp;<?php echo $note->note ?></td>        
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
            <td class="td" style="text-align:center;"><?php echo tdStart() ?>&nbsp;Remarque (s) :</td>
            <td class="td" style="text-align:center; font-weight: bold;" colspan="2"><?php echo tdStart() ?>&nbsp;<?php echo $defaults ?></td>

        </tr>
    <?php endforeach; ?>
</table>
<br/>
<?php echo getExplicationsPDF($prelevement); ?>
<br/>
<p>A votre disposition pour tout complément d'information, nous vous prions d'agréer, Madame, Monsieur, nos plus cordiales salutations.</p>

<br/>
<p style="width: 350px; font-size: 10pt; font-weight: bold; font-style: italic; text-align: right;"><?php echo sfConfig::get('app_degustation_courrier_responsable'); ?>, Responsable de l'Appui Technique de l'Ava</p>
<br/>
<p style="width: 350px; font-weight: bold; font-style: italic">Rappel du barème des notes :</p>

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

<br/>
<br/>
<small style=""><?php echo $adresse_ava['raison_sociale']; ?><br/>
    <?php echo $adresse_ava['adresse']; ?><br/>
    <?php echo $adresse_ava['cp_ville']; ?><br/>
    <?php echo $adresse_ava['telephone']; ?><br/>
    <?php echo $adresse_ava['email']; ?>
</small>