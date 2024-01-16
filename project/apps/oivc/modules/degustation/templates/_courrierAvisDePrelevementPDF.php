<?php use_helper('TemplatingPDF'); ?>

<?php include_partial('degustation/headerCourrier', ['courrier' => $courrier, "objet" => "Avis de prelevement par l’oivc"]) ?>

<p>Suite à l’examen analytique et/ou organoleptique d’un lot de vin de votre cave : <?php echo $lot->numero_logement_operateur ?></p>

<p><strong><?php echo showProduitCepagesLot($lot, false, null) ?> de <?php if ($lot->exist('quantite') && $lot->quantite) : ?><?php echo $lot->exist('quantite') ? $lot->quantite : 0 ?> cols<?php else: ?><?php echoFloatFr($lot->volume*1) ?> hl<?php endif; ?> (échantillon n°<?php echo $lot->numero_archive ?>)</strong></p>

<p>Une non-conformité a été détectée, le motif est : <strong><?php echo $lot->getShortLibelleConformite() ?> - <?php echo $lot->motif ?></strong></p>

<p>Dans l'attente du prélèvement par l'OIVC ce lot doit donc rester bloqué.</p>

<p>Afin d'organiser le prochain prélèvement merci de remplir la partie grisée</p>
<table border="1" style="background-color: #CCCCCC;">
    <tbody>
        <tr>
            <td>Mesures de correction pour rectifier le(s) défaut(s) :<br/><br/><br/><br/><br/></td>
        </tr>
        <tr>
            <td>
                <table>
                <tr><td>Je souhaite que le lot soit prélevé :</td>
                    <td><?php echoCheck('dès le mois prochain', false) ?></td>
                    <td><?php echoCheck('ultérieurement', false) ?></td>
                </tr>
                </table>
            </td>
        </tr>
        <tr><td></td></tr>
        <tr>
            <td>Vous devrez faire une déclaration (1ère mise en circulation suite à NC ODG) dans votre espace opérateur, onglet Déclarations lorsque vous aurez défini le mois de présentation du lot.<br/>Le délai ne peut dépasser 12 mois.<br/>Observations:<br/><br/><br/><br/><br/>
            </td>
        </tr>
        <tr><td>
            <table>
                <tr>
                    <td>À : ________________</td>
                    <td>Le : ________________</td>
                    <td>Signature, </td>
                </tr>
            </table>
        </td></tr>
        <br/>
    </tbody>
</table>

<p>Vous avez également la possibilité de déclasser ce lot en en adressant à votre ODG et à l’OIVC une déclaration de déclassement.</p>

<p>Merci de retourner ce courrier dans un délai de 15 jours.</p>

<?php include_partial('degustation/footerCourrier', ['courrier' => $courrier]) ?>
