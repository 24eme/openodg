<?php use_helper("Date") ?>
<div class="page-header">
    <h2>Tournée Constat VT/SGN</h2>    
</div>
<div class="row">
    <div class="col-xs-8 col-xs-offset-2 text-center">
        
                <h3><?php echo format_date($tournee->date, "P", "fr_FR"); ?></h3>
           
      <h4><?php echo $tournee->agents->{$tournee->appellation}->nom; ?></h4>
           
    </div>

</div>