<?php
/**
 * Copyright since 2021 InPost S.A.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the EUPL-1.2 or later.
 * You may not use this work except in compliance with the Licence.
 *
 * You may obtain a copy of the Licence at:
 * https://joinup.ec.europa.eu/software/page/eupl
 * It is also bundled with this package in the file LICENSE.txt
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the Licence is distributed on an AS IS basis,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the Licence for the specific language governing permissions
 * and limitations under the Licence.
 *
 * @author    InPost S.A.
 * @copyright Since 2021 InPost S.A.
 * @license   https://joinup.ec.europa.eu/software/page/eupl
 */

use InPost\Shipping\ShipX\Resource\Organization\Shipment;

class InPostParcelModel extends ObjectModel
{
    /**
     * @var int|null
     */
    public $id_shipment;

    /**
     * @var string|null
     */
    public $template;

    /**
     * @var string|null
     */
    public $dimensions;

    /**
     * @var string|null
     */
    public $tracking_number;

    /**
     * @var bool
     */
    public $is_non_standard = false;

    /**
     * @var InPostShipmentModel|null
     */
    private $shipment;

    public static $definition = [
        'table' => 'inpost_shipment_parcel',
        'primary' => 'id_parcel',
        'fields' => [
            'id_shipment' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'template' => [
                'type' => self::TYPE_STRING,
                'values' => Shipment::DIMENSION_TEMPLATES,
                'allow_null' => true,
            ],
            'dimensions' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255,
                'allow_null' => true,
            ],
            'is_non_standard' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'tracking_number' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 24,
                'allow_null' => true,
            ],
        ],
    ];

    public function add($auto_date = true, $null_values = false)
    {
        if (!isset($this->shipment)) {
            throw new \LogicException('Parcel must be associated with a shipment.');
        }

        if (null === $this->shipment->id) {
            throw new \LogicException('Shipment has not been persisted.');
        }

        $this->id_shipment = $this->shipment->id;

        return parent::add($auto_date, $null_values);
    }

    public function validateField($field, $value, $id_lang = null, $skip = [], $human_errors = false)
    {
        if (
            null === $value
            && isset($this->def['fields'][$field]['allow_null'])
            && $this->def['fields'][$field]['allow_null']
        ) {
            return true;
        }

        return parent::validateField($field, $value, $id_lang, $skip, $human_errors);
    }

    /**
     * @internal
     */
    public function setShipment(InPostShipmentModel $shipment)
    {
        $this->shipment = $shipment;
    }
}
