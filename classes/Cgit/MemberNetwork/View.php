<?php

namespace Cgit\MemberNetwork;

class View
{
    /**
     * Network view GET parameter key
     *
     * @var string
     */
    private $key = 'network_page';

    /**
     * Available views
     *
     * @var array
     */
    private $views = [
        'dashboard',
        'search',
        'results',
        'join',
        'profile',
        'edit',
    ];

    /**
     * Construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->key = apply_filters('cgit_member_network_view_key', $this->key);
        $this->views = apply_filters('cgit_member_network_available_views', $this->views);
    }

    /**
     * Return view name
     *
     * Return the name of the view that should be displayed on the current page
     * based on the current GET parameter. If the parameter is missing, return
     * the first available view.
     *
     * @return string
     */
    public function view()
    {
        $view = apply_filters('cgit_member_network_default_view', $this->views[0]);

        if (isset($_GET[$this->key]) && in_array($_GET[$this->key], $this->views)) {
            $view = $_GET[$this->key];
        }

        return apply_filters('cgit_member_network_current_view', $view);
    }

    /**
     * Return view URL
     *
     * If the view is not specified, the current view will be used. The second
     * parameter adds additional query parameters to the URL.
     *
     * @param string $view
     * @param array $args
     * @return string
     */
    public function url($view = null, $args = [])
    {
        if (is_null($view)) {
            $view = $this->view();
        }

        $url = add_query_arg($this->key, $view, $this->baseUrl());

        if ($args) {
            $url = add_query_arg($args, $url);
        }

        return apply_filters('cgit_member_network_view_url', $url, $view);
    }

    /**
     * Return base URL
     *
     * @return string
     */
    private function baseUrl($base = null)
    {
        $url = home_url('/');
        $view = $this->view();

        if (is_string($base)) {
            $url = $base;
        } elseif (is_int($base) || $base instanceof \WP_Post) {
            $url = get_permalink($base);
        }

        return apply_filters('cgit_member_network_base_url', $url, $view);
    }
}
