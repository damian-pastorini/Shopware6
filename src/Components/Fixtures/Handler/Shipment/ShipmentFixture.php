<?php

namespace Kiener\MolliePayments\Components\Fixtures\Handler\Shipment;

use Kiener\MolliePayments\Components\Fixtures\MollieFixtureHandlerInterface;
use Kiener\MolliePayments\Components\Fixtures\Utils\CurrencyUtils;
use Kiener\MolliePayments\Components\Fixtures\Utils\DeliveryTimesUtils;
use Mollie\Api\Resources\ShipmentCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;

class ShipmentFixture implements MollieFixtureHandlerInterface
{

    private const SHIPMENT_ID = '0d1eeedd6d22436385580e2ff42431b9';


    /**
     * @var EntityRepository<ShipmentCollection>
     */
    private EntityRepository $repoShipments;

    /**
     * @var EntityRepository<SalesChannelCollection>
     */
    private EntityRepository $repoSalesChannels;

    /**
     * @var CurrencyUtils
     */
    private CurrencyUtils $currencyUtils;

    /**
     * @var DeliveryTimesUtils
     */
    private DeliveryTimesUtils $deliveryTimesUtils;


    /**
     * @param EntityRepository<ShipmentCollection> $repoShipments
     * @param EntityRepository<SalesChannelCollection> $repoSalesChannels
     */
    public function __construct(EntityRepository $repoShipments, EntityRepository $repoSalesChannels, CurrencyUtils $currencyUtils, DeliveryTimesUtils $deliveryTimesUtils)
    {
        $this->repoShipments = $repoShipments;
        $this->repoSalesChannels = $repoSalesChannels;
        $this->currencyUtils = $currencyUtils;
        $this->deliveryTimesUtils = $deliveryTimesUtils;
    }


    public function install(): void
    {
        $ctx = Context::createDefaultContext();

        $this->createShipment(self::SHIPMENT_ID, 'Mollie Test Shipment', $ctx);
    }

    public function uninstall(): void
    {
        $ctx = Context::createDefaultContext();

        $this->repoShipments->delete([['id' => self::SHIPMENT_ID]], $ctx);
    }


    private function createShipment(string $id, string $name, Context $context): void
    {
        $currencyEuro = $this->currencyUtils->getCurrency('EUR');
        $deliveryTime = $this->deliveryTimesUtils->getRandomDeliveryTime();

        $this->repoShipments->upsert([
            [
                "id" => $id,
                "active" => true,
                "name" => $name,
                "availabilityRuleId" => null,
                "technicalName" => 'mollie_fixture_shipment',
                "deliveryTimeId" => $deliveryTime->getId(),
                "prices" => [
                    [
                        "id" => '021eeedd6d22436385580e2ff42431b3',
                        "calculation" => 2,
                        "quantityStart" => 0,
                        "currencyPrice" => [
                            [
                                "currencyId" => $currencyEuro->getId(),
                                "net" => 4.19,
                                "gross" => 4.99,
                                "linked" => false
                            ]
                        ]
                    ]
                ],
                "translations" => [
                    "de-DE" => [
                        "trackingUrl" => "https://www.carrier.com/de/tracking/%s"
                    ],
                    "en-GB" => [
                        "trackingUrl" => "https://www.carrier.com/en/tracking/%s"
                    ]
                ]
            ],
        ], $context);


        $salesChannelIds = $this->repoSalesChannels->searchIds(new Criteria(), $context)->getIds();

        $this->assignShippingMethod($id, $salesChannelIds, $context);
    }

    private function assignShippingMethod(string $shippingId, array $salesChannelIds, Context $ctx): void
    {
        $paymentUpdates = [];
        $scShippingIds = [];

        $scShippingIds[] = [
            'id' => $shippingId,
        ];

        foreach ($salesChannelIds as $id) {
            $paymentUpdates[] = [
                'id' => $id,
                'shippingMethods' => $scShippingIds,
            ];
        }

        $this->repoSalesChannels->update($paymentUpdates, $ctx);
    }
}