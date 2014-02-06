<?php

namespace Intahwebz\TableMap;


class SQLLimitFragment extends SQLFragment {

    var $limit;

    function __construct($limit) {
        $this->limit = $limit;
    }
}


