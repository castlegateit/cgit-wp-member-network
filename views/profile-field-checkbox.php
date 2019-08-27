<?php

if (!isset($field['options']) || !$field['options']) {
    return;
}

$options = $field['options'];
$values = get_user_meta($user->ID, $key, true);

if (!\Cgit\MemberNetwork\Plugin::isAssociativeArray($options)) {
    $options = array_combine($options, $options);
}

function cgit_trim_member_values(&$value)
{
    $value = trim($value);
}

array_walk($values, 'cgit_trim_member_values');

?>

<tr>
    <th>
        <?= $field['label'] ?>
    </th>

    <td>
        <?php

        foreach ($field['options'] as $option => $label) {
            ?>
            <label>
                <input type="checkbox" name="<?= $key ?>[]" value="<?= $option ?>" <?= in_array($label, $values) ? 'checked' : '' ?> />
                <?= $label ?>
            </label>

            <br />
            <?php
        }

        ?>
    </td>
</tr>
