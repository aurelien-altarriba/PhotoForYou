<?php

namespace AA\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="aa_user")
 * @ORM\Entity(repositoryClass="AA\UserBundle\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class User extends BaseUser
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="credit", type="integer")
     */
    protected $credit = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="photographe", type="boolean")
     */
    protected $photographe = false;   

    /**
     * Set credit
     *
     * @param integer $credit
     *
     * @return User
     */
    public function setCredit($credit)
    {
        $this->credit = $credit;

        return $this;
    }

    /**
     * Add credit
     *
     * @param integer $credit
     *
     * @return User
     */
    public function addCredit($creditAjouté)
    {
        $this->credit = $this->credit + $creditAjouté;

        return $this;
    }

    /**
     * Get credit
     *
     * @return integer
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * Set photographe
     *
     * @param boolean $photographe
     *
     * @return User
     */
    public function setPhotographe($photographe)
    {
        $this->photographe = $photographe;

        return $this;
    }

    /**
     * Get photographe
     *
     * @return boolean
     */
    public function getPhotographe()
    {
        return $this->photographe;
    }


    /**
     * @ORM\PrePersist
     */
    public function defineRole()
    {
        // Selon la valeur de photographe(true/false), on lui donne le rôle correspondant
        if($this->getPhotographe() === true){
            $this->roles = array('ROLE_PHOTOGRAPHE');
        } else {
            $this->roles = array('ROLE_CLIENT');
        }
    }
}
