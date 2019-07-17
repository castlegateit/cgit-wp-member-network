<?php

namespace Cgit\MemberNetwork;

class Search
{
    /**
     * Source data
     *
     * @var array
     */
    private $data = [];

    /**
     * Number of results per page
     *
     * @var integer
     */
    private $limit = null;

    /**
     * Pagination instance
     *
     * @var Pagination
     */
    private $pagination;

    /**
     * Search results
     *
     * @var array
     */
    private $results = [];

    /**
     * Result format
     *
     * Defines what to return. By default, meta values are returned as WP would
     * return them, i.e. serialized data is automatically unserialized.
     * Alternatively, "raw" will return the raw database values; "users" will
     * return WP_User instances.
     *
     * @var string
     */
    private $format = 'default';

    /**
     * Parse data from source
     *
     * If the source is an array, use the array as the source data. If the
     * source is a string that is a case-insensitive match for "post", attempt
     * to use POST data. Otherwise, attempt to use GET data.
     *
     * @param mixed $source
     * @return void
     */
    public function parse($source = null)
    {
        if (is_array($source)) {
            $this->data = $source;
            return;
        }

        if (is_string($source) && strtolower($source == 'post')) {
            $this->data = $_POST;
            return;
        }

        $this->data = $_GET;
    }

    /**
     * Set number of results per page
     *
     * @param integer $limit
     * @return void
     */
    public function paginate($limit)
    {
        if (!is_int($limit)) {
            $this->limit = null;
            return;
        }

        $this->limit = $limit;
        $this->updatePagination();
    }

    /**
     * Perform search and sanitize results
     *
     * @return void
     */
    public function search()
    {
        $this->performSearch();
        $this->sanitizeResults();
        $this->updatePagination();
    }

    /**
     * Return search results
     *
     * @return array
     */
    public function results()
    {
        if (is_null($this->limit) || is_null($this->pagination)) {
            return $this->results;
        }

        return $this->pagination->results();
    }

    /**
     * Perform search
     *
     * @return void
     */
    private function performSearch()
    {
        $query = new SearchQuery($this->data);
        $this->results = $query->results();
    }

    /**
     * Sanitize search results
     *
     * Format the results as raw values, WP meta values, or WP users based on
     * the value of the format property.
     *
     * @return void
     */
    private function sanitizeResults()
    {
        // Do not sanitize values returned from database
        if (strtolower($this->format) == 'raw') {
            return;
        }

        // Return WP_User instances
        if (strtolower(substr($this->format, 0, 4)) == 'user') {
            $this->results = array_map(function ($result) {
                return get_user_by('id', $result->user_id);
            }, $this->results);

            return;
        }

        // Return values as WP meta values (unserialized as appropriate)
        $this->results = array_map(function ($result) {
            // List of valid meta keys actually assigned to this result
            $keys = array_intersect(
                array_keys((new MemberFields)->metaFields()),
                array_keys(get_object_vars($result))
            );

            foreach ($keys as $key) {
                $result->$key = get_user_meta($result->user_id, $key, true);
            }

            return $result;
        }, $this->results);
    }

    /**
     * Create or update pagination
     *
     * @return void
     */
    private function updatePagination()
    {
        if (!$this->results) {
            $this->pagination = null;
            return;
        }

        $this->pagination = new Pagination($this->results, $this->limit);
    }

    /**
     * Return pagination instance
     *
     * @return Pagination
     */
    public function pagination()
    {
        return $this->pagination;
    }

    /**
     * Return pagination HTML
     *
     * @return string
     */
    public function renderPaginationLinks()
    {
        if (is_null($this->pagination)) {
            return '';
        }

        return $this->pagination->links();
    }

    /**
     * Return search parameters
     *
     * @param mixed $key
     * @return mixed
     */
    public function parameters($key = null)
    {
        if (is_null($key)) {
            return $this->data;
        }

        if (is_string($key) && array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        if ($key == 'meta') {
            return SearchQuery::sanitizeMetaArgs($this->data);
        }
    }
}
