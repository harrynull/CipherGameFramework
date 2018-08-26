<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\Column(type="array")
     */
    private $passed_levels = array();

    /**
     * @ORM\Column(type="integer")
     */
    private $score = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_admin = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Level", mappedBy="author_user")
     */
    private $levels;

    /**
     * @ORM\Column(type="array")
     */
    private $tried_levels = array();

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $display_name;

    public function __construct()
    {
        $this->levels = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPassedLevels(): ?array
    {
        return $this->passed_levels;
    }

    public function setPassedLevels(array $passed_levels): self
    {
        $this->passed_levels = $passed_levels;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getIsAdmin(): ?bool
    {
        return $this->is_admin;
    }

    public function setIsAdmin(?bool $is_admin): self
    {
        $this->is_admin = $is_admin;

        return $this;
    }

    /**
     * @return Collection|Level[]
     */
    public function getLevels(): Collection
    {
        return $this->levels;
    }

    public function addLevel(Level $level): self
    {
        if (!$this->levels->contains($level)) {
            $this->levels[] = $level;
            $level->setAuthorUser($this);
        }

        return $this;
    }

    public function removeLevel(Level $level): self
    {
        if ($this->levels->contains($level)) {
            $this->levels->removeElement($level);
            // set the owning side to null (unless already changed)
            if ($level->getAuthorUser() === $this) {
                $level->setAuthorUser(null);
            }
        }

        return $this;
    }

    public function getTriedLevels(): array
    {
        return $this->tried_levels;
    }

    public function setTriedLevels(array $tried_levels): self
    {
        $this->tried_levels = $tried_levels;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->display_name;
    }

    public function setDisplayName(?string $display_name): self
    {
        $this->display_name = $display_name;

        return $this;
    }
}
