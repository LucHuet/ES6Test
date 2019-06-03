<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * RepLog
 *
 * @ORM\Table(name="rep_log")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RepLogRepository")
 */
class RepLog
{
    const ITEM_LABEL_PREFIX = 'drinkable_thing.';

    const PRICE_CHOCOLATE = 5;

    private static $thingsYouCanDrink = array(
        'coffee' => '2',
        'chocolate' => self::PRICE_CHOCOLATE,
        'tea' => '3',
        'water' => '1',
    );

    /**
     * @var integer
     *
     * @Serializer\Groups({"Default"})
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @Serializer\Groups({"Default"})
     * @ORM\Column(name="reps", type="integer")
     * @Assert\NotBlank(message="How many times did you drink this?")
     * @Assert\GreaterThan(value=0, message="You can certainly drinked more than just 0!")
     */
    private $reps;

    /**
     * @var string
     *
     * @Serializer\Groups({"Default"})
     * @ORM\Column(name="item", type="string", length=50)
     * @Assert\NotBlank(message="What did you drink?")
     */
    private $item;

    /**
     * @var float
     *
     * @Serializer\Groups({"Default"})
     * @ORM\Column(name="totalLitersDrinked", type="float")
     */
    private $totalLitersDrinked;

    /**
     * The user who drinked these items
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set reps
     *
     * @param integer $reps
     * @return RepLog
     */
    public function setReps($reps)
    {
        $this->reps = $reps;

        $this->calculateTotalDrinked();

        return $this;
    }

    /**
     * Get reps
     *
     * @return integer
     */
    public function getReps()
    {
        return $this->reps;
    }

    /**
     * Set item
     *
     * @param string $item
     * @return RepLog
     */
    public function setItem($item)
    {
        if (!isset(self::$thingsYouCanDrink[$item])) {
            throw new \InvalidArgumentException(sprintf('You can\'t drink a "%s"!', $item));
        }

        $this->item = $item;
        $this->calculateTotalDrinked();

        return $this;
    }

    /**
     * Get item
     *
     * @return string
     */
    public function getItem()
    {
        return $this->item;
    }

    public function getItemLabel()
    {
        return self::ITEM_LABEL_PREFIX.$this->getItem();
    }

    /**
     * Get totalLitersDrinked
     *
     * @return float
     */
    public function getTotalLitersDrinked()
    {
        return $this->totalLitersDrinked;
    }

    /**
     * Returns an array that an be used in a form drop down
     *
     * @return array
     */
    public static function getThingsYouCanDrinkChoices()
    {
        $things = array_keys(self::$thingsYouCanDrink);
        $choices = array();
        foreach ($things as $thingKey) {
            $choices[self::ITEM_LABEL_PREFIX.$thingKey] = $thingKey;
        }

        return $choices;
    }

    /**
     * Calculates the total liters drinked and sets it on the property
     */
    private function calculateTotalDrinked()
    {
        if (!$this->getItem()) {
            return;
        }

        $liters = self::$thingsYouCanDrink[$this->getItem()];
        $totalLiters = $liters * $this->getReps();

        $this->totalLitersDrinked = $totalLiters;
    }

    /**
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \AppBundle\Entity\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}
