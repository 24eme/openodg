<h1>Liste des tâches récurrentes</h1>

<div class='alert alert-warning'>
    Une seule génération peut être active à la fois.
</div>

<form method='post' id='form-generation'>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Description</th>
            <th>Lancer</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tasks as $id => $task): ?>
            <tr>
                <td><?php echo $task['title'] ?></td>
                <td><?php echo $task['desc'] ?></td>
                <td>
                    <button class="btn btn-default btn-xs" type="submit"
                            name="task" value="<?php echo $id ?>"
                            onclick="return confirm('Êtes vous sûr de vouloir exécuter cette tâche ?')">
                        Exécuter
                    </button>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

</form>

<h3>Historique des générations</h3>
<?php include_partial('generation/list', ['generations' => $generations]); ?>
