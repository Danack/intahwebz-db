<?php


namespace Intahwebz\TableMap\Tests\DTO;


class MockContentDTOXMockNoteDTO {


    /**
     * @var MockContentDTO
     */
    public $mockContentDTO;
    
    /** @var MockNoteDTO */
    public $mockNoteDTO;


    function __construct(MockContentDTO $mockContentDTO, MockNoteDTO $mockNoteDTO) {
        $this->mockContentDTO = $mockContentDTO;
        $this->mockNoteDTO = $mockNoteDTO;
    }

}

 