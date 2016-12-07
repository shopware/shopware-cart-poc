<?php

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Tax;

use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTax;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;

class CalculatedTaxCollectionTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_TAX_NAME = 'dummy-tax';

    public function testCollectionIsCountable()
    {
        $collection = new CalculatedTaxCollection();
        static::assertCount(0, $collection);
    }

    public function testCountReturnsCorrectValue()
    {
        $collection = new CalculatedTaxCollection([
            new CalculatedTax(10.99, 19, 1),
            new CalculatedTax(5.99, 14, 1),
            new CalculatedTax(1.99, 2, 1)
        ]);
        static::assertCount(3, $collection);
    }

    public function testAddFunctionAddsATax()
    {
        $collection = new CalculatedTaxCollection();
        $collection->add(
            new CalculatedTax(10.99, 19, 1)
        );

        static::assertEquals(
            new CalculatedTaxCollection([
                new CalculatedTax(10.99, 19, 1)
            ]),
            $collection
        );
    }

    public function testFillFunctionFillsTheCollection()
    {
        $collection = new CalculatedTaxCollection();
        $collection->fill([
            new CalculatedTax(5.50, 19, 1),
            new CalculatedTax(4.40, 18, 1),
            new CalculatedTax(3.30, 17, 1)
        ]);

        static::assertEquals(
            new CalculatedTaxCollection([
                new CalculatedTax(5.50, 19, 1),
                new CalculatedTax(4.40, 18, 1),
                new CalculatedTax(3.30, 17, 1)
            ]),
            $collection
        );
    }

    public function testTaxesCanBeGetterByTheirRate()
    {
        $collection = new CalculatedTaxCollection([
            new CalculatedTax(5.50, 19, 1),
            new CalculatedTax(4.40, 18, 1),
            new CalculatedTax(3.30, 17, 1)
        ]);
        static::assertEquals(
            new CalculatedTax(5.50, 19, 1),
            $collection->get(19)
        );
    }

    public function testTaxAmountCanBeSummed()
    {
        $collection = new CalculatedTaxCollection([
            new CalculatedTax(5.50, 19, 1),
            new CalculatedTax(4.40, 18, 1),
            new CalculatedTax(3.30, 17, 1)
        ]);
        static::assertSame(13.2, $collection->getAmount());
    }

    public function testIncrementFunctionAddsNewCalculatedTaxIfNotExist()
    {
        $collection = new CalculatedTaxCollection([
            new CalculatedTax(5.50, 19, 1)
        ]);

        $collection = $collection->merge(
            new CalculatedTaxCollection([new CalculatedTax(5.50, 18, 1)])
        );

        static::assertEquals(
            new CalculatedTaxCollection([
                new CalculatedTax(5.50, 19, 1),
                new CalculatedTax(5.50, 18, 1)
            ]),
            $collection
        );
    }

    public function testIncrementFunctionIncrementsExistingTaxes()
    {
        $collection = new CalculatedTaxCollection([
            new CalculatedTax(5.50, 19, 1)
        ]);
        $collection = $collection->merge(new CalculatedTaxCollection([
            new CalculatedTax(5.50, 19, 1)
        ]));

        static::assertEquals(
            new CalculatedTaxCollection([
                new CalculatedTax(11.00, 19, 2)
            ]),
            $collection
        );
    }

    public function testIncrementFunctionIncrementExistingTaxAmounts()
    {
        $collection = new CalculatedTaxCollection([
            new CalculatedTax(5.50, 19, 1),
            new CalculatedTax(5.50, 18, 1),
            new CalculatedTax(5.50, 17, 1)
        ]);

        $collection = $collection->merge(new CalculatedTaxCollection([
            new CalculatedTax(5.50, 19, 1),
            new CalculatedTax(5.50, 18, 1),
            new CalculatedTax(5.50, 17, 1)
        ]));

        static::assertEquals(
            new CalculatedTaxCollection([
                new CalculatedTax(11.00, 19, 2),
                new CalculatedTax(11.00, 18, 2),
                new CalculatedTax(11.00, 17, 2)
            ]),
            $collection
        );
    }

    public function testIncrementFunctionWorksWithEmptyCollection()
    {
        $collection = new CalculatedTaxCollection([
            new CalculatedTax(5.50, 19, 1),
            new CalculatedTax(5.50, 18, 1),
            new CalculatedTax(5.50, 17, 1)
        ]);
        $collection = $collection->merge(new CalculatedTaxCollection());

        static::assertEquals(
            new CalculatedTaxCollection([
                new CalculatedTax(5.50, 19, 1),
                new CalculatedTax(5.50, 18, 1),
                new CalculatedTax(5.50, 17, 1)
            ]),
            $collection
        );
    }

    public function testFillFunctionsFillsTheCollection()
    {
        $collection = new CalculatedTaxCollection();
        $collection->fill([
            new CalculatedTax(5.50, 19, 1),
            new CalculatedTax(5.50, 18, 1),
            new CalculatedTax(5.50, 17, 1)
        ]);

        static::assertEquals(new CalculatedTaxCollection([
            new CalculatedTax(5.50, 19, 1),
            new CalculatedTax(5.50, 18, 1),
            new CalculatedTax(5.50, 17, 1)
        ]), $collection);
    }

    public function testTaxesCanBeRemovedByRate()
    {
        $collection = new CalculatedTaxCollection([
            new CalculatedTax(5.50, 19, 1),
            new CalculatedTax(5.50, 18, 1),
            new CalculatedTax(5.50, 17, 1)
        ]);
        $collection->remove(19);

        static::assertEquals(new CalculatedTaxCollection([
            new CalculatedTax(5.50, 18, 1),
            new CalculatedTax(5.50, 17, 1)
        ]), $collection);
    }

    public function testClearFunctionRemovesAllTaxes()
    {
        $collection = new CalculatedTaxCollection([
            new CalculatedTax(5.50, 19, 1),
            new CalculatedTax(5.50, 18, 1),
            new CalculatedTax(5.50, 17, 1)
        ]);

        $collection->clear();
        static::assertEquals(new CalculatedTaxCollection(), $collection);
    }

    public function testGetOnEmptyCollection()
    {
        $collection = new CalculatedTaxCollection();
        static::assertNull($collection->get(19));
    }
}
