<?php

namespace Garlic\Gateway\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StatusHistoryRepository")
 * @ORM\Table(name="gateway_service_status_history")
 */
class StatusHistory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Service", inversedBy="statusHistory")
     * @ORM\JoinColumn(nullable=false)
     */
    private $service;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $changedAt;
    
    /**
     * Get id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get service
     *
     * @return Service
     */
    public function getService(): Service
    {
        return $this->service;
    }
    
    /**
     * Set service
     *
     * @param Service $service
     * @return StatusHistory
     */
    public function setService(Service $service): self
    {
        $this->service = $service;

        return $this;
    }
    
    /**
     * Get status
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }
    
    /**
     * Set status
     *
     * @param int $status
     * @return StatusHistory
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }
    
    /**
     * Get changed date
     *
     * @return \DateTimeInterface
     */
    public function getChangedAt(): \DateTimeInterface
    {
        return $this->changedAt;
    }
    
    /**
     * Set changed date
     *
     * @param \DateTimeInterface $changedAt
     * @return StatusHistory
     */
    public function setChangedAt(\DateTimeInterface $changedAt): self
    {
        $this->changedAt = $changedAt;

        return $this;
    }
}
