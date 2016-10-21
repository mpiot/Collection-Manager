<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TeamRequest
 *
 * @ORM\Table(name="team_request")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TeamRequestRepository")
 */
class TeamRequest
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Team", inversedBy="teamRequests")
     */
    private $team;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="teamRequests")
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="requestDate", type="datetime")
     */
    private $requestDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="answerDate", type="datetime", nullable=true)
     */
    private $answerDate;

    /**
     * @var string
     *
     * @ORM\Column(name="answer", type="string", length=255, nullable=true)
     */
    private $answer;

    public function __construct()
    {
        $this->requestDate = new \DateTime();
        $this->answer = 'requested';
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set team
     *
     * @param string $team
     *
     * @return TeamRequest
     */
    public function setTeam(Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team
     *
     * @return string
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set user
     *
     * @param string $user
     *
     * @return TeamRequest
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set requestDate
     *
     * @param \DateTime $requestDate
     *
     * @return TeamRequest
     */
    public function setRequestDate($requestDate)
    {
        $this->requestDate = $requestDate;

        return $this;
    }

    /**
     * Get requestDate
     *
     * @return \DateTime
     */
    public function getRequestDate()
    {
        return $this->requestDate;
    }

    /**
     * Set answerDate
     *
     * @param \DateTime $answerDate
     *
     * @return TeamRequest
     */
    public function setAnswerDate($answerDate)
    {
        $this->answerDate = $answerDate;

        return $this;
    }

    /**
     * Get answerDate
     *
     * @return \DateTime
     */
    public function getAnswerDate()
    {
        return $this->answerDate;
    }

    /**
     * Set answer
     *
     * @param string $answer
     *
     * @return TeamRequest
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer
     *
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
    }
}
