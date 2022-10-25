<ol class="breadcrumb">
    <li>Certipaq</li>
    <li><a href="<?php echo url_for('certipaq_list_demandes'); ?>">Demandes</a></li>
    <li>Détail de la demande <?php echo $id; ?></li>
</ol>

<div class="page-header no-border">
    <h1>Certipaq : Détail de la demande <?php echo $id; ?></h1>
</div>

<h2>Demande</h2>
<table class="table">
<?php foreach ($param_printable as $k => $value) : ?>
<tr><th><?php echo $k ; ?></th><td><?php echo $value; ?></td></tr>
<?php endforeach; ?>
</table>

<h2>Raw</h2>
<pre>
<?php print_r($param); ?>
</pre>