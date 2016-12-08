<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\CartBundle\Infrastructure;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Cart\Cart;
use Shopware\Bundle\CartBundle\Domain\Cart\CartCalculator;
use Shopware\Bundle\CartBundle\Infrastructure\Cart\CartContextServiceInterface;
use Shopware\Bundle\CartBundle\Domain\Cart\CartPersisterInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\Stackable;

class StoreFrontCartService
{
    const CART_NAME = 'shopware';

    const CART_TOKEN_KEY = 'cart_token_' . self::CART_NAME;

    /**
     * @var CartCalculator
     */
    private $calculation;

    /**
     * @var CartPersisterInterface
     */
    private $persister;

    /**
     * @var CartContextServiceInterface
     */
    private $contextService;

    /**
     * @var \Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @param CartCalculator $calculation
     * @param CartPersisterInterface $persister
     * @param CartContextServiceInterface $contextService
     * @param \Enlight_Components_Session_Namespace $session
     */
    public function __construct(
        CartCalculator $calculation,
        CartPersisterInterface $persister,
        CartContextServiceInterface $contextService,
        \Enlight_Components_Session_Namespace $session
    ) {
        $this->calculation = $calculation;
        $this->persister = $persister;
        $this->contextService = $contextService;
        $this->session = $session;
    }

    /**
     * @return CalculatedCart
     */
    public function createNew()
    {
        $cart = $this->createNewCart();
        return $this->calculate($cart);
    }

    /**
     * @return CalculatedCart
     */
    public function getCalculated()
    {
        if ($this->getCartToken() === null) {
            //first access for frontend session
            $cart = $this->createNewCart();
        } else {
            try {
                //try to access existing cart, identified by session token
                $cart = $this->persister->load($this->getCartToken());
            } catch (\Exception $e) {
                //token not found, create new cart
                $cart = $this->createNewCart();
            }
        }

        return $this->calculate($cart);
    }

    /**
     * @param Cart $cart
     * @return CalculatedCart
     */
    public function calculate(Cart $cart)
    {
        $context = $this->contextService->getCartContext();
        $calculated = $this->calculation->calculate($cart, $context);
        $this->save($calculated->getCart());
        return $calculated;
    }

    /**
     * @param LineItemInterface $item
     */
    public function add(LineItemInterface $item)
    {
        $calculated = $this->getCalculated();

        $exists = $calculated->getLineItems()->get($item->getIdentifier());
        if ($exists instanceof Stackable) {
            $exists->getLineItem()->setQuantity($item->getQuantity() + $exists->getQuantity());
        } else {
            $calculated->getCart()->getLineItems()->add($item);
        }

        $this->calculate($calculated->getCart());
    }

    /**
     * @param string $identifier
     */
    public function remove($identifier)
    {
        $cart = $this->getCalculated()->getCart();
        $cart->getLineItems()->remove($identifier);
        $this->calculate($cart);
    }

    /**
     * @param Cart $cart
     */
    private function save(Cart $cart)
    {
        $this->persister->save($cart);
        $this->session->offsetSet(self::CART_TOKEN_KEY, $cart->getToken());
    }

    /**
     * @return Cart
     */
    private function createNewCart()
    {
        $cart = Cart::createNew(self::CART_NAME);
        $this->session->offsetSet(self::CART_TOKEN_KEY, $cart->getToken());
        return $cart;
    }

    /**
     * @return string
     */
    private function getCartToken()
    {
        if ($this->session->offsetExists(self::CART_TOKEN_KEY)) {
            return $this->session->offsetGet(self::CART_TOKEN_KEY);
        }
        return null;
    }
}
