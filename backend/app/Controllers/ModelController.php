<?php

namespace App\Controllers;

use App\Models\Model;

class ModelController {
    private $modelModel;

    function __construct() {
        $this->modelModel = new Model();
    }

    function getAllModels() {
        return $this->modelModel->getAllModels();
    }

    function getBasicEqup() {
        return $this->modelModel->getBasicEquip();
    }
}