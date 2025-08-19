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

namespace InPost\Shipping\Presenter;

use Context;
use Currency;
use InPost\Shipping\Configuration\SzybkieZwrotyConfiguration;
use InPost\Shipping\Install\Tabs;
use InPost\Shipping\PrestaShopContext;
use InPost\Shipping\ShipX\Resource\Point;
use InPost\Shipping\ShipX\Resource\SendingMethod;
use InPost\Shipping\ShipX\Resource\Service;
use InPost\Shipping\ShipX\Resource\Status;
use InPost\Shipping\Translations\DimensionTemplateTranslator;
use InPost\Shipping\Translations\SendingMethodTranslator;
use InPost\Shipping\Translations\ShippingServiceTranslator;
use InPostShipmentModel;
use InPostShipping;
use Order;
use Tools;

class ShipmentPresenter
{
    const TRANSLATION_SOURCE = 'ShipmentPresenter';

    protected $module;
    protected $context;
    protected $shippingServiceTranslator;
    protected $sendingMethodTranslator;
    protected $templateTranslator;
    protected $statusPresenter;
    protected $szybkieZwrotyConfiguration;
    private $shopContext;

    protected $currencyIndex = [];

    public function __construct(
        InPostShipping $module,
        Context $context,
        ShippingServiceTranslator $shippingServiceTranslator,
        SendingMethodTranslator $sendingMethodTranslator,
        DimensionTemplateTranslator $templateTranslator,
        ShipmentStatusPresenter $statusPresenter,
        SzybkieZwrotyConfiguration $szybkieZwrotyConfiguration,
        PrestaShopContext $shopContext = null
    ) {
        $this->module = $module;
        $this->context = $context;
        $this->shippingServiceTranslator = $shippingServiceTranslator;
        $this->sendingMethodTranslator = $sendingMethodTranslator;
        $this->templateTranslator = $templateTranslator;
        $this->statusPresenter = $statusPresenter;
        $this->szybkieZwrotyConfiguration = $szybkieZwrotyConfiguration;
        $this->shopContext = $shopContext ?: new PrestaShopContext();
    }

    public function present(InPostShipmentModel $inPostShipment): array
    {
        $id_currency = $this->getCurrencyIdByOrderId($inPostShipment->id_order);

        return [
            'id' => $inPostShipment->id,
            'service' => $this->shippingServiceTranslator->translate($inPostShipment->service),
            'sending_method' => $this->sendingMethodTranslator->translate($inPostShipment->sending_method),
            'weekend_delivery' => $inPostShipment->weekend_delivery,
            'sending_point' => $inPostShipment->sending_point,
            'point_type' => $inPostShipment->sending_method === SendingMethod::POP
                ? Point::TYPE_POP
                : Point::TYPE_PARCEL_LOCKER,
            'target_point' => $inPostShipment->target_point,
            'reference' => $inPostShipment->reference,
            'email' => $inPostShipment->email,
            'phone' => $inPostShipment->phone,
            'tracking_number' => $inPostShipment->tracking_number,
            'send_sms' => $inPostShipment->send_sms,
            'send_email' => $inPostShipment->send_email,
            'price' => $this->formatPrice($inPostShipment->price, $id_currency),
            'parcels' => array_map([$this, 'presentParcel'], $inPostShipment->getParcels()),
            'cod_amount' => $this->formatPrice($inPostShipment->cod_amount, $id_currency),
            'insurance_amount' => $this->formatPrice($inPostShipment->insurance_amount, $id_currency),
            'status' => $this->statusPresenter->present($inPostShipment->status),
            'date_add' => Tools::displayDate($inPostShipment->date_add, null, true),
            'viewUrl' => $this->context->link->getAdminLink(Tabs::SHIPMENTS_CONTROLLER_NAME, true, [], [
                'ajax' => true,
                'action' => 'viewShipment',
                'id_shipment' => $inPostShipment->id,
            ]),
            'actions' => $this->getActions($inPostShipment),
        ];
    }

