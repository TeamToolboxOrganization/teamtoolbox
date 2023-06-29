<?php

namespace App\Entity;

use Microsoft\Graph\Model\FreeBusyStatus;
use Microsoft\Graph\Model\ResponseStatus;

class MsEventDTO
{
    private string $id;

    public \DateTimeImmutable $start;

    public \DateTimeImmutable $end;

    public \DateInterval $timeDiff;

    public string $organizer;

    public string $subject;

    public FreeBusyStatus $showAs;

    public ResponseStatus $responseStatus;

    public array $categories = array();

    /**
     * @param \DateTimeImmutable $start
     * @param \DateTimeImmutable $end
     * @param \DateInterval $timeDiff
     */
    public function __construct(\DateTimeImmutable $start, \DateTimeImmutable $end, \DateInterval $timeDiff, ?string $subject, string $organizer)
    {
        $this->start = $start;
        $this->end = $end;
        $this->timeDiff = $timeDiff;
        if($subject == null){
            $this->subject = "";
        } else {
            $this->subject = $subject;
        }
        $this->organizer = $organizer;
    }


    /**
     * @return \DateTimeImmutable
     */
    public function getStart(): \DateTimeImmutable
    {
        return $this->start;
    }

    /**
     * @param \DateTimeImmutable $start
     * @return MsEventDTO
     */
    public function setStart(\DateTimeImmutable $start): MsEventDTO
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getEnd(): \DateTimeImmutable
    {
        return $this->end;
    }

    /**
     * @param \DateTimeImmutable $end
     * @return MsEventDTO
     */
    public function setEnd(\DateTimeImmutable $end): MsEventDTO
    {
        $this->end = $end;
        return $this;
    }

    /**
     * @return \DateInterval
     */
    public function getTimeDiff(): \DateInterval
    {
        return $this->timeDiff;
    }

    /**
     * @param \DateInterval $timeDiff
     * @return MsEventDTO
     */
    public function setTimeDiff(\DateInterval $timeDiff): MsEventDTO
    {
        $this->timeDiff = $timeDiff;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizer(): string
    {
        return $this->organizer;
    }

    /**
     * @param string $organizer
     * @return MsEventDTO
     */
    public function setOrganizer(string $organizer): MsEventDTO
    {
        $this->organizer = $organizer;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return MsEventDTO
     */
    public function setSubject(string $subject): MsEventDTO
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     * @return MsEventDTO
     */
    public function setCategories(array $categories): MsEventDTO
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @param string $category
     * @return MsEventDTO
     */
    public function addCategory(string $category){
        $this->categories[] = $category;
        return $this;
    }


    /**
     * @return FreeBusyStatus
     */
    public function getShowAs(): FreeBusyStatus
    {
        return $this->showAs;
    }

    /**
     * @param FreeBusyStatus $showAs
     * @return MsEventDTO
     */
    public function setShowAs(FreeBusyStatus $showAs): MsEventDTO
    {
        $this->showAs = $showAs;
        return $this;
    }
    /**
     * @return ResponseStatus
     */
    public function getResponseStatus(): ResponseStatus
    {
        return $this->responseStatus;
    }

    /**
     * @param ResponseStatus $responseStatus
     * @return MsEventDTO
     */
    public function setResponseStatus(ResponseStatus $responseStatus): MsEventDTO
    {
        $this->responseStatus = $responseStatus;
        return $this;
    }

    /**
     * @return String
     */
    public function getId(): String {
        return $this->id;
    }

    /**
     * @param String $id
     * @return MsEventDTO
     */
    public function setId(String $id): MsEventDTO
    {
        $this->id = $id;
        return $this;
    }
}