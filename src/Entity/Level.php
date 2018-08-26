<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LevelRepository")
 */
class Level
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
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $unlock_score;

    /**
     * @ORM\Column(type="integer")
     */
    private $score;

    /**
     * @ORM\Column(type="integer")
     */
    private $tried_num = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $passed_num = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $total_rating = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $rated_num = 0;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $tags;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $answer;

    /**
     * @ORM\Column(type="boolean")
     */
    private $under_review;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="levels")
     */
    private $author_user;

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUnlockScore(): ?int
    {
        return $this->unlock_score;
    }

    public function setUnlockScore(int $unlock_score): self
    {
        $this->unlock_score = $unlock_score;

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

    public function getTriedNum(): ?int
    {
        return $this->tried_num;
    }

    public function setTriedNum(int $tried_num): self
    {
        $this->tried_num = $tried_num;

        return $this;
    }

    public function getTotalRating(): ?int
    {
        return $this->total_rating;
    }

    public function setTotalRating(int $total_rating): self
    {
        $this->total_rating = $total_rating;

        return $this;
    }

    public function getRatedNum(): ?int
    {
        return $this->rated_num;
    }

    public function setRatedNum(int $rated_num): self
    {
        $this->rated_num = $rated_num;

        return $this;
    }

    public function getPassedNum(): ?int
    {
        return $this->passed_num;
    }

    public function setPassedNum(int $passed_num): self
    {
        $this->passed_num = $passed_num;

        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getUnderReview(): ?bool
    {
        return $this->under_review;
    }

    public function setUnderReview(bool $under_review): self
    {
        $this->under_review = $under_review;

        return $this;
    }

    public function getAuthorUser(): ?User
    {
        return $this->author_user;
    }

    public function setAuthorUser(?User $author_user): self
    {
        $this->author_user = $author_user;

        return $this;
    }
}
