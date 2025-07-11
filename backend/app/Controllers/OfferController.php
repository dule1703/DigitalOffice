<?php

namespace App\Controllers;

use App\Models\Offer;

class OfferController {
    private $offerModel;

    public function __construct() {
        $this->offerModel = new Offer();
    }

    public function getAllOffers() {
        return $this->offerModel->getAllOffers();
    }

    public function getOfferById($id) {      return $this->offerModel->getOfferById($id);

    }

   public function searchOffers($term) {
        $term = trim($term);
        if (strlen($term) < 2) {
            return ['status' => 'error', 'message' => 'Prekratak pojam za pretragu.'];
        }

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
