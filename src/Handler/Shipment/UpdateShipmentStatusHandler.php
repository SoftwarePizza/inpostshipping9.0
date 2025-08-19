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

namespace InPost\Shipping\Handler\Shipment;

use InPost\Shipping\Configuration\OrdersConfiguration;
use InPost\Shipping\Configuration\ShipXConfiguration;
use InPost\Shipping\Repository\ObjectModel\ShipmentRepository;
use InPost\Shipping\ShipX\Resource\Organization\Shipment;
use InPost\Shipping\ShipX\Resource\Status;

class UpdateShipmentStatusHandler
{
    protected $shipXConfiguration;
    protected $ordersConfiguration;
    private $repository;

    /**
     * @var array<string, \OrderState> order states by shipment status
     */
    private $orderStates = [];

    public function __construct(
        ShipXConfiguration $shipXConfiguration,
        OrdersConfiguration $ordersConfiguration,
        ShipmentRepository $repository = null
    ) {
        $this->shipXConfiguration = $shipXConfiguration;
        $this->ordersConfiguration = $ordersConfiguration;
        $this->repository = $repository ?: new ShipmentRepository();
    }

    public function handle(array $ids = [])
    {
        $shipments = $this->repository->getNotFinalizedShipments(
            $this->shipXConfiguration->useSandboxMode(),
            $this->shipXConfiguration->getOrganizationId(),
            $ids
        );

        $i = 0;
        $shipmentsByShipXId = [];

        foreach ($shipments as $shipment) {
            $shipmentsByShipXId[$shipment->shipx_shipment_id] = $shipment;
            if (0 === ++$i % 100) {
                $this->updateStatues($shipmentsByShipXId);
                $shipmentsByShipXId = [];
            }
        }

        if ([] !== $shipmentsByShipXId) {
            $this->updateStatues($shipmentsByShipXId);
        }
    }

    /**
     * @param array<int, \InPostShipmentModel> $shipmentsByShipXId
     */
    private function updateStatues(array $shipmentsByShipXId)
    {
        $shipments = Shipment::getCollection(['id' => array_keys($shipmentsByShipXId)], 'id', 'asc');

        foreach ($shipments as $shipment) {
            $shipmentModel = $shipmentsByShipXId[$shipment->getId()];
            if ($shipmentModel->status === $shipment->status) {
                continue;
            }

            $hasTrackingNumbers = !empty($shipmentModel->tracking_number);
            $shipmentModel->tracking_number = $shipment->tracking_number;
            $shipmentModel->status = $shipment->status;
            $shipmentModel->update();

            $this->updateOrderStatus($shipmentModel);

            if ($hasTrackingNumbers) {
                continue;
            }

            $iterator = new \MultipleIterator(\MultipleIterator::MIT_NEED_ALL | \MultipleIterator::MIT_KEYS_NUMERIC);
            $iterator->attachIterator(new \ArrayIterator($shipment->parcels ?: []));
            $iterator->attachIterator(new \ArrayIterator($shipmentModel->getParcels()));

            foreach ($iterator as list($parcel, $parcelModel)) {
                $parcelModel->tracking_number = $parcel['tracking_number'];
                $parcelModel->update();
            }

            $shipmentModel->updateOrderTrackingNumber();
        }
    }

    protected function updateOrderStatus(\InPostShipmentModel $shipmentModel)
    {
        if (null === $orderState = $this->getOrderStateByShipmentStatus($shipmentModel->status)) {
            return;
        }

        $currentState = $shipmentModel->getOrder()->getCurrentOrderState();

        if (null === $currentState || $currentState->id !== $orderState->id) {
            $shipmentModel->getOrder()->setCurrentState($orderState->id);
        }
    }

    /**
     * @param string $status
     *
     * @return \OrderState|null
     */
    private function getOrderStateByShipmentStatus($status)
    {
        if (array_key_exists($status, $this->orderStates)) {
            return $this->orderStates[$status];
        }

        if (!$orderStateId = $this->getOrderStateIdByShipmentStatus($status)) {
            return $this->orderStates[$status] = null;
        }

        if (!\Validate::isLoadedObject($orderState = new \OrderState($orderStateId))) {
            return $this->orderStates[$status] = null;
        }

        return $this->orderStates[$status] = $orderState;
    }

    /**
     * @param string $status
     *
     * @return int|null
     */
    private function getOrderStateIdByShipmentStatus($status)
    {
        switch ($status) {
            case Status::STATUS_COLLECTED_FROM_SENDER:
            case Status::STATUS_TAKEN_BY_COURIER:
                if (!$this->ordersConfiguration->shouldChangeOrderStateOnShipmentCollected()) {
                    return null;
                }

                return $this->ordersConfiguration->getShipmentCollectedOrderStateId();
            case Status::STATUS_DELIVERED:
                if (!$this->ordersConfiguration->shouldChangeOrderStateOnShipmentDelivered()) {
                    return null;
                }

                return $this->ordersConfiguration->getShipmentDeliveredOrderStateId();
            default:
                return null;
        }
    }
}
