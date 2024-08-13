<?php use_helper("Date"); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Compte'); ?>
<?php use_helper('Float'); ?>

<style>
    <?php echo style(); ?>
    table {
        font-size: 12px;
    }

    th {
        font-weight: bold;
    }
</style>
<table>
    <tr>
        <td width="50%"><img src="file://<?php echo sfConfig::get('sf_web_dir').'/images/pdf/'; ?>logo_oivc.jpg" height="50"/></td>
        <td width="50%" style="text-align: right;"><img src="file://<?php echo sfConfig::get('sf_web_dir').'/images/pdf/'; ?>oivc_cofrac.png"  height="50"/></td>
    </tr>
</table>
<table border="1">
<tbody>
    <tr>
        <td>FUVC-OIVC<br/>MANUEL QUALITE / CHAPITRE 07<br/>ANNEXE 07.9 RAPPORT D INSPECTION CONTROLE PRODUIT/ 1223 / REV H</td>
    </tr>
</tbody>
</table>
<br/><br/>
<table style="text-align: center"><tr><td><strong>Rapport d'Inspection Contrôle Produit N° <?php echo $lot->unique_id ?></strong></td></tr></table>

<br/><br/>

<table border="1">
    <tbody>
        <tr style="background-color: #CCCCCC;"><td><strong>OPÉRATEUR</strong></td></tr>
        <tr><td>Nom ou raison sociale de l'opérateur<br/>
                <?php echo $etablissement->getRaisonSociale() ?><br/>
                N° CVI : <?php echo $etablissement->getCvi() ?><i> </i><i> </i><i> </i><i> </i><i> </i><i> </i><i> </i> N° SIRET de l'opérateur : <?php echo formatSIRET($etablissement->getSiret()) ?>
        </td></tr>
        <tr><td>Catégorie :
            <?php echoCheck("Producteur viticole", in_array($etablissement->famille, [EtablissementFamilles::FAMILLE_PRODUCTEUR, EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR])); ?>
            <?php echoCheck("Cave coopérative", $etablissement->famille == EtablissementFamilles::FAMILLE_COOPERATIVE); ?>
            <?php echoCheck("Négociant", in_array($etablissement->famille, [EtablissementFamilles::FAMILLE_NEGOCIANT, EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR])); ?></td></tr>
        <tr><td>Adresse de l'opérateur<br/>
                <?php echo $etablissement->getAdresse() ?>, <?php echo $etablissement->getCodePostal() ?> <?php echo $etablissement->getCommune() ?><br/>
                Adresse du site de prélèvement<br/>
                <?php echo $lot->getAdresseLogement() ?>
        </td></tr>
        <tr style="background-color: #CCCCCC;"><td><strong>PRÉLÈVEMENT N° ÉCHANTILLON :</strong> <?php echo $lot->numero_archive; ?></td></tr>
        <tr><td>
            <table>
                <tr>
                    <td style="width: 10%">Type :</td>
                    <td><?php echoCheck('Aléatoire', $lot->initial_type == TourneeClient::TYPE_TOURNEE_LOT_ALEATOIRE && !$lot->hasSpecificitePassage() && $lot->getRegionOrigine() === 'OIVC') ?></td>
                    <td><?php echoCheck('Aléatoire renforcé', $lot->initial_type == TourneeClient::TYPE_TOURNEE_LOT_ALEATOIRE_RENFORCE && !$lot->hasSpecificitePassage() && $lot->getRegionOrigine() === 'OIVC') ?></td>
                    <td><?php echoCheck('Vrac export', $lot->initial_type == 'VracExport' && !$lot->hasSpecificitePassage()) ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td><?php echoCheck('Recours', $lot->initial_type == TourneeClient::TYPE_TOURNEE_LOT_RECOURS && !$lot->hasSpecificitePassage()) ?></td>
                    <td><?php echoCheck('Sous traitance', false) ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="3"><?php echoCheck('Suite à contrôle produit ODG non conforme', $lot->isNCODG()); ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="3"><?php echoCheck('Suite à contrôle produit OIVC non conforme', $lot->isNCOI()); ?></td>
                </tr>
                <tr>
                    <td></td><td colspan="3"><?php echoCheck('Contrôle supplémentaire', $lot->initial_type == TourneeClient::TYPE_TOURNEE_LOT_SUPPLEMENTAIRE) ?></td><td></td>
                </tr>
            </table>
        </td></tr>
        <tr><td>Date du prélèvement : <?php echo str_replace(' 00:00', '', $lot->getPrelevementFormat()) ?></td></tr>
        <tr><td>Au moment du prélèvement, le vin est :
            <?php if($lot->initial_type == TourneeClient::TYPE_TOURNEE_LOT_RECOURS) :?>
                Témoin
            <?php else: ?>
                <?php if ($lot->exist('quantite') && $lot->quantite) : ?>conditionné<?php else: ?>en vrac<?php endif; ?><?php if($lot->destination_type == DRevClient::LOT_DESTINATION_CONDITIONNEMENT_CONSERVATOIRE): ?> sur conservatoire<?php endif; ?>
            <?php endif; ?>
        </td></tr>
        <tr><td>Opérateur ou son représentant présent au cours du prélèvement<br/>
                Nom : <?php echo $courrier->getExtra('representant_nom'); ?><i> </i><i> </i><i> </i><i> </i><i> </i><i> </i><i> </i>Fonction : <?php echo $courrier->getExtra('representant_fonction'); ?>
        </td></tr>
        <tr><td>Agent de l'OIVC<br/>
                Nom : <?php echo $courrier->getExtra('agent_nom'); ?>
        </td></tr>
        <tr style="background-color: #CCCCCC;"><td><strong>DESCRIPTION DU LOT :</strong></td></tr>
        <tr><td>
            <table cellspacing="0" cellpadding="0">
                <tr>
                    <td style="width: 40%;">AOC et couleur : <?php echo $lot->produit_libelle; ?></td>
                    <td style="width: 30%;">Millésime : <?php echo $lot->millesime ?></td>
                    <?php if ($lot->exist('quantite') && $lot->quantite) : ?>
                    <td style="width: 30%;">Nb Cols : <?php echo $lot->quantite ?></td>
                    <?php else: ?>
                    <td style="width: 30%;">Volume : <?php echoFloatFr($lot->volume) ?> hl</td>
                    <?php endif; ?>
                </tr>
                <tr>
                    <td style="width: 40%;">N° lot / logement : <?php echo $lot->numero_logement_operateur ?></td>
                    <?php if($lot->destination_date && !($lot->initial_type == 'VracExport') ): ?>
                    <td style="width: 50%;">Date de transaction/conditionnement : <?php echo DateTimeImmutable::createFromFormat('Y-m-d', $lot->destination_date)->format('d/m/Y') ?></td>
                    <?php endif; ?>
                </tr>
            </table>
        </td></tr>
        <tr>
            <td>
                Observations éventuelles :
                <br/><br/>
            </td>
        </tr>
    </tbody>
