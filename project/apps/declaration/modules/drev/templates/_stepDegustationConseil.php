<?php $steps = array(
                    "prelevement" => 1,
                    "lot_alsace" => 2,
					"lot_grdcru" => 3,
                      ); ?>
<?php $stepNum = $steps[$step]; ?>

<ul class="nav nav-tabs">
  <li class="<?php if($stepNum == 1): ?>active<?php endif; ?>"><a href="<?php if($stepNum != 1): ?><?php echo url_for("drev_degustation_conseil", $drev) ?><?php else: echo '#'; endif; ?>">Prèlevement en cuve ou en fût</a></li>
  <li class="<?php if($stepNum == 2): ?>active<?php endif; ?>"><a href="<?php if($stepNum != 2): ?><?php echo url_for("drev_lots_alsace", $drev) ?><?php else: echo '#'; endif; ?>">Lots AOC Alsace</a></li>
  <li class="<?php if($stepNum == 3): ?>active<?php endif; ?>"><a href="<?php if($stepNum != 3): ?><?php echo url_for("drev_lots_grdcru", $drev) ?><?php else: echo '#'; endif; ?>">Lots AOC Alsace Grand Cru</a></li>
</ul>