    protected function formatPrice(?float $price, int $currencyId): string
    {
        if (!$price) {
            return '--';
        }

        $currency = Currency::getCurrencyInstance($currencyId);

        return $this->context->currentLocale->formatPrice($price, $currency->iso_code);
    }

    protected function getActions(InPostShipmentModel $inPostShipment): array
    {
        $actions = [
            'printLabel' => [
                'text' => $this->module->l('Print label', self::TRANSLATION_SOURCE),
                'url' => $this->context->link->getAdminLink(Tabs::SHIPMENTS_CONTROLLER_NAME, true, [], [
                    'ajax' => true,
                    'action' => 'printLabel',
                    'id_shipment' => $inPostShipment->id,
                ]),
                'icon' => 'print',
            ],
        ];

        if (in_array($inPostShipment->service, Service::LOCKER_CARRIER_SERVICES, true)) {
            $actions['return'] = [
                'text' => $this->module->l('Return', self::TRANSLATION_SOURCE),
                'url' => $this->szybkieZwrotyConfiguration->getOrderReturnFormUrl(true),
                'icon' => 'undo',
            ];
        } else {
            $actions['printReturnLabel'] = [
                'text' => $this->module->l('Print return label', self::TRANSLATION_SOURCE),
                'url' => $this->context->link->getAdminLink(Tabs::SHIPMENTS_CONTROLLER_NAME, true, [], [
                    'ajax' => true,
                    'action' => 'printReturnLabel',
                    'id_shipment' => $inPostShipment->id,
                ]),
                'icon' => 'print',
            ];
        }

        if ($inPostShipment->id_dispatch_order) {
            $actions['printDispatchOrder'] = [
                'text' => $this->module->l('Print dispatch order', self::TRANSLATION_SOURCE),
                'url' => $this->context->link->getAdminLink(Tabs::SHIPMENTS_CONTROLLER_NAME, true, [], [
                    'ajax' => true,
                    'action' => 'printDispatchOrder',
                    'id_shipment' => $inPostShipment->id,
                ]),
                'icon' => 'print',
            ];
        } elseif ($inPostShipment->sending_method === SendingMethod::DISPATCH_ORDER) {
            $actions['createDispatchOrder'] = [
                'text' => $this->module->l('Create dispatch order', self::TRANSLATION_SOURCE),
                'url' => $this->context->link->getAdminLink(Tabs::SHIPMENTS_CONTROLLER_NAME, true, [], [
                    'ajax' => true,
                    'action' => 'createDispatchOrder',
                    'id_shipment' => $inPostShipment->id,
                ]),
                'icon' => $this->shopContext->is177() ? 'local_shipping' : 'truck',
            ];
        }

        if (in_array($inPostShipment->status, Status::NOT_SENT_STATUSES, true)) {
            $actions['deleteShipment'] = [
                'text' => $this->module->l('Delete', self::TRANSLATION_SOURCE),
                'url' => $this->context->link->getAdminLink(Tabs::SHIPMENTS_CONTROLLER_NAME, true, [], [
                    'ajax' => true,
                    'action' => 'delete',
                    'id_shipment' => $inPostShipment->id,
                ]),
                'icon' => $this->shopContext->is177() ? 'delete' : 'trash',
            ];
        }

        return $actions;
    }

    protected function getCurrencyIdByOrderId(int $orderId): int
    {
        if (!isset($this->currencyIndex[$orderId])) {
            $this->currencyIndex[$orderId] = (int) (new Order($orderId))->id_currency;
        }

        return $this->currencyIndex[$orderId];
    }

    /**
     * @return array
     */
    private function presentParcel(\InPostParcelModel $parcel)
    {
        return [
            'template' => $this->templateTranslator->translate($parcel->template),
            'dimensions' => json_decode($parcel->dimensions, true),
            'is_non_standard' => $parcel->is_non_standard,
            'tracking_number' => $parcel->tracking_number,
        ];
    }
}
