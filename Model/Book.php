<?php

class Book
{
    private ?int $ID;
    private ?string $name;
    private ?string $location;
    private ?string $eventDate;
    private ?string $distance;
    private ?string $status;
    private ?int $slots;
    private ?string $category;
    private ?string $image;
    private ?string $organizer;
    private ?float $price;

    public function __construct(
        ?int $ID = null,
        ?string $name = null,
        ?string $location = null,
        ?string $eventDate = null,
        ?string $distance = null,
        ?string $status = null,
        ?int $slots = null,
        ?string $category = null,
        ?string $image = null,
        ?string $organizer = null,
        ?float $price = null
    ) {
        $this->ID = $ID;
        $this->name = $name;
        $this->location = $location;
        $this->eventDate = $eventDate;
        $this->distance = $distance;
        $this->status = $status;
        $this->slots = $slots;
        $this->category = $category;
        $this->image = $image;
        $this->organizer = $organizer;
        $this->price = $price;
    }

    public function getID(): ?int
    {
        return $this->ID;
    }

    public function setID(?int $ID): void
    {
        $this->ID = $ID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
    }

    public function getEventDate(): ?string
    {
        return $this->eventDate;
    }

    public function setEventDate(?string $eventDate): void
    {
        $this->eventDate = $eventDate;
    }

    public function getDistance(): ?string
    {
        return $this->distance;
    }

    public function setDistance(?string $distance): void
    {
        $this->distance = $distance;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getSlots(): ?int
    {
        return $this->slots;
    }

    public function setSlots(?int $slots): void
    {
        $this->slots = $slots;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getOrganizer(): ?string
    {
        return $this->organizer;
    }

    public function setOrganizer(?string $organizer): void
    {
        $this->organizer = $organizer;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    public function getTitle(): ?string
    {
        return $this->getName();
    }

    public function getAuthor(): ?string
    {
        return $this->getLocation();
    }

    public function getPublicationDate(): ?string
    {
        return $this->getEventDate();
    }

    public function getLangue(): ?string
    {
        return $this->getDistance();
    }

    public function getCopies(): ?int
    {
        return $this->getSlots();
    }
}
