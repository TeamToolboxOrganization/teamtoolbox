<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Client\Token\AccessToken;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MsTokenRepository")
 * @ORM\Table(name="mstoken")
 */
class MsToken
{
    public function createFromData(AccessToken $accessToken,\Microsoft\Graph\Model\User $userMS, User $user)
    {
        $this->setUser($user);
        $this->setAccessToken($accessToken->getToken());
        $this->setRefreshToken($accessToken->getRefreshToken());
        $this->setTokenExpires($accessToken->getExpires());
        $this->setUserName($userMS->getDisplayName());
        $this->setUserEmail($userMS->getMail());
        $this->setUserTimeZone($userMS->getMailboxSettings()->getTimeZone());
    }

    public function updateToken(AccessToken $accessToken){
        $this->setAccessToken($accessToken->getToken());
        $this->setRefreshToken($accessToken->getRefreshToken());
        $this->setTokenExpires($accessToken->getExpires());
    }

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="App\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="userId", nullable=true)
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(name="accessToken", type="string", nullable=false)
     */
    private $accessToken;

    /**
     * @var string
     * @ORM\Column(name="refreshToken", type="string", nullable=false)
     */
    private $refreshToken;

    /**
     * @var string
     * @ORM\Column(name="tokenExpires", type="string", nullable=false)
     */
    private $tokenExpires;
    /**
     * @var string
     * @ORM\Column(name="userName", type="string", nullable=false)
     */
    private $userName;
    /**
     * @var string
     * @ORM\Column(name="userEmail", type="string", nullable=false)
     */
    private $userEmail;
    /**
     * @var string
     * @ORM\Column(name="userTimeZone", type="string", nullable=false)
     */
    private $userTimeZone;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return MsToken
     */
    public function setId(int $id): MsToken
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return MsToken
     */
    public function setUser(User $user): MsToken
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     * @return MsToken
     */
    public function setAccessToken(string $accessToken): MsToken
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     * @return MsToken
     */
    public function setRefreshToken(string $refreshToken): MsToken
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getTokenExpires(): string
    {
        return $this->tokenExpires;
    }

    /**
     * @param string $tokenExpires
     * @return MsToken
     */
    public function setTokenExpires(string $tokenExpires): MsToken
    {
        $this->tokenExpires = $tokenExpires;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     * @return MsToken
     */
    public function setUserName(string $userName): MsToken
    {
        $this->userName = $userName;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    /**
     * @param string $userEmail
     * @return MsToken
     */
    public function setUserEmail(string $userEmail): MsToken
    {
        $this->userEmail = $userEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserTimeZone(): string
    {
        return $this->userTimeZone;
    }

    /**
     * @param string $userTimeZone
     * @return MsToken
     */
    public function setUserTimeZone(string $userTimeZone): MsToken
    {
        $this->userTimeZone = $userTimeZone;
        return $this;
    }

}