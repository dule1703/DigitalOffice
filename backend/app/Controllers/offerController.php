<?php

namespace App\Controllers;

use App\Models\Offer;

class OfferController {
    private $offerModel;

    public function __construct() {
        $this->offerModel = new Offer();
    }

    public function getAllOffers() {
        $offers = $this->offerModel->getAllOffers();
        return [
            'status' => 'success',
            'data' => $offers
        ];
    }

    public function getOfferById($id) {
        $offer = $this->offerModel->getOfferById($id);
        if ($offer) {
            return ['status' => 'success', 'data' => $offer];
        }
        return ['status' => 'error', 'message' => 'Ponuda nije pronađena.'];
    }

    public function searchOffers($term) {
        $results = $this->offerModel->searchOffers($term);
        return ['status' => 'success', 'data' => $results];
    }

}
