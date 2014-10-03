<div class="tab-content hidden">
    <form id="form_transmission" method="post" action="<?php echo $url ?>">

        <input type="hidden" name="csv" value="<?php echo $csv ?>" />
        <input type="hidden" name="pdf" value="<?php echo $pdf ?>" />
        <button class="btn btn-default" type="submit">Envoyer</button>
    </form>
</div>

<p class="text-center">Transmission en cours...</p>

<script type="text/javascript">
    document.getElementById('form_transmission').submit();
</script>