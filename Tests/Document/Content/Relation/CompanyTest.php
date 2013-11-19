<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Document\Content\Relation;

use Integrated\Bundle\ContentBundle\Document\Content\Relation\Company;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class CompanyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Company
     */
    private $company;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->company = new Company();
    }

    /**
     * Company should implement ContentInterface
     */
    public function testInstanceOfContentInterface()
    {
        $this->assertInstanceOf('Integrated\Common\Content\ContentInterface', $this->company);
    }

    /**
     * Company should extend Content
     */
    public function testInstanceOfContent()
    {
        $this->assertInstanceOf('Integrated\Bundle\ContentBundle\Document\Content\Content', $this->company);
    }

    /**
     * Company should extend Relation
     */
    public function testInstanceOfRelation()
    {
        $this->assertInstanceOf('Integrated\Bundle\ContentBundle\Document\Content\Relation\Relation', $this->company);
    }

    /**
     * Test get- and setAccountnumber function
     */
    public function testGetAndSetAccountnumberFunction()
    {
        $accountnumber = 'accountnumber';
        $this->assertEquals($accountnumber, $this->company->setAccountnumber($accountnumber)->getAccountnumber());
    }

    /**
     * Test get- and setDescription function
     */
    public function testGetAndSetDescriptionFunction()
    {
        $description = array('nl' => 'Omschrijving', 'en' => 'Description');
        $this->assertEquals($description, $this->company->setDescription($description)->getDescription());
    }

    /**
     * Test get- and setPhonenumbers function
     */
    public function testGetAndSetPhonenumbersFunction()
    {
        $phonenumbers = array('0123456789', '9876543210');
        $this->assertSame($phonenumbers, $this->company->setPhonenumbers($phonenumbers)->getPhonenumbers());
    }

    /**
     * Test get- and setEmail function
     */
    public function testGetAndSetEmailFunction()
    {
        $email = 'email';
        $this->assertEquals($email, $this->company->setEmail($email)->getEmail());
    }

    /**
     * Test get- and setAddresses function
     */
    public function testGetAndSetAddressesFunction()
    {
        $addresses = array(
            $this->getMock('Integrated\Bundle\ContentBundle\Document\Content\Embedded\Address')
        );
        $this->assertSame($addresses, $this->company->setAddresses($addresses)->getAddresses());
    }

    /**
     * Test get- and setName function
     */
    public function testGetAndSetNameFunction()
    {
        $name = 'name';
        $this->assertEquals($name, $this->company->setName($name)->getName());
    }

    /**
     * Test get- and setLogo function
     */
    public function testGetAndSetLogoFunction()
    {
        /* @var $logo \Integrated\Bundle\ContentBundle\Document\Content\File | \\PHPUnit_Framework_MockObject_MockObject */
        $logo = $this->getMock('Integrated\Bundle\ContentBundle\Document\Content\File');
        $this->assertSame($logo, $this->company->setLogo($logo)->getLogo());
    }
}