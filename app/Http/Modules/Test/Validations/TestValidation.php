<?php

namespace App\Http\Modules\Test\Validations;

class TestValidation {

    public static function test() {
        return [
            'rules' => [
                'id' => ['required', 'integer'],
                'name'  => ['string']
            ],
            'filters' => [
            ]
        ];
    }


}