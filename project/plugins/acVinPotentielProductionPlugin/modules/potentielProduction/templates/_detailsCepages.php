<?php 
    if (isset($cepages) && $cepages['TOTAL'] > 0): 
        foreach ($cepages as $cepage => $surface): 
            if ($cepage == 'TOTAL' || !$surface) continue;
?>
<tr>
	<td><?php echo $cepage ?></td>
	<td class="text-right"><?php echo $surface ?>&nbsp;<small class="text-muted">ha</small></td>
</tr>
<?php endforeach; endif; ?>