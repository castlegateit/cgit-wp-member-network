<?php

namespace Cgit\MemberNetwork;

class Roles
{
    /**
     * Roles
     *
     * All roles created by this plugin. By default, the plugin registers a
     * single role with no capabilities.
     *
     * @var array
     */
    private $roles = [
        'network_member' => [
            'name' => 'cgit_network_member',
            'label' => 'Network Member',
            'capabilities' => [],
        ],
    ];

    /**
     * Construct
     *
     * Apply a filter to the array of user roles so that they can be modified by
     * other plugins. Note that user roles are created during plugin activation,
     * so the plugin must be reactivated for any user role modifications to take
     * effect.
     *
     * @return void
     */
    public function __construct()
    {
        $this->roles = apply_filters('cgit_member_network_roles', $this->roles);
    }

    /**
     * Register all user roles
     *
     * Note that user roles are stored in the database. This method should be
     * run once, during plugin activation.
     *
     * @return void
     */
    public function create()
    {
        foreach ($this->roles as $role) {
            remove_role($role['name']);
            add_role($role['name'], $role['label'], $role['capabilities']);
        }
    }

    /**
     * Return user role information
     *
     * Provided with just a key, return the full array of user role information;
     * provided with a key and a field, return the name, label, or capabilities
     * for that role.
     *
     * @param string $key
     * @return mixed
     */
    public function info($key, $field = null)
    {
        if (!array_key_exists($key, $this->roles)) {
            return;
        }

        if (!is_null($field)) {
            if (!array_key_exists($field, $this->roles[$key])) {
                return;
            }

            return $this->roles[$key][$field];
        }

        return $this->roles[$key];
    }

    /**
     * Return all user roles
     *
     * @return array
     */
    public function roles()
    {
        return $this->roles;
    }

    /**
     * Return default user role (for new members)
     *
     * @return array
     */
    public function defaultRole()
    {
        $role = array_values($this->roles)[0];
        $role = apply_filters('cgit_member_network_roles_default_role', $role);

        return $role;
    }

    /**
     * Is the current user a network member?
     *
     * @return boolean
     */
    public function isNetworkMember()
    {
        if (!is_user_logged_in()) {
            return false;
        }

        $valid_roles = array_map(function ($role) {
            return $role['name'];
        }, $this->roles);

        $current_roles = wp_get_current_user()->roles;
        $roles = array_intersect($valid_roles, $current_roles);
        $is_member = count($roles) > 0;

        return apply_filters('cgit_member_network_is_member', $is_member);
    }

    /**
     * Is the current user a network admin?
     *
     * @return boolean
     */
    public function isNetworkAdmin()
    {
        if (!is_user_logged_in()) {
            return false;
        }

        $is_admin = current_user_can('edit_users');

        return apply_filters('cgit_member_network_is_admin', $is_admin);
    }
}
