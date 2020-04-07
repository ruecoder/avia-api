<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookingRepository")
 */
class Booking
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="integer", unique=false, nullable=false)
     */
    private $flight_id;

    /**
     * @ManyToOne(targetEntity="App\Entity\Flights")
     * @JoinColumn(name="flight_id", referencedColumnName="id")
     */
    private $flight;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     */
    private $seat;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setFlight(?Flights $flight): self
    {
        $this->flight = $flight;
        return $this;
    }

    public function getFlight(): ?Flights
    {
        if (isset($this->flight) && $this->flight_id != 0) {
            return $this->flight;
        } else {
            return null;
        }
    }

    public function getFlightId(): ?int
    {
        return $this->flight_id;
    }

    public function setFlightId(int $flight_id): self
    {
        $this->flight_id = $flight_id;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSeat(): ?int
    {
        return $this->seat;
    }

    public function setSeat(int $seat): self
    {
        $this->seat = $seat;

        return $this;
    }
}
