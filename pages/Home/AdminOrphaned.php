<?php
    $db = new \Neodock\Framework\Database();

    $currentfiles = \Neodock\Recipes\RecipeUtilities::ReadRecipesFromDisk();
    $files = [];
    foreach ($currentfiles as $file) {
        $files[] = $file['filepath'];
    }
    $countofdiskfiles = count($files);

    $db->query('SELECT id, filepath FROM dbo.recipes WHERE datedeleted IS NULL');
    $db->execute();

    $result = $db->resultset();
    $dbrecords = [];
    foreach ($result as $row) {
        $dbrecords[$row['id']] = $row['filepath'];
    }

    $countoforiginaldbfiles = count($dbrecords);

    $recordstodelete = [];

    foreach ($dbrecords as $key => $value) {
        if (!in_array($value, $files)) {
            $recordstodelete[] = $key;
        }
    }

    $countofrecordstodelete = count($recordstodelete);

    $db->query('UPDATE dbo.ratings SET datedeleted=CURRENT_TIMESTAMP WHERE recipe_id=:id');
    foreach ($recordstodelete as $id) {
        $db->bind(':id', $id);
        $db->execute();
    }


    $db->query('UPDATE dbo.recipes SET datedeleted=CURRENT_TIMESTAMP WHERE id=:id');
    foreach ($recordstodelete as $id) {
        $db->bind(':id', $id);
        $db->execute();
    }
?>
<!-- Main Content -->
<div class="container mt-4">
    <p>Files on disk: <?=$countofdiskfiles?></p>
    <p>Records in DB at start: <?=$countoforiginaldbfiles?></p>
    <p>Records deleted: <?=$countofrecordstodelete?></p>
</div>