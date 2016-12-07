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

namespace Shopware\Bundle\CartBundle\Infrastructure\Customer;

use Shopware\Bundle\CartBundle\Domain\Customer\Address;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\AttributeHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\CountryHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\Hydrator;

class AddressHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @var CountryHydrator
     */
    private $countryHydrator;

    /**
     * @param AttributeHydrator $attributeHydrator
     * @param CountryHydrator $countryHydrator
     */
    public function __construct(AttributeHydrator $attributeHydrator, CountryHydrator $countryHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
        $this->countryHydrator = $countryHydrator;
    }

    /**
     * @param array $data
     * @return Address
     */
    public function hydrate($data)
    {
        $address = new Address();
        $address->setId((int) $data['__address_id']);
        $address->setCompany($data['__address_company']);
        $address->setDepartment($data['__address_department']);
        $address->setSalutation($data['__address_salutation']);
        $address->setFirstname($data['__address_firstname']);
        $address->setTitle($data['__address_title']);
        $address->setLastname($data['__address_lastname']);
        $address->setStreet($data['__address_street']);
        $address->setZipcode($data['__address_zipcode']);
        $address->setCity($data['__address_city']);
        $address->setPhone($data['__address_phone']);
        $address->setVatId($data['__address_ustid']);
        $address->setAdditionalAddressLine1($data['__address_additional_address_line1']);
        $address->setAdditionalAddressLine2($data['__address_additional_address_line2']);

        if ($data['__country_id'] !== null) {
            $address->setCountry($this->countryHydrator->hydrateCountry($data));
        }

        if ($data['__countryState_id'] !== null) {
            $address->setState($this->countryHydrator->hydrateState($data));
        }

        if ($data['__addressAttribute.id']) {
            $this->attributeHydrator->addAttribute($address, $data, 'addressAttribute');
        }

        return $address;
    }
}
