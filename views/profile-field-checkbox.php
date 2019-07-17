<?php

if (!isset($field['options'])) {
    return;
}

$values = get_user_meta($user->ID, $key, true);

?>

<tr>
    <th>
        <?= $field['label'] ?>
    </th>

    <td>
        <?php

        foreach ($field['options'] as $option) {
            ?>
            <label>
                <input type="checkbox" name="<?= $key ?>[]" value="<?= $option ?>" <?= in_array($option, $values) ? 'checked' : '' ?> />
                <?= $option ?>
            </label>

            <br />
            <?php
        }

        ?>
    </td>
</tr>
