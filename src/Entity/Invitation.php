<?php


namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;



/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\InvitationRepository")
 * @ORM\Table(name="invitation")
 */
class Invitation
{

    const RECEIVER_ACCEPT_MSG = "Only Receiver can Accept invitation";
    const RECEIVER_REJECT_MSG = "Only Receiver can Reject invitation";
    const SENDER_CANCEL_MSG = "Only Sender can Cancel invitation";
    const ACCEPT_CANCELLED_MSG = "You Can't accept a cancelled invitation";
    const REJECT_CANCELLED_MSG = "You Can't reject a cancelled invitation";


    const STATUS_WAITING = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_CANCELLED = 3;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="sender", referencedColumnName="email")
     */
    private $sender;

    /**
     * @return User
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param User $sender
     */
    public function setSender($sender): void
    {
        $this->sender = $sender;
    }
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="receiver", referencedColumnName="email")
     */
    private $receiver;
    /**
     * @return User
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @param User $receiver
     */
    public function setReceiver($receiver): void
    {
        $this->receiver = $receiver;
    }
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $message;

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }
    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
    /**
     * @return string
     */
    public function getStatusAsString()
    {
        $statuses = ["Waiting","Accepted","Rejected","Cancelled"];
        return $statuses[$this->status];
    }
    /**
     * @param int $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    public function __construct($sender,$receiver,$message,$status = Invitation::STATUS_WAITING)
    {
        $this->setSender($sender);
        $this->setReceiver($receiver);
        $this->setMessage($message);
        $this->setStatus($status);

    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


}