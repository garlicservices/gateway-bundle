<?php

namespace Garlic\Gateway\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ServiceRepository")
 * @ORM\Table(name="gateway_service")
 * @ORM\HasLifecycleCallbacks()
 */
class Service
{
    /** @var int Service statuses */
    const STATUS_NEW = 0;
    const STATUS_OK = 1;
    const STATUS_BUSY = 2;
    const STATUS_ERROR = 3;
    const STATUS_OFFLINE = 4;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastHealthCheckAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="float")
     */
    private $lastTiming;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StatusHistory", mappedBy="service", orphanRemoval=true, cascade={"persist"})
     */
    private $statusHistory;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * Service constructor.
     */
    public function __construct()
    {
        $this->enabled = true;
        $this->status = self::STATUS_NEW;
        $this->statusHistory = new ArrayCollection();
    }


    /**
     * Set id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param mixed $name
     * @return Service
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set enabled
     *
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set enabled
     *
     * @param mixed $enabled
     * @return Service
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get last health check date
     *
     * @return \DateTimeInterface|null
     */
    public function getLastHealthCheckAt(): ?\DateTimeInterface
    {
        return $this->lastHealthCheckAt;
    }

    /**
     * Set last health check date
     *
     * @param \DateTimeInterface|null $lastHealthCheckAt
     * @return Service
     */
    public function setLastHealthCheckAt(?\DateTimeInterface $lastHealthCheckAt): self
    {
        $this->lastHealthCheckAt = $lastHealthCheckAt;

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
     * @return Service
     */
    public function setStatus(int $status): self
    {
        if ($this->status != $status) {
            $this->addStatusHistory($status);
        }

        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastTiming()
    {
        return $this->lastTiming;
    }

    /**
     * Set last response timing
     *
     * @param float $lastTiming
     * @return Service
     */
    public function setLastTiming($lastTiming): self
    {
        $this->lastTiming = $lastTiming;

        return $this;
    }

    /**
     * Get status history
     *
     * @return Collection|StatusHistory[]
     */
    public function getStatusHistory(): Collection
    {
        return $this->statusHistory;
    }

    /**
     * Add new row of status history
     *
     * @param int $status
     * @return Service
     */
    public function addStatusHistory(int $status): self
    {
        $statusHistory = new StatusHistory();
        $statusHistory
            ->setStatus($status)
            ->setChangedAt(new \DateTime());

        $this->statusHistory[] = $statusHistory;
        $statusHistory->setService($this);

        return $this;
    }

    /**
     * Remove status history row
     *
     * @param StatusHistory $statusHistory
     * @return Service
     */
    public function removeStatusHistory(StatusHistory $statusHistory): self
    {
        if ($this->statusHistory->contains($statusHistory)) {
            $this->statusHistory->removeElement($statusHistory);
            if ($statusHistory->getService() === $this) {
                $statusHistory->setService(null);
            }
        }

        return $this;
    }

    /**
     * Save all the changes of Service status
     *
     * @ORM\PreUpdate
     * @param PreUpdateEventArgs $event
     */
    public function saveStatusHistory(PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField('status')) {
            $this->addStatusHistory($event->getNewValue('status'));
        }
    }

    /**
     * Set updatedAt and createdAt to current if value not specified time on prePersist event
     *
     * @ORM\PrePersist
     */
    public function preCreateChangeDate()
    {
        $this->createdAt = $this->createdAt ?: new \DateTime();
        $this->updatedAt = $this->updatedAt ?: new \DateTime();
    }

    /**
     * Set updatedAt to current time on preUpdate event
     *
     * @ORM\PreUpdate
     */
    public function preUpdateChangeDate()
    {
        $this->updatedAt = new \DateTime();
    }
}
