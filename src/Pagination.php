<?php

namespace Virtualorz\Faq;

use Config;
use Route;

trait Pagination {

    public function getPagination($total, $ipp = null, $pop = null, $curr = null, $rn = null) {

        //
        $totalItems = intval($total);
        if ($totalItems < 0) {
            $totalItems = 0;
        }

        //
        $itemsPerPage = Config::get('pagination.items');
        if (!is_null($ipp)) {
            $itemsPerPage = intval($ipp);
        }
        if ($itemsPerPage <= 0) {
            $itemsPerPage = 1;
        }

        //
        $pagesOfPager = Config::get('pagination.pages');
        if (!is_null($pop)) {
            $pagesOfPager = intval($pop);
        }
        if ($pagesOfPager <= 0) {
            $pagesOfPager = 1;
        }

        //
        $currentPage = intval(Route::input('optional.page', 1));
        if (!is_null($curr)) {
            $currentPage = intval($curr);
        }
        if ($currentPage <= 0) {
            $currentPage = 1;
        }

        //
        $routeName = Route::currentRouteName();
        if (!is_null($rn)) {
            $routeName = $rn;
        } else {
            if (!ends_with($routeName, '.index')) {
                $tmpRouteName = trim($routeName . '.index', '.');
                if (Route::has($tmpRouteName)) {
                    $routeName = $tmpRouteName;
                }
            }
        }



        $pagination = array();
        $pagination['curr'] = $currentPage;
        $pagination['total'] = $totalItems;
        $pagination['first'] = 1;
        $pagination['last'] = $totalItems <= 0 ? 1 : ceil($totalItems / $itemsPerPage);
        $pagination['prev'] = ($currentPage - 1) <= 0 ? 1 : ($currentPage - 1);
        $pagination['next'] = ($currentPage + 1) > $pagination['last'] ? $pagination['last'] : ($currentPage + 1);
        $pagination['start'] = ($currentPage - floor($pagesOfPager / 2)) < 1 ? 1 : ($currentPage - floor($pagesOfPager / 2));
        $pagination['end'] = $pagination['start'] + $pagesOfPager - 1;
        if ($pagination['end'] > $pagination['last']) {
            $diff = $pagination['end'] - $pagination['last'];
            $pagination['end'] = $pagination['last'];
            $pagination['start'] = ($pagination['start'] - $diff) < 1 ? 1 : $pagination['start'] - $diff;
        }
        $pagination['index_start'] = ($pagination['curr'] - 1) * $itemsPerPage + 1;
        $pagination['index_end'] = $pagination['index_start'] + $itemsPerPage - 1;
        if ($totalItems <= 0) {
            $pagination['index_start'] = $pagination['index_end'] = 0;
        } else if ($totalItems < $pagination['index_end']) {
            $pagination['index_end'] = $totalItems;
        }
        $pagination['items'] = $itemsPerPage;
        $pagination['pages'] = $pagesOfPager;
        $pagination['route_name'] = $routeName;
        $pagination['page_name'] = 'page';
        $pagination['route_param'] = [];
        $pagination['route_option_param'] = [];

        return $pagination;
    }

}
