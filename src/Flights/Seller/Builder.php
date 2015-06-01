<?php

namespace TravelPSDK\Flights\Seller;

use TravelPSDK\Flights\Seller\Info as SellerInfo,
    TravelPSDK\Flights\Seller\InfoFactory as SellerInfoFactory,
    TravelPSDK\Common\Collection as TicketsCollection
    ;

class Builder
{

    /**
     * @var \stdClass
     */
    private $sellerData;

    /**
     * @var SellerInfoFactory
     */
    private $sellerInfoFactory;

    /**
     * @param \stdClass $sellerData
     */
    public function __construct($sellerData)
    {
        $this->sellerData = $sellerData;
        $this->sellerInfoFactory = new SellerInfoFactory();
    }

    /**
     * @param \stdClass $sellerData
     * @return Entity
     */
    public static function build($sellerData)
    {
        $builder = new Builder($sellerData);

        $sellerInfo = $builder->buildSellerInfo();
        $ticketsCount = $builder->extractTicketsCount();
        $ticketsCollection = $builder->buildTicketsCollection($sellerInfo);

        $entity = new Entity($sellerInfo,
                             $ticketsCount,
                             $ticketsCollection);

        return $entity;
    }

    /**
     * @param SellerInfo $sellerInfo
     * @return \ArrayIterator
     */
    private function buildTicketsCollection($sellerInfo)
    {
        $proposals = $this->sellerData->proposals;
        $collection = new TicketsCollection();
        if (empty($proposals)) {
            return $collection;
        }

        $ticketFactory = new TicketFactory($sellerInfo);

        foreach ($proposals as $proposal) {
            $ticket = $ticketFactory->create($proposal);
            $collection->append($ticket);
        }

        return $collection;
    }

    private function extractTicketsCount()
    {
        $this->validateSellerMetaGates();
        $gates = (array) $this->sellerData->meta->gates;
        $gate = $gates[0];

        return $gate->good_count;
    }

    private function validateSellerMetaGates()
    {
        if (empty($this->sellerData->meta)
            ||
            empty($this->sellerData->meta->gates))
        {
            throw new \InvalidArgumentException("Seller data is invalid. Unable to get gate id/count!");
        }
    }

    /**
     * @return SellerInfo
     */
    private function buildSellerInfo()
    {
        $this->validateSellerMetaGates();
        $sellerInfo = $this->sellerInfoFactory->create($this->sellerData);
        return $sellerInfo;
    }
}
