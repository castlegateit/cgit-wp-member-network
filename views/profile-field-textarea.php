<tr>
    <th>
        <label for="<?= $key ?>"><?= $field['label'] ?></label>
    </th>

    <td>
        <textarea name="<?= $key ?>" id="<?= $key ?>" rows="4"><?= get_user_meta($user->ID, $key, true) ?></textarea>
    </td>
</tr>
