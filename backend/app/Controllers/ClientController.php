<?php

namespace App\Controllers;

use App\Models\Client;

class ClientController {
    private $clientModel;

    public function __construct() {
        $this->clientModel = new Client();
    }

    public function getAllClients() {
        $clients = $this->clientModel->getAllClients();

        return [
            'status' => 'success',
            'data' => $clients
        ];
    }

    public function searchClients($term) {
        $term = trim($term);
        if (strlen($term) < 2) {
            return ['status' => 'error', 'message' => 'Prekratak pojam za pretragu.'];
        }

        $results = $this->clientModel->searchClients($term);
        return ['status' => 'success', 'data' => $results];
    }

    public function getClientById($id) {
        return $this->clientModel->getClientById($id);
    }

     public function createClient($data) {
        $response = $this->clientModel->createClient($data);
        return $response;
    }

    public function updateClient($id, $data) {
        $response = $this->clientModel->updateClient($id, $data);
        return $response;
    }

    public function deleteClient($id) {
        $response = $this->clientModel->deleteClient($id);
        return $response;
    }

}