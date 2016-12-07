<?php

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Cart;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Cart\Cart;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;
use Shopware\Bundle\CartBundle\Domain\Price\CartPrice;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\ConfiguredGoodsItem;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\ConfiguredLineItem;

class CalculatedCartTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyCartHasNoGoods()
    {
        $cart = new CalculatedCart(
            Cart::createNew('test'),
            new CalculatedLineItemCollection(),
            new CartPrice(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection()),
            new DeliveryCollection()
        );

        static::assertCount(0, $cart->getLineItems()->filterGoods());
    }

    public function testCartWithLineItemsHasGoods()
    {
        $cart = new CalculatedCart(
            Cart::createNew('test'),
            new CalculatedLineItemCollection([
                new ConfiguredGoodsItem('A', 1),
                new ConfiguredLineItem('B', 1)
            ]),
            new CartPrice(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection()),
            new DeliveryCollection()
        );

        static::assertCount(1, $cart->getLineItems()->filterGoods());
    }

    public function testCartHasNoGoodsIfNoLineItemDefinedAsGoods()
    {
        $cart = new CalculatedCart(
            Cart::createNew('test'),
            new CalculatedLineItemCollection([
                new ConfiguredLineItem('A', 1),
                new ConfiguredLineItem('B', 1)
            ]),
            new CartPrice(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection()),
            new DeliveryCollection()
        );

        static::assertCount(0, $cart->getLineItems()->filterGoods());
    }
}
