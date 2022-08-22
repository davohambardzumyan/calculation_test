<?php

namespace App\Http\Controllers;

class UserController extends Controller
{

    public function __construct( $userId ) {

        $this->userId = $userId;

    }

    private $userId;

    private $freeWithdraw = 3;


    public function getUserId() {
        return $this->userId;
    }


    public function isFreeWithdraw( $withdrawInWeekForPrivateUser ) {

        return (count( $withdrawInWeekForPrivateUser ) > $this->freeWithdraw ) ? true : false;

    }
}
