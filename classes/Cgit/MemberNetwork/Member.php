<?php

namespace Cgit\MemberNetwork;

class Member
{
    /**
     * User data
     *
     * @var array
     */
    private $data = [];

    /**
     * User meta
     *
     * @var array
     */
    private $meta = [];

    /**
     * Construct
     *
     * @return void
     */
    public function __construct($id = null)
    {
        $this->resetUserValues();
        $this->resetMetaValues();

        if (!is_null($id)) {
            $this->setId($id);
        }
    }

    /**
     * Reset user data
     *
     * @return void
     */
    private function resetUserValues()
    {
        $this->data = [
            'ID' => null,
            'user_email' => null,
            'display_name' => null,
        ];
    }

    /**
     * Reset meta data based on available fields
     *
     * @return void
     */
    private function resetMetaValues()
    {
        $keys = array_diff(array_keys((new MemberFields)->fields()), ['user_id', 'email']);
        $values = array_fill(0, count($keys), null);

        $this->meta = array_combine($keys, $values);
    }

    /**
     * Return user ID
     *
     * @return integer
     */
    public function getId()
    {
        return $this->data['ID'];
    }

    /**
     * Set user ID
     *
     * @param integer $id
     * @param string $field
     * @return void
     */
    public function setId($id, $field = null)
    {
        $user = $this->getUserByField($id, $field);

        // Reset data
        $this->resetUserValues();
        $this->resetMetaValues();

        if (!$user) {
            return;
        }

        // Import user data
        foreach ($this->data as $key => $value) {
            $this->data[$key] = $user->$key;
        }

        // Import meta data
        $this->importMetaValues();
    }

    /**
     * Return user instance based on email, login, or ID
     *
     * @param mixed $id
     * @param string $field
     * @return WP_User
     */
    private function getUserByField($id, $field = null)
    {
        if (is_null($field)) {
            if (filter_var($id, FILTER_VALIDATE_EMAIL)) {
                $field = 'email';
            } elseif (is_string($id)) {
                $field = 'login';
            } else {
                $field = 'id';
            }
        }

        return get_user_by($field, $id);
    }

    /**
     * Import valid meta values from database
     *
     * @return void
     */
    private function importMetaValues()
    {
        $fields = (new MemberFields)->fields();
        $meta = [];

        foreach (array_keys($this->meta) as $key) {
            $single = true;

            // Can the field have multiple values?
            if (isset($fields['multiple']) && $fields['multiple']) {
                $single = false;
            }

            $value = get_user_meta($this->data['ID'], $key, $single);

            if ($value) {
                $meta[$key] = $value;
            }
        }

        $this->meta = $this->sanitizeMetaValues($meta);
    }

    /**
     * Sanitize meta values
     *
     * Returns a complete array of meta values, with no additional keys and with
     * non-existent keys filled in with current values.
     *
     * @param array $values
     * @return array
     */
    private function sanitizeMetaValues($values)
    {
        return array_merge(
            $this->meta,
            array_intersect_key($values, $this->meta)
        );
    }

    /**
     * Get form-friendly values
     *
     * @return array
     */
    public function getValues()
    {
        $values = [
            'user_id' => $this->data['ID'],
            'email' => $this->data['user_email'],
        ];

        return array_merge($values, $this->meta);
    }

    /**
     * Set user and meta data based on form-friendly values
     *
     * @param array $values
     * @return void
     */
    public function setValues($values)
    {
        $data = [];
        $meta = $this->sanitizeMetaValues($values);

        if (isset($values['user_id'])) {
            $data['ID'] = $values['user_id'];
        }

        if (isset($values['email'])) {
            $data['user_email'] = $values['email'];
        }

        if (!isset($values['display_name'])) {
            $names = [
                $meta['first_name'],
                $meta['last_name'],
            ];

            $data['display_name'] = trim(implode(' ', $names));
        }

        $this->data = array_merge($this->data, $data);
        $this->meta = $meta;
    }

    /**
     * Create user
     *
     * @return void
     */
    public function create()
    {
        if ($this->exists()) {
            return trigger_error($this->data['user_email'] . ' already exists');
        }

        $data = array_merge($this->data, [
            'user_login' => $this->data['user_email'],
            'user_pass' => null,
            'role' => (new Roles)->defaultRole()['name'],
        ]);

        $this->data['ID'] = wp_insert_user($data);
        $this->updateMetaValues();
    }

    /**
     * Update user
     *
     * @return void
     */
    public function update()
    {
        if (!$this->exists()) {
            return trigger_error($this->data['user_email'] . ' does not exist');
        }

        wp_update_user($this->data);
        $this->updateMetaValues();
    }

    /**
     * Update user meta
     *
     * @return void
     */
    private function updateMetaValues()
    {
        if (!$this->exists()) {
            return;
        }

        foreach ($this->meta as $key => $value) {
            update_user_meta($this->data['ID'], $key, $value);
        }
    }

    /**
     * Does the user exist?
     *
     * @return boolean
     */
    private function exists()
    {
        return isset($this->data['ID']) && $this->data['ID'] &&
            get_user_by('email', $this->data['user_email']) !== false;
    }
}
