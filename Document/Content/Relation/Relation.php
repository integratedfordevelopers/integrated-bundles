<?php

/*
* This file is part of the Integrated package.
*
* (c) e-Active B.V. <integrated@e-active.nl>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Integrated\Bundle\ContentBundle\Document\Content\Relation;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Integrated\Common\ContentType\Mapping\Annotations as Type;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Address;

/**
 * Abstract class for Relations
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 *
 * @ODM\MappedSuperclass
 */
class Relation extends Content
{
    /**
     * @var string
     * @ODM\String
     * @Type\Field
     */
    protected $accountnumber;

    /**
     * @var array
     * @ODM\Hash
     * @Type\Field(type="translatable_textarea")
     */
    protected $description = array();

    /**
     * @var array
     * @ODM\Hash
     */
    protected $phonenumbers = array();

    /**
     * @var string
     * @ODM\String
     * @Type\Field(type="email")
     */
    protected $email;

    /**
     * @var Address[]
     * @ODM\EmbedMany(targetDocument="Integrated\Bundle\ContentBundle\Document\Content\Embedded\Address", strategy="set")
     */
    protected $addresses = array();

    /**
     * Get the accountnumber of the document
     *
     * @return string
     */
    public function getAccountnumber()
    {
        return $this->accountnumber;
    }

    /**
     * Set the accountnumber of the document
     *
     * @param string $accountnumber
     * @return $this
     */
    public function setAccountnumber($accountnumber)
    {
        $this->accountnumber = $accountnumber;
        return $this;
    }

    /**
     * Get the description of the document
     *
     * @return array
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the description of the document
     *
     * @param array $description
     * @return $this
     */
    public function setDescription(array $description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the phonenumbers of the document
     *
     * @return array
     */
    public function getPhonenumbers()
    {
        return $this->phonenumbers;
    }

    /**
     * Set the phonenumbers of the document
     *
     * @param array $phonenumbers
     * @return $this
     */
    public function setPhonenumbers(array $phonenumbers)
    {
        $this->phonenumbers = $phonenumbers;
        return $this;
    }

    /**
     * Get the email of the document
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the email of the document
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get the addresses of the document
     *
     * @return Address[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * Set the addresses of the document
     *
     * @param array $addresses
     * @return $this
     */
    public function setAddresses(array $addresses)
    {
        $this->addresses = $addresses;
        return $this;
    }
}