<?php use_helper("Date"); ?>
<?php use_helper('ParcellaireAffectation') ?>
<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<?php use_helper('Date') ?>
<?php use_helper('Compte') ?>
<style>
<?php echo styleTirage(); ?>
</style>

<span class="h3Alt">&nbsp;Exploitation&nbsp;</span><br/>
<table class="tableAlt"><tr><td>
            <table border="0">
                <tr>
                    <td style="width: 420px;">&nbsp;Nom : <i><?php echo $tirage->declarant->raison_sociale ?></i></td>
                    <td><i><?php echo $tirage->getQualite(); ?></i></td>
                </tr>
                <tr>
                    <td>&nbsp;Adresse : <i><?php echo $tirage->declarant->adresse ?></i></td>
                    <td>N° SIRET : <i><?php echo formatSIRET($tirage->declarant->siret); ?></i></td>
                </tr>
                <tr>
                    <td>&nbsp;Commune : <i><?php echo $tirage->declarant->code_postal ?> <?php echo $tirage->declarant->commune ?></i></td>
                    <td><?php if ($tirage->declarant->cvi): ?>N° CVI : <i><?php echo $tirage->declarant->cvi ?></i><?php else: ?>&nbsp;<?php endif; ?></td>
                </tr>
                <tr>
                  <td collspan="2">&nbsp;Lieu de stockage : <i><?php echo ($tirage->lieu_stockage) ? $tirage->lieu_stockage : "même adresse"; ?></i></td>
                </tr>
                <tr>
                    <td>&nbsp;Tel / Fax : <i><?php echo $tirage->declarant->telephone ?> / <?php echo $tirage->declarant->fax ?></i></td>
                    <td>&nbsp;Email : <i><?php echo $tirage->declarant->email ?></i></td>
                </tr>
            </table>
        </td></tr></table>
&nbsp;<br/>
&nbsp;<br/>
&nbsp;<br/>
&nbsp;<br/>
<span class="h3Alt">&nbsp;Caractéristiques du lot&nbsp;</span><br/>
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
                <tr>
                    <td><?php echo tdStart() ?>&nbsp;Couleur</td>
                    <td><?php echo tdStart() ?>&nbsp;<?php echo $tirage->couleur_libelle; ?></td>
                </tr>
                <tr>
                    <td><?php echo tdStart() ?>&nbsp;Cépage</td>
                    <td><?php echo tdStart() ?>&nbsp;<?php $cpt = 0; foreach($tirage->cepages as $cepage) {
        if ($cepage->selectionne)  {
            if ($cpt++)
                echo ' ; ';
            echo str_replace(' ', '&nbsp;', $cepage->libelle);
        }
    } ?></td>
                </tr>
                <tr>
                    <td><?php echo tdStart() ?>&nbsp;Millésime</td>
                    <td><?php echo tdStart() ?>&nbsp;<?php echo $tirage->millesime_libelle; if ($tirage->millesime_ventilation) echo ' ('.$tirage->millesime_ventilation.')'; ?></td>
                </tr>
                <tr>
                    <td><?php echo tdStart() ?>&nbsp;Fermentation Malo-lactique</td>
                    <td><?php echo tdStart() ?>&nbsp;<?php  echo ($tirage->fermentation_lactique) ? 'Oui' : 'Non' ; ?></td>
                </tr>
            </table>
&nbsp;<br/>
&nbsp;<br/>
&nbsp;<br/>
&nbsp;<br/>
<span class="h3Alt">&nbsp;Répartition du volume&nbsp;</span><br/>
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <td class="td"><?php echo tdStart() ?>&nbsp;Date de mise en bouteilles</td>
        <td class="td"><?php echo tdStart() ?>&nbsp;<?php if ($tirage->date_mise_en_bouteille_debut == $tirage->date_mise_en_bouteille_fin ) {
    echo "le ".format_date($tirage->date_mise_en_bouteille_debut, 'dd/MM/yyyy', 'fr_FR');
    }else{
    echo "du ".format_date($tirage->date_mise_en_bouteille_debut, 'dd/MM/yyyy', 'fr_FR');
    echo " au ".format_date($tirage->date_mise_en_bouteille_fin, 'dd/MM/yyyy', 'fr_FR');
    }?></td>
    </tr>
    <tr>
        <td style="vertical-align: top;"><?php echo tdStart() ?>&nbsp;Détail de la composition du lot</td>
    <td><?php echo tdStart() ?>&nbsp;<?php  $nbcompo = 0 ; foreach ($tirage->composition as $compo): ?><?php echo ($nbcompo) ? "<br/>".tdStart()."&nbsp;" : ""; echo $compo->nombre; ?>&nbsp;bouteilles de <?php echo $compo->contenance; $nbcompo++;?><?php endforeach; ?></td>
    </tr>
    <tr>
        <td><?php echo tdStart() ?>&nbsp;Volume total mis en bouteilles</td>
        <td><?php echo tdStart() ?>&nbsp;<?php  echo $tirage->getVolumeTotalComposition(); ?> hl</td>
    </tr>
</table>
