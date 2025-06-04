<td cellspacing="0" cellpadding="0" style="margin: 0; padding: 0;">
    <table class="table-etiquette" cellspacing="0" cellpadding="0" style="font-size:8px;overflow: hidden;white-space: nowrap; top: 0; left: 0; padding: 0; margin: 0; width: 220px">
      <tr>
            <td style="overflow-wrap:break-word;text-align: left; height: 6px; line-height: 6px; overflow: hidden;" colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td style="overflow-wrap:break-word;text-align: left; height: 15px; line-height: 15px; overflow: hidden;">&nbsp;&nbsp;N° <strong><?php echo (int)$lotInfo->lot->numero_archive;  ?></strong></td>
        <td style="overflow-wrap:break-word;text-align: right; height: 15px; line-height: 15px; overflow: hidden; padding-right">N°DOSSIER <strong><?php echo (int)$lotInfo->lot->numero_dossier;  ?></strong>
        </td>
      </tr>
      <tr>
          <td colspan="2" style="overflow-wrap:break-word;text-align: center; height: 28px; line-height: 14px; overflow: hidden;" >
              <?php if (($i % 3) != 2 || !$anonymat4labo): ?>
                  <strong><?php echo ($lotInfo->lot->declarant_nom)? truncate_text(html_entity_decode($lotInfo->lot->declarant_nom, ENT_QUOTES | ENT_SUBSTITUTE), 47, '… ', 'middle') : "Leurre";  ?></strong>
                  <?php if($lotInfo->etablissement->cvi):echo ($lotInfo->etablissement->cvi); elseif ($lotInfo->etablissement->siret):echo substr($lotInfo->etablissement->siret,0,9)." "; endif; ?>
                  <?php if($lotInfo->etablissement->num_interne): ?>&nbsp;/&nbsp;<?php echo substr($lotInfo->etablissement->num_interne,0,6) ?><?php endif; ?>
        <?php else: ?><br /><i>Destiné au laboratoire</i><?php endif; ?></td>
      </tr>
      <tr>
            <td style="overflow-wrap:break-word;text-align: left; height: 4px; line-height: 1px; overflow: hidden;" colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2" style="overflow-wrap:break-word;text-align: center; height: 28.5px; line-height: 14px; overflow: hidden; vertical-align: middle;"><strong>&nbsp;&nbsp;<?php echo truncate_text(strtoupper(
            KeyInflector::unaccent(($lotInfo->lot->getConfig() ? null : $lotInfo->lot->details)." ".$lotInfo->lot->produit_libelle)
        ), 50, '…', 'middle') .' '.  $lotInfo->lot->millesime;  ?></strong>
          <?php if (! DegustationConfiguration::getInstance()->hasTypiciteCepage()): ?>
            <br /><?php echo truncate_text($lotInfo->lot->specificite, 50, '…', 'middle') ?>
          <?php endif; ?>
        </td>
      </tr>
      <?php if(DegustationConfiguration::getInstance()->hasTypiciteCepage()): ?>
      <tr>
        <td colspan="2" style="overflow-wrap:break-word;text-align: center; height: 24px; line-height: 12px; overflow: hidden;">&nbsp;&nbsp;<?php echo showOnlyCepages($lotInfo->lot, 58, 'span') ?></td>
      </tr>
      <?php endif; ?>
      <tr>
        <td style="overflow-wrap:break-word;text-align: left; height: 15px; line-height: 15px; overflow: hidden; width: 65%;">&nbsp;&nbsp;<?php $lot = $lotInfo->lot; $centilisation = $lot->centilisation ? " ($lot->centilisation)" : null; ?>Lot <strong><?php echo truncate_text($lotInfo->lot->numero_logement_operateur.$centilisation, 15, '…');  ?></strong>
        </td>
        <td style="overflow-wrap:break-word;text-align: right; height: 15px; line-height: 15px; overflow: hidden; width: 35%;"><?php if(!is_null($lotInfo->lot->volume)): ?><strong><?php echo sprintf("%.2f", $lotInfo->lot->volume);  ?></strong><?php endif; ?> HL<?php if(is_null($lotInfo->lot->volume)): ?>/COLS<?php endif; ?></td>
      </tr>
      <?php if(!DegustationConfiguration::getInstance()->hasTypiciteCepage()): ?>
      <tr>
        <td colspan="2" style="overflow-wrap:break-word;text-align: left; height: 24px; line-height: 12px; overflow: hidden;">&nbsp;&nbsp;DATE PRELEVMT&nbsp;____/____/_______<br />&nbsp;&nbsp;NOM PRELEVEUR&nbsp;_________________</td>
      </tr>
      <?php endif; ?>
      <tr>
            <td style="overflow-wrap:break-word;text-align: left; height: 7px; line-height: 7px; overflow: hidden;" colspan="2">&nbsp;</td>
      </tr>
    </table>
</td>
