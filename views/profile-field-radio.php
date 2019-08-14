<?php

if (!isset($field['options']) || !$field['options']) {
    return;
}

$options = $field['options'];
$value = get_user_meta($user->ID, $key, true);

if (!\Cgit\MemberNetwork\Plugin::isAssociativeArray($options)) {
    $options = array_combine($options, $options);
}

?>

<tr>
    <th>
        <?= $field['label'] ?>
    </th>

    <td>
        <?php

        foreach ($options as $option => $label) {
            ?>
            <label>
                <input type="radio" name="<?= $key ?>" value="<?= $option ?>" <?= $option == $value ? 'checked' : '' ?> />
                <?= $label ?>
            </label>

            <br />
            <?php
        }

        ?>
    </td>
</tr>
