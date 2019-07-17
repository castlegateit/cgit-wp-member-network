<?php

if (!isset($field['options'])) {
    return;
}

$value = get_user_meta($user->ID, $key, true);

?>

<tr>
    <th>
        <label for="<?= $key ?>"><?= $field['label'] ?></label>
    </th>

    <td>
        <select name="<?= $key ?>" id="<?= $key ?>">
            <option value="">None</option>

            <?php

            foreach ($field['options'] as $option) {
                ?>
                <option value="<?= $option ?>" <?= $option == $value ? 'selected' : '' ?>><?= $option ?></option>
                <?php
            }

            ?>
        </select>
    </td>
</tr>
