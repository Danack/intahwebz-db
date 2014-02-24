<?php


namespace Intahwebz\TableMap\Tests\DTO;


class PersonDTOXPhoneNumberDTO {

    /**@var PersonDTO */
    public $personDTO;
    
    /** @var PhoneNumberDTO[] */
    public $phoneNumberDTOCollection;

    function __construct(PersonDTO $personDTO = null, array $phoneNumberDTOCollection = array()) {
        $this->personDTO = $personDTO;
        $this->phoneNumberDTOCollection = $phoneNumberDTOCollection;
    }
    
    function addPerson(PersonDTO $personDTO) {
        $this->personDTO = $personDTO;
    }

    function addPhoneNumber(PhoneNumberDTO $phoneNumberDTO) {
        $this->phoneNumberDTOCollection[] = $phoneNumberDTO;
    }
    
    function initFromResultSet($rows) {
        foreach($rows as $row) {
            $newPersonDTO = castToObject('\Intahwebz\TableMap\Tests\DTO\PersonDTO', $row);
            if ($this->personDTO == null) {
                $this->personDTO = $newPersonDTO;
            }

            $newPhoneNumberDTO = castToObject('\Intahwebz\TableMap\Tests\DTO\PhoneNumberDTO', $row);
            if (in_array($newPhoneNumberDTO, $this->phoneNumberDTOCollection) == false) {
                $this->phoneNumberDTOCollection[$newPhoneNumberDTO->phoneNumberID] = $newPhoneNumberDTO;
            }
        }
    }
}

 