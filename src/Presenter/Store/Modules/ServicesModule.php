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

namespace InPost\Shipping\Presenter\Store\Modules;

use InPost\Shipping\ChoiceProvider\CarrierChoiceProvider;
use InPost\Shipping\ChoiceProvider\DimensionTemplateChoiceProvider;
use InPost\Shipping\ChoiceProvider\ShippingServiceChoiceProvider;
use InPost\Shipping\Presenter\CarrierPresenter;
use InPost\Shipping\Presenter\Store\PresenterInterface;
use InPost\Shipping\ShipX\Resource\Service;
require_once _PS_MODULE_DIR_.'inpostshipping/classes/InPostCarrierModel.php';

class ServicesModule implements PresenterInterface
{
    protected $shippingServiceChoiceProvider;
    protected $carrierChoiceProvider;
    protected $dimensionTemplateChoiceProvider;
    protected $carrierPresenter;

    public function __construct(
        ShippingServiceChoiceProvider $shippingServiceChoiceProvider,
        CarrierChoiceProvider $carrierChoiceProvider,
        DimensionTemplateChoiceProvider $dimensionTemplateChoiceProvider,
        CarrierPresenter $carrierPresenter
    ) {
        $this->shippingServiceChoiceProvider = $shippingServiceChoiceProvider;
        $this->carrierChoiceProvider = $carrierChoiceProvider;
        $this->dimensionTemplateChoiceProvider = $dimensionTemplateChoiceProvider;
        $this->carrierPresenter = $carrierPresenter;
    }

    /**
     * {@inheritdoc}
     */
    public function present(): array
    {
        return [
            'services' => [
                'choices' => [
                    'service' => $this->shippingServiceChoiceProvider->getChoices(),
                    'carrier' => $this->carrierChoiceProvider->getChoices(),
                    'template' => $this->dimensionTemplateChoiceProvider->getChoices(),
                ],
                'list' => $this->getServiceList(),
                'courierServices' => Service::COURIER_SERVICES,
                'smsEmailServices' => Service::SMS_EMAIL_SERVICES,
            ],
        ];
    }

    protected function getServiceList(): array
    {
        $list = [];

    foreach (\InPostCarrierModel::getNonDeletedCarriers() as $carrier) {
            $list[$carrier->id] = $this->carrierPresenter->present($carrier);
        }

        return $list;
    }
}
