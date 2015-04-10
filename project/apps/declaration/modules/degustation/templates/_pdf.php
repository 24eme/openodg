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
        <td style="width: 304px; font-size: 7pt;"><?php echo $adresse_ava['raison_sociale']; ?><br/><?php echo $adresse_ava['adresse']; ?><br/><?php echo $adresse_ava['cp_ville']; ?><br/><?php echo $adresse_ava['telephone']; ?><br/>
    <?php echo $adresse_ava['email']; ?>
        </td>
        <td style="width: 324px; font-weight: bold;"><?php echo $degustation->raison_sociale ?><br/>
            <?php echo $degustation->adresse ?><br/>
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
        <td style="width: 304px;" >&nbsp;</td>
        <td style="width: 324px; font-weight: bold; "><?php echo 'Colmar, le ' . ucfirst(format_date(date('Y-m-d'), "P", "fr_FR")); ?>
        </td>
    </tr>
</table>
<br/>
<p>
<!--N/Réf.: <?php
            echo str_replace('-', '', $degustation->date_degustation) . '/' . $prelevement->anonymat_degustation;
            echo ($prelevement->type_courrier == DegustationClient::COURRIER_TYPE_OPE) ? '/OPE' : '';
            ?><br />-->
N° CVI : <?php echo $degustation->cvi; ?><br /><br />
Objet : Dégustation conseil <?php echo $degustation->appellation_libelle . ' millésime ' . ((int) substr($degustation->date_degustation, 0, 4) - 1); ?><br />
</p>
<p>Madame, Monsieur,</p>
<br/>
<p style="text-align: justify;">Vous avez présenté un échantillon d'<strong><?php echo $degustation->appellation_libelle . ' ' . $prelevement->libelle; ?></strong> à une dégustation conseil organisée par l'ODG-AVA. Celle-ci a eu lieu le <strong><?php echo ucfirst(format_date($degustation->date_degustation, "P", "fr_FR")); ?></strong>.</p>
<p>Les experts dégustateurs ont fait les commentaires suivants sur votre vin : </p>

<div><span class="h3">&nbsp;Rapport de notes&nbsp;</span></div>

<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: left; width: 250px; font-weight: bold;">&nbsp;Produit</th>    
        <th class="th" style="text-align: center; width: 70px; font-weight: bold;">Lot N°</th>  
        <th class="th" style="text-align: center; width: 150px; font-weight: bold;">Cuve</th>  
        <th class="th" style="text-align: center; width: 150px; font-weight: bold;">N° de Prélèvement</th>
    </tr>
    <tr>
        <td class="td" style="text-align:left; font-weight: bold;"><?php echo tdStart() ?>&nbsp;<?php echo $prelevement->libelle; ?></td>
        <td class="td" style="text-align:center; font-weight: bold;"><?php echo tdStart() ?>&nbsp;<?php echo $prelevement->getKey() + 1; ?></td>        
        <td class="td" style="text-align:center; font-weight: bold;"><?php echo tdStart() ?>&nbsp;<?php echo $prelevement->cuve; ?></td>        
        <td class="td" style="text-align:center; font-weight: bold;"><?php echo tdStart() ?>&nbsp;<?php echo $prelevement->anonymat_prelevement_complet; ?></td>

    </tr>    
    <?php foreach ($prelevement->notes as $type_note => $note): ?>
        <tr>
            <td class="td" style="text-align:center; border-bottom: 1px solid #fff;"><?php echo tdStart() ?>&nbsp;<?php echo getLibelleTypeNote($type_note) ?></td>
            <td class="td" style="text-align:center; font-weight: bold; border-bottom: 1px solid #fff;" colspan="3"><?php echo tdStart() ?>&nbsp;<?php echo $note->note ?></td>        
        </tr>
        <?php
        $defaults = "";
        $cpt = 0;
        foreach ($note->defauts as $default) {
            $defaults.=$default;
            if ($cpt < count($note->defauts) - 1) {
                $defaults.=", ";
            }
            $cpt++;
        }
        ?>
        <tr>
            <td class="td" style="text-align:center; border-top: 1px solid #fff;"><?php echo tdStart() ?>&nbsp;Remarque(s)</td>
            <td class="td" style="text-align:center; border-top: 1px solid #fff;" colspan="3"><?php echo tdStart() ?>&nbsp;<?php if($defaults): ?><?php echo $defaults ?><?php else: ?><i><small>Aucune</small></i><?php endif; ?></td>

        </tr>
    <?php endforeach; ?>
    <?php if($prelevement->appreciations): ?>
    <tr>
        <td class="td" style="text-align:center; border-top: 1px solid #fff;"><?php echo tdStart() ?>&nbsp;Appréciation(s)</td>
        <td class="td" style="text-align:center; border-top: 1px solid #fff;" colspan="3"><?php echo tdStart() ?>&nbsp;<?php if($prelevement->appreciations): ?><?php echo $prelevement->appreciations ?><?php else: ?><i><small>Aucune</small></i><?php endif; ?></td>

    </tr>
    <?php endif; ?>
</table>
<p></p>
<?php echo getExplicationsPDF($prelevement); ?>
<p></p>
<p style="text-align: justify;">A votre disposition pour tout complément d'information, nous vous prions d'agréer, Madame, Monsieur, nos plus cordiales salutations.</p>
<br/>
<p style="text-align: right;"><strong><?php echo sfConfig::get('app_degustation_courrier_responsable'); ?><br />Responsable de l'Appui Technique de l'AVA</strong></p>
<p></p>
<br/>
<p style="font-weight: normal; font-size: 8pt;">Rappel du barème des notes :</p>
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
            <th class="th" style="text-align: left; width: 140px; height:16px; font-weight: bold;"><span style="font-size: 1pt"><br /></span><small>&nbsp;<?php echo $noteLibelle; ?></small></th>    
            <td class="td" style="text-align: left; width: 490px; height:16px; font-size: 9pt;"><span style="font-size: 1pt"><br /></span><small>&nbsp;<?php echo $notesDesc; ?></small></td>
        </tr>            
    <?php endforeach; ?>
</table>