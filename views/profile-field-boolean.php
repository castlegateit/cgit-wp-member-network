<tr>
    <th>
        <?= $field['label'] ?>
    </th>

    <td>
        <label>
            <input type="checkbox" name="<?= $key ?>" value="1" <?= get_user_meta($user->ID, $key, true) ? 'checked' : '' ?> />
            <?= $field['label'] ?>
        </label>
    </td>
</tr>
