<?php

namespace Cgit\MemberNetwork;

class SearchQuery
{
    /**
     * Search term
     *
     * @var string
     */
    private $term = '';

    /**
     * Search field
     *
     * @var string
     */
    private $field = '';

    /**
     * Search last name initial
     *
     * @var string
     */
    private $initial = '';

    /**
     * Search meta
     *
     * @var array
     */
    private $meta = [];

    /**
     * Database query
     *
     * @var string
     */
    private $query = '';

    /**
     * Database query WHERE parameters
     *
     * @var array
     */
    private $where = [];

    /**
     * Valid meta keys
     *
     * @var array
     */
    private $metaKeys = [];

    /**
     * Construct
     *
     * @param array $args
     * @return void
     */
    public function __construct($args = null)
    {
        $this->metaKeys = array_keys((new MemberFields)->metaFields());
        $this->parse($args);
    }

    /**
     * Parse request parameters
     *
     * @param array $args
     * @return void
     */
    public function parse($args)
    {
        if (!is_array($args)) {
            $args = [];
        }

        $this->extractSearchTerm($args);
        $this->extractSearchField($args);
        $this->extractSearchInitial($args);
        $this->extractSearchMeta($args);
    }

    /**
     * Extract search term from parameters
     *
     * @param array $args
     * @return void
     */
    private function extractSearchTerm($args)
    {
        if (!array_key_exists('term', $args)) {
            $this->term = '';

            return;
        }

        $term = $args['term'];
        $term = apply_filters('cgit_member_network_search_term', $term);

        $this->term = $term;
    }

    /**
     * Extract search field from parameters
     *
     * @param array $args
     * @return void
     */
    private function extractSearchField($args)
    {
        if (!array_key_exists('field', $args)) {
            $this->field = '';

            return;
        }

        $field = $args['field'];
        $field = apply_filters('cgit_member_network_search_field', $field);

        $this->field = $field;
    }

    /**
     * Extract search initial from parameters
     *
     * @param array $args
     * @return void
     */
    private function extractSearchInitial($args)
    {
        if (!array_key_exists('initial', $args) || !is_string($args['initial'])) {
            $this->initial = '';

            return;
        }

        $initial = strtolower(substr($args['initial'], 0, 1));
        $initial = apply_filters('cgit_member_network_search_initial', $initial);

        $this->initial = $initial;
    }

    /**
     * Extract search meta from parameters
     *
     * Assume that meta parameters each have a key prefix, by default "meta_",
     * to distinguish them from other query parameters.
     *
     * @param array $args
     * @return void
     */
    private function extractSearchMeta($args)
    {
        $meta = self::sanitizeMetaArgs($args);
        $meta = apply_filters('cgit_member_network_search_meta', $meta);

        $this->meta = $meta;
    }

    /**
     * Convert meta values from query string to meta values for search
     *
     * @param array $args
     * @return array
     */
    public static function sanitizeMetaArgs($args)
    {
        $prefix = apply_filters('cgit_member_network_search_meta_prefix', 'meta_');
        $keys = array_keys((new MemberFields)->metaFields());
        $meta = [];

        foreach ($args as $key => $value) {
            $item_prefix = substr($key, 0, strlen($prefix));
            $meta_key = substr($key, strlen($prefix));

            // Is this a valid meta parameter? If the key does not start with
            // the meta prefix or if the non-prefixed key is not a valid meta
            // key, skip this parameter.
            if ($item_prefix != $prefix || !in_array($meta_key, $keys)) {
                continue;
            }

            // For internal consistency and for everyone's sanity, ensure all
            // meta values are arrays.
            if (!is_array($value)) {
                $value = [$value];
            }

            $meta[$meta_key] = $value;
        }

        return $meta;
    }

    /**
     * Perform search and return results
     *
     * @return void
     */
    public function results()
    {
        global $wpdb;

        $this->updateSearchQuery();

        return $wpdb->get_results($this->query);
    }

    /**
     * Create or update search query
     *
     * @return void
     */
    private function updateSearchQuery()
    {
        $this->resetSearchQuery();

        $this->updateSearchQuerySelect();
        $this->updateSearchQueryFrom();
        $this->updateSearchQueryWhere();
        $this->updateSearchQueryOrderBy();
    }

    /**
     * Reset search query
     *
     * @return void
     */
    private function resetSearchQuery()
    {
        $this->query = '';
    }

    /**
     * Set search query SELECT statement
     *
     * @return void
     */
    private function updateSearchQuerySelect()
    {
        global $wpdb;

        // Basic user columns
        $columns = [
            'ID AS user_id',
            'user_login AS login',
            'user_email AS email',
            'display_name',
        ];

        // Initial column
        $columns[] = "(SELECT LOWER(SUBSTR(meta_value, 1, 1))
            FROM `{$wpdb->usermeta}`
            WHERE `{$wpdb->users}`.ID = user_id
                AND meta_key = 'last_name'
            LIMIT 1) AS initial";

