<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Document\ContentType;

use Doctrine\Common\Collections\ArrayCollection;
use \Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ContentTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentType
     */
    private $contentType;

    protected function setUp()
    {
        $this->contentType = new ContentType();
    }

    /**
     * Test the create functions
     */
    public function testCreate()
    {
        // Mock ContentInterface
        $content = $this->getMock('Integrated\Common\Content\ContentInterface');

        // Set class
        $class = get_class($content);
        $this->contentType->setClass($class);

        // Assert
        $this->assertInstanceOf($class, $this->contentType->create());
    }

    /**
     * Test get- and setId function
     */
    public function testGetAndSetIdFunction()
    {
        $id = 'abc123';
        $this->assertEquals($id, $this->contentType->setId($id)->getId());
    }

    /**
     * Test get- and setClass function
     */
    public function testGetAndSetClassFunction()
    {
        $class = 'HenkDeVries';
        $this->assertEquals($class, $this->contentType->setClass($class)->getClass());
    }

    /**
     * Test get- and setName function
     */
    public function testGetAndSetNameFunction()
    {
        $name = 'Henk de Vries';
        $this->assertEquals($name, $this->contentType->setName($name)->getName());

        // After name edit, type should stay the same
        $type = $this->contentType->getType();
        $this->contentType->setName('Wim');
        $this->assertEquals($type, $this->contentType->getType());
    }

    /**
     * Test get- and setType function
     */
    public function testGetAndSetTypeFunction()
    {
        $type = 'Familie De Vries';
        $this->assertEquals($type, $this->contentType->setType($type)->getType());
    }

    /**
     * Test get- and setFields function
     */
    public function testGetAndSetFieldsFunction()
    {
        // Mock fields
        $field1 = $this->getMockClass('Integrated\Common\ContentType\ContentTypeFieldInterface');
        $field2 = $this->getMockClass('Integrated\Common\ContentType\ContentTypeFieldInterface');

        $fields = array(
            $field1,
            $field2
        );

        // Assert
        $this->assertSame($fields, $this->contentType->setFields($fields)->getFields());
    }

    /**
     * Test getField function
     */
    public function testGetFieldFunction()
    {
        // Mock fields
        $field = $this->getMock('Integrated\Common\ContentType\ContentTypeFieldInterface');
        $field->expects($this->exactly(2))
            ->method('getName')
            ->will($this->returnValue('henk'));

        $this->contentType->setFields(array($field));

        // Asserts
        $this->assertSame($field, $this->contentType->getField('henk'));
        $this->assertNull($this->contentType->getField('henkie'));
    }

    /**
     * Test hasField function
     */
    public function testHasFieldFunction()
    {
        // Mock fields
        $field = $this->getMock('Integrated\Common\ContentType\ContentTypeFieldInterface');
        $field->expects($this->exactly(2))
            ->method('getName')
            ->will($this->returnValue('henk'));

        $this->contentType->setFields(array($field));

        // Asserts
        $this->assertTrue($this->contentType->hasField('henk'));
        $this->assertFalse($this->contentType->hasField('henkie'));
    }

    /**
     * Test get- and setRelations function
     */
    public function testGetAndSetRelationsFunction()
    {
        // Mock fields
        $relation1 = $this->getMockClass('Integrated\Common\ContentType\ContentTypeRelationInterface');
        $relation2 = $this->getMockClass('Integrated\Common\ContentType\ContentTypeRelationInterface');

        $relations = new ArrayCollection(
            array(
                $relation1,
                $relation2
            )
        );

        // Assert
        $this->assertSame($relations, $this->contentType->setRelations($relations)->getRelations());
    }

    /**
     * Test addRelation function with new relation
     */
    public function testAddRelationFunctionWithNewRelation()
    {
        /** @var $newRelation \Integrated\Common\ContentType\ContentTypeRelationInterface | \PHPUnit_Framework_MockObject_MockObject */
        $newRelation = $this->getMock('Integrated\Common\ContentType\ContentTypeRelationInterface');

        // Asserts
        $this->assertTrue($this->contentType->addRelation($newRelation));
        $this->assertCount(1, $this->contentType->getRelations());
    }


    /**
     * Test addRelation function with duplicate relation
     */
    public function testAddRelationFunctionWithDuplicateRelation()
    {
        /** @var $newRelation \Integrated\Common\ContentType\ContentTypeRelationInterface | \PHPUnit_Framework_MockObject_MockObject */
        $newRelation = $this->getMock('Integrated\Common\ContentType\ContentTypeRelationInterface');

        // Stub getId
        $newRelation->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue('id1'));

        $duplicateRelation = $newRelation;

        // Asserts
        $this->assertTrue($this->contentType->addRelation($newRelation));
        $this->assertFalse($this->contentType->addRelation($duplicateRelation));
        $this->assertCount(1, $this->contentType->getRelations());
    }

    /**
     * Test getRelation function with existing relation
     */
    public function testGetRelationFunctionWithExistingRelation()
    {
        /** @var $relation \Integrated\Common\ContentType\ContentTypeRelationInterface | \PHPUnit_Framework_MockObject_MockObject */
        $relation = $this->getMock('Integrated\Common\ContentType\ContentTypeRelationInterface');

        // Stub getId
        $relation->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('id1'));

        // Asserts
        $this->assertTrue($this->contentType->addRelation($relation));
        $this->assertSame($relation, $this->contentType->getRelation('id1'));
    }

    /**
     * Test getRelation function with not existing relation
     */
    public function testGetRelationFunctionWithNotExistingRelation()
    {
        /** @var $relation \Integrated\Common\ContentType\ContentTypeRelationInterface | \PHPUnit_Framework_MockObject_MockObject */
        $relation = $this->getMock('Integrated\Common\ContentType\ContentTypeRelationInterface');

        // Stub getId
        $relation->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('id1'));

        // Asserts
        $this->assertTrue($this->contentType->addRelation($relation));
        $this->assertFalse($this->contentType->getRelation('id2'));
    }

    /**
     * Test removeRelation function with existing relation
     */
    public function testRemoveRelationFunctionWithExistingRelation()
    {
        /** @var $existingRelation \Integrated\Common\ContentType\ContentTypeRelationInterface | \PHPUnit_Framework_MockObject_MockObject */
        $existingRelation = $this->getMock('Integrated\Common\ContentType\ContentTypeRelationInterface');

        // Stub getId
        $existingRelation->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('id1'));

        // Asserts
        $this->assertTrue($this->contentType->addRelation($existingRelation));
        $this->assertCount(1, $this->contentType->getRelations());

        $this->assertTrue($this->contentType->removeRelation($existingRelation));
        $this->assertCount(0, $this->contentType->getRelations());
    }

    /**
     * Test removeRelation function with not existing relation
     */
    public function testRemoveRelationFunctionWithNotExistingRelation()
    {
        /** @var $existingRelation \Integrated\Common\ContentType\ContentTypeRelationInterface | \PHPUnit_Framework_MockObject_MockObject */
        $existingRelation = $this->getMock('Integrated\Common\ContentType\ContentTypeRelationInterface');

        // Stub getId
        $existingRelation->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('id1'));

        /** @var $notExistingRelation \Integrated\Common\ContentType\ContentTypeRelationInterface | \PHPUnit_Framework_MockObject_MockObject */
        $notExistingRelation = $this->getMock('Integrated\Common\ContentType\ContentTypeRelationInterface');

        // Asserts
        $this->assertTrue($this->contentType->addRelation($existingRelation));
        $this->assertCount(1, $this->contentType->getRelations());

        $this->assertFalse($this->contentType->removeRelation($notExistingRelation));
        $this->assertCount(1, $this->contentType->getRelations());
    }

    /**
     * Test get- and setCreatedAt function
     */
    public function testGetAndSetCreatedAtFunction()
    {
        $createdAt = new \DateTime();
        $this->assertSame($createdAt, $this->contentType->setCreatedAt($createdAt)->getCreatedAt());
    }
}