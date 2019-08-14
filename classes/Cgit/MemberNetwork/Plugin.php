<?php

namespace Cgit\MemberNetwork;

class Plugin
{
    /**
     * Plugin file path
     *
     * @var string
     */
    public $file = '';

    /**
     * Construct
     *
     * @param string $file
     * @return void
     */
    public function __construct($file)
    {
        $this->file = $file;

        $this->createRoles();
        $this->createAdminFields();
    }

    /**
     * Create member user roles
     *
     * @return void
     */
    public function createRoles()
    {
        register_activation_hook($this->file, [(new Roles), 'create']);
    }

    /**
     * Create member profile fields
     *
     * @return void
     */
    public function createAdminFields()
    {
        $admin = new MemberAdmin;

        $admin->setViewPath(dirname($this->file) . '/views');
        $admin->init();
    }

    /**
     * Is this an associative array?
     *
     * @param array $foo
     * @return boolean
     */
    public static function isAssociativeArray($foo)
    {
        if ($foo === []) {
            return false;
        }

        return array_keys($foo) !== range(0, count($foo) - 1);
    }
}
