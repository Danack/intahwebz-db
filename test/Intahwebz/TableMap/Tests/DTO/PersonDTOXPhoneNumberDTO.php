<?php


namespace Intahwebz\TableMap\Tests\DTO;


class PersonDTOXPhoneNumberDTO {

    /**@var PersonDTO */
    public $personDTO;
    
    /** @var PhoneNumberDTO[] */
    public $phoneNumberDTOCollection;

    function __construct(PersonDTO $personDTO, array $phoneNumberDTOCollection) {
        $this->personDTO = $personDTO;
        $this->phoneNumberDTOCollection = $phoneNumberDTOCollection;
    }
}

 