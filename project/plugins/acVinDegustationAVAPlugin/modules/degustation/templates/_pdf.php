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
        <td style="width: 304px; font-size: 7pt;"><!--<?php echo $adresse_ava['raison_sociale']; ?><br/><?php echo $adresse_ava['adresse']; ?><br/><?php echo $adresse_ava['cp_ville']; ?><br/><?php echo $adresse_ava['telephone']; ?><br/>
    <?php echo $adresse_ava['email']; ?>-->
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
        <td style="width: 324px; font-weight: bold; "><?php echo 'Colmar, le ' . format_date(date('Y-m-d'), "P", "fr_FR"); ?>
        </td>
    </tr>
</table>
<p>
N° CVI : <?php echo $degustation->cvi; ?><br /><br />
Objet : Dégustation conseil <?php echo str_replace(" ".$degustation->getMillesime(), "", $degustation->libelle) . ' millésime ' . $degustation->getMillesime() ?><br />
</p>
<p>Madame, Monsieur,</p>
<br/>
<p style="text-align: justify;">Vous avez présenté un échantillon à la dégustation conseil suivante :</p>

<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: left; width: 200px; font-weight: bold;"><?php echo tdStart() ?>&nbsp;Date</th>
        <td class="td" style="text-align: left; width: 420px;"><?php echo tdStart() ?>&nbsp;<?php echo ucfirst(format_date($degustation->date_degustation, "P", "fr_FR")); ?></td>
    </tr>
    <tr>
        <th class="th" style="text-align: left; width: 200px; font-weight: bold;"><?php echo tdStart() ?>&nbsp;Produit</th>
        <td class="td" style="text-align: left; width: 420px;"><?php echo tdStart() ?>&nbsp;<?php echo $prelevement->getLibelleComplet(); ?><?php if($prelevement->exist('fermentation_lactique') || $prelevement->exist('composition_cepages')): ?><br />&nbsp;<?php endif; ?><?php if($prelevement->exist('fermentation_lactique')): ?><small>Malo-lactique</small><?php endif; ?><?php if($prelevement->exist('composition_cepages')): ?><small><?php if($prelevement->exist('fermentation_lactique')): ?> - <?php endif; ?><?php echo $prelevement->get('composition_cepages'); ?></small><?php endif; ?></td>
    </tr>

    <tr>
        <th class="th" style="text-align: left; width: 200px; font-weight: bold;"><?php echo tdStart() ?>&nbsp;Organisateur</th>
        <td class="td" style="text-align: left; width: 420px;"><?php echo tdStart() ?>&nbsp;<?php echo $degustation->getOrganisme() ?></td>
    </tr>
    <tr>
        <th class="th" style="text-align: left; width: 200px; font-weight: bold;"><?php echo tdStart() ?>&nbsp;Identification du lot</th>
        <td class="td" style="text-align: left; width: 420px;"><?php echo tdStart() ?>&nbsp;Lot n° <?php echo $prelevement->getKey() + 1; ?> / Échantillon n° <?php echo ($prelevement->anonymat_prelevement_complet) ? $prelevement->anonymat_prelevement_complet : $prelevement->anonymat_degustation; ?><?php if($prelevement->denomination_complementaire): ?> / <?php echo $prelevement->denomination_complementaire; ?><?php endif; ?></td>
    </tr>
    <tr>
        <th class="th" style="text-align: left; width: 200px; font-weight: bold;"><?php echo tdStart() ?>&nbsp;Cuve / Volume</th>
        <td class="td" style="text-align: left; width: 420px;"><?php echo tdStart() ?>&nbsp;<?php if($prelevement->getCuveNettoye()): ?><?php echo $prelevement->getCuveNettoye(); ?> <?php endif; ?><?php if($prelevement->getCuveNettoye() && $prelevement->volume_revendique): ?>/ <?php endif; ?><?php if($prelevement->volume_revendique): ?><?php echoFloat($prelevement->volume_revendique) ?> <small>hl</small><?php endif; ?><?php if(!$prelevement->getCuveNettoye() && !$prelevement->volume_revendique): ?><i>Non défini</i><?php endif; ?></td>
    </tr>
</table>

<p>Les experts dégustateurs ont fait les commentaires suivants sur votre vin : </p>

<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <?php foreach ($prelevement->notes as $type_note => $note): ?>
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
            <th class="th" style="text-align: left; width: 200px; font-weight: bold; height: 38px;"><?php echo tdStart() ?>&nbsp;<?php echo getLibelleTypeNote($type_note) ?><br />&nbsp;<span style="font-weight: normal;">Remarque(s)</span></th>
            <td class="td" style="text-align: left; font-weight: bold; width: 420px; height: 38px;"><?php echo tdStart() ?>&nbsp;<?php if($note->note == "X"): ?><span style="font-weight: normal">Échantillon non dégusté</span><?php else: ?><?php echo $note->note ?><span style="font-weight: normal"> - <?php echo $note->getLibelle() ?></span><?php endif; ?><br />&nbsp;<span style="font-weight: normal;"><?php if($defaults): ?><?php echo $defaults ?><?php else: ?><i>Aucune</i><?php endif; ?></span></td>
        </tr>
    <?php endforeach; ?>
    <?php if($prelevement->appreciations): ?>
    <tr>
        <th class="th" style="text-align:left; font-weight: bold;"><?php echo tdStart() ?>&nbsp;Appréciation(s)</th>
        <td class="td" style="text-align:left;"><?php echo tdStart() ?>&nbsp;<?php if($prelevement->appreciations): ?><?php echo $prelevement->appreciations ?><?php else: ?><i>Aucune</i><?php endif; ?></td>

    </tr>
    <?php endif; ?>
</table>
<?php echo getExplicationsPDF($prelevement); ?>
<p style="text-align: justify;">A votre disposition pour tout complément d'information, nous vous prions d'agréer, Madame, Monsieur, nos plus cordiales salutations.</p>
<br/>
<p style="text-align: justify;">L'équipe de L'Appui Technique AVA-ODG</p>
<p></p>
<br/>
<p style="font-weight: normal; font-size: 8pt;">Rappel du barème des notes :</p>
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <?php
    foreach (DegustationClient::getInstance()->getNotesTypeByAppellation($degustation->appellation) as $noteType => $noteLibelle):
        $notesDesc = "";
        foreach (DegustationClient::$note_type_notes[$noteType] as $noteDesc):
            $notesDesc.=$noteDesc . ' / ';
        endforeach;
        $notesDesc = substr($notesDesc, 0, strlen($notesDesc) - 2);
        ?>
        <tr>
            <th class="th" style="text-align: left; width: 200px; height:16px; font-weight: bold;"><span style="font-size: 1pt"><br /></span><small>&nbsp;<?php echo $noteLibelle; ?></small></th>
            <td class="td" style="text-align: left; width: 420px; height:16px; font-size: 9pt;"><span style="font-size: 1pt"><br /></span><small>&nbsp;<?php echo $notesDesc; ?></small></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php if($degustation->appellation == "VTSGN"): ?>
<p style="text-align: justify; font-size: 9pt;">N.B. : Les commentaires et appréciations des experts dégustateurs concernent <strong>uniquement la mention VT ou SGN</strong> et ne portent pas sur l'appellation Alsace ou Alsace Grand cru.</p>
<?php endif; ?>
