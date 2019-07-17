<?php

namespace Cgit\MemberNetwork;

class MemberFields
{
    /**
     * Default fields
     *
     * @var array
     */
    protected $defaultFields = [
        'user_id' => [
            'label' => 'User ID',
        ],

        'email' => [
            'label' => 'Email',
            'required' => true,
            'validate' => 'email',
        ],

        'first_name' => [
            'label' => 'First name',
            'required' => true,
        ],

        'last_name' => [
            'label' => 'Last name',
            'required' => true,
        ],

        'title' => [
            'label' => 'Title',
        ],

        'organization' => [
            'label' => 'Organization',
        ],

        'department' => [
            'label' => 'Department',
        ],

        'position' => [
            'label' => 'Position',
        ],

        'tel' => [
            'label' => 'Telephone',
        ],

        'notes' => [
            'label' => 'Notes',
            'type' => 'textarea',
        ],
    ];

    /**
     * Available fields
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Construct
     *
     * @return void
     */
    public function __construct()
    {
        $fields = apply_filters('cgit_member_network_member_fields', $this->defaultFields);
        $fields = array_merge($fields, $this->defaultFields);

        $this->fields = $fields;
    }

    /**
     * Return default fields
     *
     * @return array
     */
    public function fields()
    {
        return $this->fields;
    }

    /**
     * Return meta fields
     *
     * @return array
     */
    public function metaFields()
    {
        return array_diff_key($this->fields, [
            'user_id' => null,
            'login' => null,
            'email' => null,
        ]);
    }
}
