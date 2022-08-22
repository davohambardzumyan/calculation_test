<?php

namespace App\Services;

use AshAllenDesign\LaravelExchangeRates\Classes\ExchangeRate;
use Carbon\Carbon;

class CommissionFeeService
{
    private $withdrawInWeekForPrivateUserByDate = [];

    private function getData( $userId, $date, $value, $currency ) {

        $valueInEURO = $this->changeInEuro( $value, $currency );

        $exceeded1000 = 0;

        if ( $valueInEURO > 1000 ) {

            $exceeded1000 = $valueInEURO - 1000;
        }

        return [
            'userId'    => $userId,
            'date'      => $date,
            'value'     => $value,
            'totalValueInEURO'  => $valueInEURO,
            'exceeded1000'      => $exceeded1000,
            'currency'  => $currency
        ];

    }

    private function changeInEuro( $value, $currency, $date=null) {

        if( $currency!='EUR' ) {

            $exchangeRate = new ExchangeRate();

            $carbonDate = $date==null?Carbon::now():Carbon::parse($date)->format('Y-m-d');

            $result = $exchangeRate->exchangeRate("EUR", $currency, $carbonDate);

            return $value/$result;
        }

        return $value;
    }

    /**
     * @param $userId
     * @param $date
     * @param $value
     * @param $currency
     * @return array
     */
    public function setWithdrawInWeekForPrivateUserByDate( $userId, $date, $value, $currency ) {

        $year = explode( '-', $date )[0];

        $numberOfWeek = date("W", strtotime( $date ) );

        $data = $this->getData( $userId, $date, $value, $currency );

        if ( !array_key_exists( $userId, $this->withdrawInWeekForPrivateUserByDate ) ) {

            $weekArr = [ $numberOfWeek => [ $data ] ];

            $yearArr = [ $year => $weekArr ];

            $this->withdrawInWeekForPrivateUserByDate[ $userId ] = $yearArr;

            return $this->withdrawInWeekForPrivateUserByDate;
        }

        if ( !array_key_exists( $year, $this->withdrawInWeekForPrivateUserByDate[ $userId ] ) ) {

            $this->withdrawInWeekForPrivateUserByDate[ $userId ][ $year ] = [ $numberOfWeek => [ $data ] ] ;

            return $this->withdrawInWeekForPrivateUserByDate;
        }

        if ( array_key_exists( $numberOfWeek, $this->withdrawInWeekForPrivateUserByDate[ $userId ][ $year ] ) ) {

            $numberOfWeekArr = $this->withdrawInWeekForPrivateUserByDate[ $userId ][ $year ][ $numberOfWeek ];

            $countNumberOfWeekArr = count( $numberOfWeekArr );

            $data['totalValueInEURO'] += $numberOfWeekArr[ $countNumberOfWeekArr - 1 ][ 'totalValueInEURO' ];

            if ( $numberOfWeekArr[ $countNumberOfWeekArr - 1 ][ 'exceeded1000' ] > 0 ) {

                $data['exceeded1000'] = $this->changeInEuro( $data['value'], $data['currency'] );

            } else if ( $data['totalValueInEURO'] > 1000 ) {

                $data['exceeded1000'] = $data['totalValueInEURO'] - 1000;
            }

            array_push( $this->withdrawInWeekForPrivateUserByDate[ $userId ][ $year ][ $numberOfWeek ], $data );

        } else {

            $this->withdrawInWeekForPrivateUserByDate[ $userId ][ $year ][ $numberOfWeek ] = [ $data ];
        }

        return $this->withdrawInWeekForPrivateUserByDate;
    }

    public function getWithdrawInWeekForPrivateUserByDate( $userId, $date ) {

        $year = explode( '-', $date )[0];

        $numberOfWeek = date("W", strtotime( $date ) );

        return $this->withdrawInWeekForPrivateUserByDate[ $userId ][ $year ][ $numberOfWeek ];
    }

    public function getWithdrawInWeekForPrivateAllUsersByDate( ) {

        return $this->withdrawInWeekForPrivateUserByDate;
    }

    public function getWeekOnDecemberFromBeforeYearValueInEuro( $userId, $year ) {

        if (!isset( $this->withdrawInWeekForPrivateUserByDate[ $userId ][ $year ][ '01' ] ) ) {
            return false;
        }

        $value = 0;

        $commissionFeeForThisWeek = $this->withdrawInWeekForPrivateUserByDate[ $userId ][ $year ][ '01' ];
        for ( $i = 0; $i < count( $commissionFeeForThisWeek ); $i++ ) {
            $row = $commissionFeeForThisWeek[ $i ];
            if ( date("m", strtotime( $row[ 'date' ] ) ) === '12' ) {
                $value += $this->changeInEuro( $row[ 'value' ], $row[ 'currency' ] );
            }
        }

        return $value;
    }
}
