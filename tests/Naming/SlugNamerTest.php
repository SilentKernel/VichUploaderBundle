<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Doctrine\ORM\EntityRepository;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\SlugNamer;
use Vich\UploaderBundle\Tests\TestCase;

final class SlugNamerTest extends TestCase
{
    public function fileDataProvider(): array
    {
        return [
            // case -> original name, result pattern
            'non existing' => ['lala.jpeg', '/lala.jpeg/'],
            'existing' => ['làlà.mp3', '/lala-1.mp3/'],
        ];
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsAnUniqueName(string $originalName, string $pattern): void
    {
        $file = $this->getUploadedFileMock();
        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->willReturn($originalName)
        ;

        $entity = new \stdClass();

        $mapping = $this->getMockBuilder(PropertyMapping::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $mapping->expects($this->once())
            ->method('getFile')
            ->with($entity)
            ->willReturn($file)
        ;

        $repo = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->addMethods(['findOneBySlug'])
            ->getMock()
        ;
        $repo
            ->method('findOneBySlug')
            ->willReturnMap([['lala.jpeg', null], ['lala.mp3', new \StdClass()]])
        ;

        $namer = new SlugNamer($this->getTransliterator(), $repo, 'findOneBySlug');

        $this->assertRegExp($pattern, $namer->name($entity, $mapping));
    }
}
