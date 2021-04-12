<?php use_helper('TemplatingPDF'); ?>

<?php $i=0; ?>
<table border="0" class="" cellspacing=0 cellpadding=0 style="text-align: right;">
<?php foreach($plancheLots as $key => $lotInfo): ?>
  <?php  if (!($key%3)){echo("<tr>");}?>
        <?php $i++; ?>
        <td style="text-align: left;">
            <table cellspacing=0 cellpadding=0 style="font-size:8px;padding:0px;">
              <?php echo tdStart() ?>
              <tr style="line-height:17px;">
                <td colspan="2" style="overflow-wrap:break-word;text-align:center;line-height:8px;" >
                  <?php echo tdStart() ?>&nbsp;<h1><strong><?php echo $lotInfo->getNumeroAnonymat(); ?></strong></h1>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                </td>
              </tr>
              <tr style="line-height:17px;">
                <td colspan="2" style="overflow-wrap:break-word; text-align: center;">
                  <?php echo tdStart() ?><strong>&nbsp;IGP&nbsp;<?php echo $lotInfo->produit_libelle .' '.  $lotInfo->millesime;  ?></strong>
                </td>
              </tr>
              <tr style="line-height:12px;">
                <td colspan="2" style="overflow-wrap:break-word;text-align:center;line-height:7px;">
                  <?php echo tdStart() ?>
                  <?php echo showOnlyCepages($lotInfo, 75); ?>
                </td>
              </tr>
              <tr style="line-height:17px;">
                <td colspan="2" style="overflow-wrap:break-word;text-align:center;line-height:6px;">
                  <?php if ($lotInfo->details): ?>
                  <?php echo tdStart() ?><strong><?php echo $lotInfo->details;  ?></strong>
                  <?php else: ?>
                  <?php echo tdStart() ?>
                  <?php endif; ?>

                </td>
              </tr>
              <tr>
                <td>
                </td>
                <td>
                </td>
              </tr>
            </table>
        </td>
<?php if (!(($key+1)%3)){ echo ('</tr>'); $i=0;} ?>
<?php endforeach; ?>


<?php if($i != 0):?>
  <?php for($j=$i; $j < 3; $j++): ?>
    <td>
      <table cellspacing=0 cellpadding=0 style="font-size:8px;padding:0px;">
        <?php echo tdStart() ?>
        <tr>
          <td colspan="2">
            <?php echo tdStart() ?>
          </td>
        </tr>
        <tr>
          <td colspan="2">
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <?php echo tdStart() ?>
          </td>
        </tr>
        <tr>
          <td colspan="2">
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <?php echo tdStart() ?>
          </td>
        </tr>
        <tr>
          <td>
          </td>
          <td>
          </td>
        </tr>
      </table>
    </td>
  <?php endfor; ?>
  </tr>
<?php endif; ?>
</table>
