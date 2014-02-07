<?php

namespace Intahwebz\TableMap\Fragment;


class SQLLimitFragment extends SQLFragment {

    var $limit;

    function __construct($limit) {
        $this->limit = $limit;
    }
}


