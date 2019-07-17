<?php

if (!isset($field['options'])) {
    return;
}

$value = get_user_meta($user->ID, $key, true);

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
                <input type="radio" name="<?= $key ?>" value="<?= $option ?>" <?= $option == $value ? 'checked' : '' ?> />
                <?= $option ?>
            </label>

            <br />
            <?php
        }

        ?>
    </td>
</tr>
