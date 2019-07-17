<tr>
    <th>
        <label for="<?= $key ?>"><?= $field['label'] ?></label>
    </th>

    <td>
        <input type="text" name="<?= $key ?>" id="<?= $key ?>" value="<?= get_user_meta($user->ID, $key, true) ?>" class="regular-text" />
    </td>
</tr>
