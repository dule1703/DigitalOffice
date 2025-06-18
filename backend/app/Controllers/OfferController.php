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

    public function createOffer($data) {
        $success = $this->offerModel->createOffer($data);
        return $success
            ? ['status' => 'success', 'message' => 'Ponuda je uspešno kreirana.']
            : ['status' => 'error', 'message' => 'Greška pri kreiranju ponude.'];
    }

    public function updateOffer($id, $data) {
        $success = $this->offerModel->updateOffer($id, $data);
        return $success
            ? ['status' => 'success', 'message' => 'Ponuda je uspešno ažurirana.']
            : ['status' => 'error', 'message' => 'Greška pri ažuriranju ponude.'];
    }

    public function deleteOffer($id) {
        $success = $this->offerModel->deleteOffer($id);
        return $success
            ? ['status' => 'success', 'message' => 'Ponuda je obrisana.']
            : ['status' => 'error', 'message' => 'Greška pri brisanju ponude.'];
    }

}
