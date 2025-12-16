<?php
include 'db.php';

$template_id = intval($_GET['template_id']);

$fields = $conn->query("
    SELECT * FROM project_template_fields
    WHERE template_id = $template_id
");

while ($f = $fields->fetch_assoc()):
    $field_id = $f['field_id'];
    $field_name = htmlspecialchars($f['field_name']);
?>
    <label><?= $field_name ?>:</label><br>

    <select name="field_<?= $field_id ?>" required>
        <option value="">-- Select <?= $field_name ?> --</option>
        <?php
        $options = $conn->query("
            SELECT option_name
            FROM project_template_field_options
            WHERE field_id = $field_id
        ");
        while ($o = $options->fetch_assoc()):
        ?>
            <option value="<?= htmlspecialchars($o['option_name']) ?>">
                <?= htmlspecialchars($o['option_name']) ?>
            </option>
        <?php endwhile; ?>
    </select>
    <br><br>

<?php endwhile; ?>
