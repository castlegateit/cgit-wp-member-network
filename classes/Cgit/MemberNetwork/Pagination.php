<?php

namespace Cgit\MemberNetwork;

class Pagination
{
    /**
     * Items
     *
     * @var array
     */
    private $items;

    /**
     * Number of items per page
     *
     * @var integer
     */
    private $itemsPerPage = 10;

    /**
     * First page number
     *
     * @var integer
     */
    private $firstPage = 1;

    /**
     * Last page number
     *
     * @var integer
     */
    private $lastPage = 1;

    /**
     * Current page number
     *
     * @var integer
     */
    private $currentPage = 1;

    /**
     * GET key for page numbers
     *
     * @var string
     */
    private $key = 'page_number';

    /**
     * First item
     *
     * @var integer
     */
    private $firstIndex = 0;

    /**
     * Last item
     *
     * @var integer
     */
    private $lastIndex = 0;

    /**
     * First item visible on page
     *
     * @var integer
     */
    private $firstVisibleIndex = 0;

    /**
     * Last item visible on page
     *
     * @var integer
     */
    private $lastVisibleIndex = 0;

    /**
     * Constructor
     *
     * @param array $items
     * @return void
     */
    public function __construct($items, $limit = 10)
    {
        $this->items = $items;

        $this->key = apply_filters(
            'cgit_member_network_pagination_key',
            $this->key
        );

        $this->itemsPerPage = apply_filters(
            'cgit_member_network_pagination_limit',
            (int) $limit
        );

        $this->generatePageNumbers();
        $this->generateIndexes();
    }

    /**
     * Generate page numbers
     *
     * @return void
     */
    private function generatePageNumbers()
    {
        if ($this->itemsPerPage > 0) {
            $this->lastPage = ceil(count($this->items) / $this->itemsPerPage);
        }

        if (isset($_GET[$this->key])) {
            $this->currentPage = min($_GET[$this->key], $this->lastPage);
        }
    }

    /**
     * Generate indexes
     *
     * @return void
     */
    private function generateIndexes()
    {
        $limit = $this->itemsPerPage;

        $this->lastIndex = count($this->items) - 1;
        $this->firstVisibleIndex = ($this->currentPage - 1) * $limit;
        $this->lastVisibleIndex = $this->firstVisibleIndex + $limit -1;

        if ($this->lastVisibleIndex > $this->lastIndex) {
            $this->lastVisibleIndex = $this->lastIndex;
        }
    }

    /**
     * Return all page numbers
     *
     * @return array
     */
    public function pages()
    {
        return range($this->firstPage, $this->lastPage);
    }

    /**
     * Return current page number
     *
     * @return integer
     */
    public function page()
    {
        return $this->currentPage;
    }

    /**
     * Return page URL
     *
     * @param integer $number
     * @return string
     */
    public function pageUrl($number = 1)
    {
        if ($number > $this->lastPage) {
            $number = $this->lastPage;
        }

        if ($number == 1) {
            return remove_query_arg($this->key);
        }

        return add_query_arg($this->key, $number);
    }

    /**
     * Return list of pagination URLs
     *
     * @return array
     */
    public function urls()
    {
        $urls = [];

        if (!$this->items || $this->firstPage == $this->lastPage) {
            return $urls;
        }

        foreach ($this->pages() as $page) {
            $urls[$page] = $this->pageUrl($page);
        }

        return apply_filters('cgit_member_network_pagination_urls', $urls);
    }

    /**
     * Return pagination links
     *
     * @param boolean $return_array
     * @return string
     */
    public function links($return_array = false)
    {
        $urls = $this->urls();
        $links = [];

        if (!$urls) {
            return;
        }

        if ($this->currentPage != $this->firstPage) {
            $links[] = '<a href="' . $this->pageUrl($this->firstPage) . '" class="page-numbers prev">&lt;</a>';
        }

        foreach ($urls as $page => $url) {
            if ($page == $this->currentPage) {
                $links[] = '<span class="page-numbers current">' . $page . '</span>';
                continue;
            }

            $links[] = '<a href="' . $url . '" class="page-numbers">' . $page . '</a>';
        }

        if ($this->currentPage != $this->lastPage) {
            $links[] = '<a href="' . $this->pageUrl($this->lastPage) . '" class="page-numbers next">&gt;</a>';
        }

        $links = apply_filters('cgit_member_network_pagination_links', $links);

        if ($return_array) {
            return $links;
        }

        return implode(PHP_EOL, $links);
    }

    /**
     * Return paginated results
     *
     * @return array
     */
    public function results()
    {
        return array_slice($this->items, $this->firstVisibleIndex, $this->itemsPerPage);
    }

    /**
     * Return human readable index of first visible element
     *
     * @param boolean $zero
     * @return integer
     */
    public function firstVisibleIndex($zero = false)
    {
        if ($zero) {
            return $this->firstVisibleIndex;
        }

        return $this->firstVisibleIndex + 1;
    }

    /**
     * Return human readable index of last visible element
     *
     * @param boolean $zero
     * @return integer
     */
    public function lastVisibleIndex($zero = false)
    {
        if ($zero) {
            return $this->lastVisibleIndex;
        }

        return $this->lastVisibleIndex + 1;
    }

    /**
     * Return human readable index of last element
     *
     * @param boolean $zero
     * @return integer
     */
    public function lastIndex($zero = false)
    {
        if ($zero) {
            return $this->lastIndex;
        }

        return $this->lastIndex + 1;
    }
}
