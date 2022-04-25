<?php

namespace App\Payments;

use App\User;
use App\UserTransaction;

use Illuminate\Support\Facades\Log;

class Przelewy24
{

    static public function config() {

        $env = config('app.env');

        return [
            'crc' => config('payments.p24.crc'),
            'merchantId' => config('payments.p24.merchantId'),
            'posId' => config('payments.p24.posId'),
            'reportKey' => config('payments.p24.reportKey'),
            'urlAPI' => (
                $env == 'production'
                ?
//                'https://sandbox.przelewy24.pl/api/v1'
                 'https://secure.przelewy24.pl/api/v1'
                :
//                'https://secure.przelewy24.pl/api/v1'
                'https://sandbox.przelewy24.pl/api/v1'
            ),
            'urlReturn' => (
                $env == 'production'
                ?
                'https://testy.iusvitae.pl/app/shop/return'
                :
                'http://localhost:8005/app/shop/return'
            ),
            'urlStatus' => (
                $env == 'production'
                ?
                'https://testy.iusvitae.pl/api/app/packages/buy/status'
                :
                'http://localhost:8005/api/app/packages/buy/status'
            ),
        ];
    }

    static public function urlTransaction( UserTransaction $transaction ) {
        $env = config('app.env');

        return (
            $env == 'production'
            ?
             'https://secure.przelewy24.pl/trnRequest/'.$transaction->p24_token
//            'https://sandbox-go.przelewy24.pl/trnRequest/'.$transaction->p24_token
            :
            'https://sandbox-go.przelewy24.pl/trnRequest/'.$transaction->p24_token
        );
    }

    static public function urlTransactionBase() {
        $env = config('app.env');

        return (
            $env == 'production'
            ?
             'https://secure.przelewy24.pl/trnRequest/'
//            'https://sandbox-go.przelewy24.pl/trnRequest/'
            :
            'https://sandbox-go.przelewy24.pl/trnRequest/'
        );
    }

    static public function register( User $user, UserTransaction $transaction ) {

        extract(self::config());

        $userAuth = $posId.':'.$reportKey;
        $sessionId   = $transaction->hash.':'.$transaction->id;
        $amount      = $transaction->amount_gross;
        $currency    = 'PLN';
        $description = $transaction->description;
        $email       = $user->email;
        $country     = 'PL';

        $jsonSign    = json_encode(
            array(
                "sessionId" => $sessionId,
                "merchantId" => intval($merchantId),
                "amount" => intval($amount),
                "currency" => $currency,
                "crc" => $crc
            ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        Log::debug('Przelewy24::register() ERROR  : '.$crc);
        $sign     = hash('sha384', $jsonSign);
        $data     = array(
            "merchantId" => intval($merchantId),
            "posId" => intval($posId),
            "sign" => $sign,
            "amount" => intval($amount),
            "currency" => $currency,
            "client" => $user->name . ' ' . $user->surname,
            "description" => $description,
            "email" => $email,
            "country" => $country,
            "urlReturn" => $urlReturn,
            "urlStatus" => $urlStatus,
            "sessionId" => $sessionId,
            "method" => 0,
            "language" => "PL",
            "encoding" => "UTF-8",
        );

        $data_string = json_encode($data);
        $parameters  = http_build_query($data);
        $ch = curl_init( $urlAPI . '/transaction/register' );

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $userAuth );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $err = curl_error($ch);
        $response = json_decode($result);
        $info = curl_getinfo($ch);

        curl_close($ch);

        Log::debug('Przelewy24::register() ERROR ['.$err.'] : '.$info['url']);
        Log::debug('Przelewy24::register() ERROR ['.$err.'] : '.$parameters);
        if ($err) {
            Log::debug('Przelewy24::register() ERROR ['.$err.'] : '.serialize($response));
            return false;
        }

        if (empty($response->data->token)) {
            Log::debug('Przelewy24::register() ERROR TOKEN : '.serialize($response));
            return false;
        }

        $transaction->registered_at = date('Y-m-d H:i:s');
        $transaction->p24_token = $response->data->token;
        $transaction->status = 'registered';
        $transaction->save();
        return true;
    }

    static public function verify($input) {

      $conf = self::config();

      $userAuth = $conf['posId'].':'.$conf['reportKey'];
      $crc = $conf['crc'];
      $merchantId = $conf['merchantId'];
      $poId = $conf['posId'];

      $sessionId = $input['sessionId'];
      $amount = $input['amount'];
      $currency = $input['currency'];
      $orderId = $input['orderId'];

      $jsonSign = json_encode(
          array(
          "sessionId" => $sessionId,
          "orderId" => intval($orderId),
          "amount" => intval($amount),
          "currency" => $currency,
          "crc" => $crc
          ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
      );
      $sign = hash('sha384', $jsonSign);

      $data = array(
          "merchantId" => intval($merchantId),
          "posId" => intval($poId),
          "sessionId" => $sessionId,
          "amount" => intval($amount),
          "currency" => $currency,
          "orderId" => $orderId,
          "sign" => $sign,
      );

      $data_string = json_encode($data);
      $parameters = http_build_query($data);
      $ch = curl_init($conf['urlAPI'].'/transaction/verify');
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_USERPWD, $userAuth );
      curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $result = curl_exec($ch);
      $response = json_decode($result);
      return $response;
    }
}
