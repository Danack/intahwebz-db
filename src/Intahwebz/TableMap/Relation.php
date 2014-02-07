<?php


namespace Intahwebz\TableMap;


class Relation {
    
    //http://docs.doctrine-project.org/en/2.0.x/reference/association-mapping.html
    
    //http://blog.anthonychaves.net/2009/12/16/bidirectional-relationships-owning-and-inverse-sides/
    //The owning-side of a bidirectional relationship is the one 
    //with the foreign key column on the table 

    const ONE_TO_ONE_UNIDIRECTIONAL = 'ONE_TO_ONE_UNIDIRECTIONAL'; //This is key in subordinate table
    const ONE_TO_ONE_BIDIRECTIONAL = 'ONE_TO_ONE_DIRECTIONAL'; //This has join
    const ONE_TO_ONE_SELF_REFERENCING = 'ONE_TO_ONE_SELF_REFERENCING'; //This is key same table?

    const ONE_TO_MANY_UNIDIRECTIONAL = 'ONE_TO_MANY_UNIDIRECTIONAL';
    const ONE_TO_MANY_BIDIRECTIONAL = 'ONE_TO_MANY_BIDIRECTIONAL';
    const ONE_TO_MANY_SELF_REFERENCING = 'ONE_TO_MANY_SELF_REFERENCING';

    const MANY_TO_ONE_UNIDIRECTION = 'MANY_TO_ONE_UNIDIRECTION';

    const MANY_TO_MANY_UNIDIRECTIONAL = 'MANY_TO_MANY_UNIDIRECTIONAL';
    const MANY_TO_MANY_BIDIRECTIONAL = 'MANY_TO_MANY_BIDIRECTIONAL';
    const MANY_TO_MANY_SELF_REFERENCING = 'MANY_TO_MANY_SELF_REFERENCING';
}

 