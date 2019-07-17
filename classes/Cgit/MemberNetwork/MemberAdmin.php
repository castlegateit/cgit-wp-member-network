<?php

namespace Cgit\MemberNetwork;

class MemberAdmin
{
    /**
     * List of meta fields
     *
     * @var array
     */
    private $fields = [];

    /**
     * View path
     *
     * @var string
     */
    private $views = '';

    /**
     * Construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->setMetaFields();
    }

    /**
     * Set editable meta fields
     *
     * @return void
     */
    private function setMetaFields()
    {
        $fields = (new MemberFields)->metaFields();
        $fields = apply_filters('cgit_member_network_member_admin_fields', $fields);

        $this->fields = $fields;
    }

    /**
     * Initialize
     *
     * @return void
     */
    public function init()
    {
        add_action('show_user_profile', [$this, 'printProfileFields'], 100);
        add_action('edit_user_profile', [$this, 'printProfileFields'], 100);
        add_action('personal_options_update', [$this, 'updateProfileFields']);
        add_action('edit_user_profile_update', [$this, 'updateProfileFields']);
    }

    /**
     * Print fields
     *
     * @param WP_User $user
     * @return void
     */
    public function printProfileFields($user)
    {
        include $this->views . '/profile-head.php';

        foreach ($this->fields as $key => $field) {
            $type = 'text';

            if (isset($field['type'])) {
                $type = $field['type'];
            }

            include $this->views . '/profile-field-' . $type . '.php';
        }

        include $this->views . '/profile-foot.php';
    }

    /**
     * Save values
     *
     * @param integer $id
     * @return void
     */
    public function updateProfileFields($id)
    {
        if (!current_user_can('edit_user', $id)) {
            return;
        }

        foreach (array_keys($this->fields) as $field) {
            if (!isset($_POST[$field])) {
                continue;
            }

            update_user_meta($id, $field, $_POST[$field]);
        }
    }

    /**
     * Set view path
     *
     * @param string $path
     * @return void
     */
    public function setViewPath($path)
    {
        $this->views = $path;
    }
}
