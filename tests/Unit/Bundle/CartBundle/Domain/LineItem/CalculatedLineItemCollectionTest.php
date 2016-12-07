<?php

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\LineItem;

use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItem;
use Shopware\Bundle\CartBundle\Domain\Price\Price;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCollection;
use Shopware\Bundle\CartBundle\Domain\Product\ProductProcessor;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Bundle\CartBundle\Domain\Voucher\CalculatedVoucher;
use Shopware\Bundle\CartBundle\Domain\Voucher\PercentageVoucherProcessor;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\ConfiguredGoodsItem;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\ConfiguredLineItem;

class CalculatedLineItemCollectionTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_TAX_NAME = 'dummy-tax';

    public function testCollectionIsCountable()
    {
        $collection = new CalculatedLineItemCollection();
        static::assertCount(0, $collection);
    }

    public function testCountReturnsCorrectValue()
    {
        $collection = new CalculatedLineItemCollection([
            new ConfiguredLineItem('A'),
            new ConfiguredLineItem('B'),
            new ConfiguredLineItem('C')
        ]);
        static::assertCount(3, $collection);
    }

    public function testCollectionOverwriteExistingIdentifierWithLastItem()
    {
        $collection = new CalculatedLineItemCollection([
            new ConfiguredLineItem('A', 1),
            new ConfiguredLineItem('A', 2),
            new ConfiguredLineItem('A', 3)
        ]);

        static::assertEquals(
            new CalculatedLineItemCollection([
                new ConfiguredLineItem('A', 3)
            ]),
            $collection
        );
    }

    public function testFilterReturnsNewCollectionWithCorrectItems()
    {
        $collection = new CalculatedLineItemCollection([
            new CalculatedVoucher(
                new LineItem(1, ProductProcessor::TYPE_PRODUCT, 1),
                new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection())
            ),
            new CalculatedVoucher(
                new LineItem(2, ProductProcessor::TYPE_PRODUCT, 1),
                new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection())
            ),
            new ConfiguredLineItem('A', 3),
            new ConfiguredLineItem('B', 3),
            new ConfiguredLineItem('C', 3),
            new ConfiguredLineItem('D', 3)
        ]);

        static::assertEquals(
            new CalculatedLineItemCollection([
                new ConfiguredLineItem('A', 3),
                new ConfiguredLineItem('B', 3),
                new ConfiguredLineItem('C', 3),
                new ConfiguredLineItem('D', 3)
            ]),
            $collection->filterClass(ConfiguredLineItem::class)
        );

        static::assertEquals(
            new CalculatedLineItemCollection([
                new CalculatedVoucher(
                    new LineItem(1, ProductProcessor::TYPE_PRODUCT, 1),
                    new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection())
                ),
                new CalculatedVoucher(
                    new LineItem(2, ProductProcessor::TYPE_PRODUCT, 1),
                    new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection())
                )
            ]),
            $collection->filterClass(CalculatedVoucher::class)
        );
    }

    public function testFilterReturnsNewCollection()
    {
        $collection = new CalculatedLineItemCollection([
            new ConfiguredLineItem('A', 3),
            new ConfiguredLineItem('B', 3),
            new ConfiguredLineItem('C', 3),
            new ConfiguredLineItem('D', 3)
        ]);

        static::assertNotSame(
            new CalculatedLineItemCollection([
                new ConfiguredLineItem('A', 3),
                new ConfiguredLineItem('B', 3),
                new ConfiguredLineItem('C', 3),
                new ConfiguredLineItem('D', 3)
            ]),
            $collection->filterClass(ConfiguredLineItem::class)
        );
    }

    public function testLineItemsCanBeCleared()
    {
        $collection = new CalculatedLineItemCollection([
            new ConfiguredLineItem('A', 3),
            new ConfiguredLineItem('B', 3),
            new ConfiguredLineItem('C', 3),
            new ConfiguredLineItem('D', 3)
        ]);

        $collection->clear();
        static::assertEquals(new CalculatedLineItemCollection(), $collection);
    }

    public function testLineItemsCanBeRemovedByIdentifier()
    {
        $collection = new CalculatedLineItemCollection([
            new ConfiguredLineItem('A', 3),
            new ConfiguredLineItem('B', 3),
            new ConfiguredLineItem('C', 3),
            new ConfiguredLineItem('D', 3)
        ]);

        $collection->remove('A');

        static::assertEquals(
            new CalculatedLineItemCollection([
                new ConfiguredLineItem('B', 3),
                new ConfiguredLineItem('C', 3),
                new ConfiguredLineItem('D', 3)
            ]),
            $collection
        );
    }

    public function testIdentifiersCanEasyAccessed()
    {
        $collection = new CalculatedLineItemCollection([
            new ConfiguredLineItem('A', 3),
            new ConfiguredLineItem('B', 3),
            new ConfiguredLineItem('C', 3),
            new ConfiguredLineItem('D', 3)
        ]);

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $collection->getIdentifiers()
        );
    }

    public function testFillCollectionWithItems()
    {
        $collection = new CalculatedLineItemCollection();
        $collection->fill([
            new ConfiguredLineItem('A', 3),
            new ConfiguredLineItem('B', 3),
            new ConfiguredLineItem('C', 3),
            new ConfiguredLineItem('D', 3)
        ]);

        static::assertEquals(
            new CalculatedLineItemCollection([
                new ConfiguredLineItem('A', 3),
                new ConfiguredLineItem('B', 3),
                new ConfiguredLineItem('C', 3),
                new ConfiguredLineItem('D', 3)
            ]),
            $collection
        );
    }

    public function testGetLineItemByIdentifier()
    {
        $collection = new CalculatedLineItemCollection([
            new ConfiguredLineItem('A', 3),
            new ConfiguredLineItem('B', 3),
            new ConfiguredLineItem('C', 3),
            new ConfiguredLineItem('D', 3)
        ]);

        static::assertEquals(
            new ConfiguredLineItem('C', 3),
            $collection->get('C')
        );
    }

    public function testGetOnEmptyCollection()
    {
        $collection = new CalculatedLineItemCollection();
        static::assertNull($collection->get('not found'));
    }

    public function testFilterGoodsReturnsOnlyGoods()
    {
        $collection = new CalculatedLineItemCollection([
            new ConfiguredGoodsItem('A', 3),
            new ConfiguredLineItem('B', 3),
            new ConfiguredLineItem('C', 3),
            new ConfiguredGoodsItem('D', 3)
        ]);

        static::assertEquals(
            new CalculatedLineItemCollection([
                new ConfiguredGoodsItem('A', 3),
                new ConfiguredGoodsItem('D', 3)
            ]),
            $collection->filterGoods()
        );
    }

    public function testFilterGoodsReturnsNewCollection()
    {
        $collection = new CalculatedLineItemCollection([
            new ConfiguredGoodsItem('A', 3),
            new ConfiguredLineItem('B', 3),
            new ConfiguredLineItem('C', 3),
            new ConfiguredGoodsItem('D', 3)
        ]);

        static::assertNotSame(
            new CalculatedLineItemCollection([
                new ConfiguredGoodsItem('A', 3),
                new ConfiguredGoodsItem('D', 3)
            ]),
            $collection->filterGoods()
        );
    }

    public function testGetPricesCollectionOfMultipleItems()
    {
        $collection = new CalculatedLineItemCollection([
            new CalculatedVoucher(
                new LineItem(1, PercentageVoucherProcessor::TYPE_PERCENTAGE_VOUCHER, 1),
                new Price(200, 200, new CalculatedTaxCollection(), new TaxRuleCollection())
            ),
            new CalculatedVoucher(
                new LineItem(2, PercentageVoucherProcessor::TYPE_PERCENTAGE_VOUCHER, 1),
                new Price(300, 300, new CalculatedTaxCollection(), new TaxRuleCollection())
            )
        ]);

        static::assertEquals(
            new PriceCollection([
                new Price(200, 200, new CalculatedTaxCollection(), new TaxRuleCollection()),
                new Price(300, 300, new CalculatedTaxCollection(), new TaxRuleCollection())
            ]),
            $collection->getPrices()
        );
    }

    public function testRemoveWithNoneExistingIdentifier()
    {
        $collection = new CalculatedLineItemCollection([
            new ConfiguredLineItem('A', 3),
            new ConfiguredLineItem('B', 3),
            new ConfiguredLineItem('C', 3),
            new ConfiguredLineItem('D', 3)
        ]);

        $collection->remove('X');
        static::assertEquals(
            new CalculatedLineItemCollection([
                new ConfiguredLineItem('A', 3),
                new ConfiguredLineItem('B', 3),
                new ConfiguredLineItem('C', 3),
                new ConfiguredLineItem('D', 3)
            ]),
            $collection
        );
    }
}
