<?php

namespace App\Services;

use Carbon\Carbon;
use App\Helpers\XmlHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleXMLElement;

class BlueMediaService
{
    use XmlHelper;

    const DESCRIPTION_TRANSACTION = 'Zaliczka na oferte ';

    public function getInitUrl($request)
    {
        $unique = rand(1, 9) . rand(1, 9) . Str::random(1) . strtotime("now") . rand(1, 9)
            . Str::random(1) . rand(1, 9) . Str::random(1);
        $data = [
            'ServiceID' => config('blueMedia.serviceId'),
            'OrderID' => (string) $unique,
            'Amount' => (string) $request->amount,
            'Description' => self::DESCRIPTION_TRANSACTION . Carbon::now(),
            'GatewayID' => config('blueMedia.getewayID'),
            'Currency' => config('blueMedia.currency'),
            'CustomerEmail' => 'mariusx14@wp.pl',
        ];
        $data['Hash'] = hash(
            'sha256',
            $data['ServiceID'] . '|'
            . $data['OrderID'] . '|'
            . $data['Amount'] . '|'
            . $data['Description'] . '|'
            . $data['GatewayID'] . '|'
            . $data['Currency'] . '|'
            . $data['CustomerEmail'] . '|'
            . config('blueMedia.hashKey')
        );
        $fields = (is_array($data)) ? http_build_query($data) : $data;
        return [config('blueMedia.initUrl') . '?' . $fields, (string) $unique];
    }

    public function handleItn($transactions)
    {
        if ($transactions == null) {
            return null;
        }

        $xmlResponse = base64_decode($transactions);

        //Log::error((string)$xmlResponse);
        $response = simplexml_load_string($xmlResponse);
        //Log::error((string)$response);
        $transaction = $response
            ->transactions
            ->transaction;
        $hashCheck = hash(
            'sha256',
            $response->serviceID . '|'
            . $transaction->orderID . '|'
            . $transaction->remoteID . '|'
            . $transaction->amount . '|'
            . $transaction->currency . '|'
            . (
            $transaction->gatewayID
                ? ($transaction->gatewayID . '|')
                : ''
            )
            . $transaction->paymentDate . '|'
            . $transaction->paymentStatus . '|'
            . (
            $transaction->paymentStatusDetails
                ? ($transaction->paymentStatusDetails . '|')
                : ''
            )
            . $transaction->startAmount . '|'
            . config('blueMedia.hashKey')
        );
        if ($hashCheck === $response->hash->__toString()) {
            $confirmation = 'CONFIRMED';
            $blueMediaPayment = '';
            $blueMediaPayment->status = constant(Payment::class . '::' . 'STATUS_' . $transaction->paymentStatus);
            $blueMediaPayment->update();
            if ($blueMediaPayment->status === 2) {
                $blueMediaPayment->application()->update(['payment_check' => 3]);
            }
        } else {
            $confirmation = 'NOTCONFIRMED';
        }
        $hash = hash(
            'sha256',
            $response->serviceID . '|'
            . $transaction->orderID . '|'
            . $confirmation . '|'
            . config('blueMedia.hashKey')
        );
        $data = [
            'serviceID' => $response->serviceID,
            'transactionsConfirmations' => [
                'transactionConfirmed' => [
                    'orderID' => $response
                        ->transactions
                        ->transaction
                        ->orderID,
                    'confirmation' => $confirmation,
                ],
            ],
            'hash' => $hash,
        ];
        $xmlData = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><confirmationList></confirmationList>');
        $this->arrayToXml($data, $xmlData);
        //Log::error((string)$xmlData);
        return $xmlData->asXML();
    }
}
