<?php


namespace Intahwebz\TableMap\Fragment;

use Intahwebz\TableMap\QueriedTable;

//http://jan.kneschke.de/projects/mysql/order-by-rand/

class SQLRandOrderFragment extends SQLFragment{

    var $tableMap;

    var $tableMap2;

    var $orderValue;

    function __construct(QueriedTable $tableMap, QueriedTable $tableMap2, $orderValue= 'ASC'){
        $this->tableMap = $tableMap;
        $this->tableMap2 = $tableMap2;
        $this->orderValue = $orderValue;
    }
}



//TODO - better rands
//> create table holes_map ( row_id int not NULL primary key, random_id int not null);
//> SET @id = 0;
//> INSERT INTO holes_map SELECT @id := @id + 1, id FROM holes;
//> select * from holes_map;


//SELECT name FROM holes
//  JOIN (SELECT r1.random_id
//         FROM holes_map AS r1
//         JOIN (SELECT (RAND() *
//                       (SELECT MAX(row_id)
//                         FROM holes_map)) AS row_id)
//               AS r2
//        WHERE r1.row_id >= r2.row_id
//        ORDER BY r1.row_id ASC
//        LIMIT 1) as rows ON (id = random_id);