        // Concatenated columns for free text search
        $all_fields = [];

        // Meta columns
        foreach ($this->metaKeys as $key) {
            // Basic column sub-query
            $column = "(SELECT meta_value
                FROM `{$wpdb->usermeta}`
                WHERE `{$wpdb->users}`.ID = user_id
                    AND meta_key = '$key'
                LIMIT 1)";

            // Column sub-query with alias
            $column_with_alias = "$column AS `$key`";

            // Append to main list of columns and combined columns
            $columns[] = $column_with_alias;
            $all_fields[] = $column;
        }

        // Add all fields column to list of columns
        if ($all_fields) {
            $all_fields_sql = implode(', ', $all_fields);
            $columns[] = "CONCAT_WS(' ', $all_fields_sql) AS all_fields";
        }

        // Assemble SELECT part of search query
        $this->append('SELECT ' . implode(', ', $columns));
    }

    /**
     * Set search query WHERE statement
     *
     * @return void
     */
    private function updateSearchQueryWhere()
    {
        $this->updateSearchQueryWhereRole();
        $this->updateSearchQueryWhereApproved();
        $this->updateSearchQueryWhereTerm();
        $this->updateSearchQueryWhereInitial();
        $this->updateSearchQueryWhereMeta();

        // No restrictions? Return all members.
        if (!$this->where) {
            return;
        }

        $this->append('HAVING ' . implode(' AND ', $this->where));
    }

    /**
     * Set search query WHERE parameters for user role(s)
     *
     * @return void
     */
    private function updateSearchQueryWhereRole()
    {
        global $wpdb;

        $roles = (new Roles)->roles();

        if (!$roles) {
            return;
        }

        foreach ($roles as $role) {
            if (!isset($role['name']) || !$role['name']) {
                continue;
            }

            $this->where[] = $wpdb->prepare("(SELECT meta_value
                    FROM {$wpdb->usermeta}
                    WHERE meta_key = '{$wpdb->base_prefix}capabilities'
                        AND user_id = ID
                    LIMIT 1)
                LIKE '%%%s%%'", $role['name']);
        }
    }

    /**
     * Set search query WHERE parameters for approved users
     *
     * Provides compatibility with the New User Approve plugin:
     * <https://wordpress.org/plugins/new-user-approve/>
     *
     * @return void
     */
    private function updateSearchQueryWhereApproved()
    {
        global $wpdb;

        if (!function_exists('pw_new_user_approve')) {
            return;
        }

        $this->where[] = "(SELECT meta_value
            FROM {$wpdb->usermeta}
            WHERE meta_key = 'pw_user_status'
                AND user_id = ID
            LIMIT 1) = 'approved'";
    }

    /**
     * Set search query WHERE parameters for search terms
     *
     * @return void
     */
    private function updateSearchQueryWhereTerm()
    {
        global $wpdb;

        if (!$this->term) {
            return;
        }

        $field = 'all_fields';

        if ($this->field) {
            $field = $this->field;
        }

        $this->where[] = $wpdb->prepare("`$field` LIKE '%%%s%%'", $this->term);
    }

    /**
     * Set search query WHERE parameters for user name initials
     *
     * @return void
     */
    private function updateSearchQueryWhereInitial()
    {
        global $wpdb;

        if (!$this->initial) {
            return;
        }

        $this->where[] = $wpdb->prepare('initial = "%s"', $this->initial);
    }

    /**
     * Set search query WHERE parameters for user meta
     *
     * @return void
     */
    private function updateSearchQueryWhereMeta()
    {
        global $wpdb;

        if (!$this->meta) {
            return;
        }

        foreach ($this->meta as $key => $values) {
            if (!$values) {
                continue;
            }

            $sub_parts = [];

            foreach ($values as $value) {
                $sub_parts[] = $wpdb->prepare("`$key` LIKE '%%%s%%'", $value);
            }

            $this->where[] = '(' . implode(' OR ', $sub_parts) . ')';
        }
    }

    /**
     * Set search query FROM statement
     *
     * @return void
     */
    private function updateSearchQueryFrom()
    {
        global $wpdb;

        $this->append('FROM ' . $wpdb->users);
    }

    /**
     * Set search query ORDER BY statement
     *
     * @return void
     */
    private function updateSearchQueryOrderBy()
    {
        $this->append('ORDER BY last_name ASC');
    }

    /**
     * Append text to search query
     *
     * @param string $sql
     * @return void
     */
    private function append($sql)
    {
        $this->query = trim("{$this->query} {$sql}");
    }
}