</table>

<br/>
<br/>

<table border="1">
    <tr><td colspan="6">RÉSULTAT DU CONTRÔLE ET MANQUEMENTS OBSERVÉS</td></tr>
    <tr style="text-align: center">
        <td></td> <td>Date</td> <td>Conforme</td> <td>Non conforme</td> <td>Libellé manquement / Code manquement</td> <td>Niveau de gravité</td>
    </tr>
    <tr>
        <td>Examen analytique<br/>(sous traitance)</td>
        <td style="text-align:center;"><?php if ($courrier->getExtra('analytique_date')) echo $courrier->getExtraDateFormat('analytique_date', 'd/m/Y'); ?></td>
        <td><?php echo echoCheck(null, ($courrier->getExtra('analytique_date')) && (! $lot->isNonConforme() || $courrier->getExtra('analytique_conforme'))); ?></td>
        <td><?php echo echoCheck(null, $lot->isNonConforme() && $courrier->getExtra('analytique_date') && !$courrier->getExtra('analytique_conforme')); ?></td>
        <td style="text-align:center;">
            <?php echo $courrier->getExtra('analytique_libelle') ; ?>
            <?php if ($courrier->getExtra('analytique_libelle') && $courrier->getExtra('analytique_code')) echo '/'; ?>
            <?php echo $courrier->getExtra('analytique_code') ; ?>
        </td>
        <td style="text-align:center;"><?php echo $courrier->getExtra('analytique_niveau') ; ?></td>
    </tr>
    <tr>
        <td>Examen organoleptique<br/></td>
        <td style="text-align:center;"><?php echo $lot->getDateCommissionFormat(); ?></td>
        <td><?php echo echoCheck(null, ! $lot->isNonConforme()); ?></td> <td><?php echo echoCheck(null, $lot->isNonConforme()); ?></td>
        <td style="text-align:center;">
            <?php echo $lot->motif ; ?>
            <?php if ($lot->motif && $courrier->getExtra('organoleptique_code')) echo '/'; ?>
            <?php echo $courrier->getExtra('organoleptique_code') ; ?>
        </td>
        <td style="text-align:center;"><?php echo $courrier->getExtra('organoleptique_niveau') ; ?></td>
    </tr>
    <tr><td colspan="6">Date transmission INAO :<br /><br /></td></tr>
</table>

<br/><br/>

<table>
<tr>
  <td style="width: 50%"><strong>Nom du responsable d'inspection :</strong></td>
  <td style="width: 25%"><strong>Date :</strong></td>
  <td style="width: 25%"><strong>Signature :</strong></td>
</tr>
</table>

<br/><br/>
<br/><br/>

<p><small>Les points de contrôles n'ayant pas pu être vus par rapport au travail initialement prévu sont notés dans la ligne observations.<br/>
Les méthodes d'inspection utilisées sont décrites dans le Plan d'Inspection de l'AOC concernée.<br/>
En cas de sous traitance de point de contrôle noter SST à la ligne observations.</small></p>
