<?php

namespace App\Payments;

use App\User;
use App\UserTransaction;

use Illuminate\Support\Facades\Log;

class Fakturownia
{

    static public function config() {
        return [
            'apiKey' => config('payments.fv.apiKey'),
            'urlAPI' => 'https://'.config('payments.fv.domain').'.fakturownia.pl/invoices.json',
            'urlPDF' => 'https://'.config('payments.fv.domain').'.fakturownia.pl/invoice/',
            'seller_name' => config('payments.fv.seller_name'),
            'seller_vat_id' => config('payments.fv.seller_vat_id'),
            'seller_post_code' => config('payments.fv.seller_post_code'),
            'seller_city' => config('payments.fv.seller_city'),
            'seller_street' => config('payments.fv.seller_street'),
        ];
    }

    static public function urlPDF() {
        extract(self::config());
        return $urlPDF;
    }

    static public function add(UserTransaction $transaction) {
        extract(self::config());

        Log::debug('START Generowanie faktury dla [ID='.$transaction->id.']');

        $user = unserialize($transaction->user_invoice_data);

        $date = date('Y-m-d');

        $object = new \stdClass();
        $object->api_token = 'hCaolbWJMCHSXUlxtaG';

        $object->invoice = new \stdClass();

        $object->invoice->department_id = '739997';
        $object->invoice->number = null;
        $object->invoice->sell_date = $date;
        $object->invoice->issue_date = $date;
        $object->invoice->payment_to = $date;

        $object->invoice->seller_name = $seller_name;
        $object->invoice->seller_tax_no = $seller_vat_id;
        $object->invoice->seller_post_code = $seller_post_code;
        $object->invoice->seller_city = $seller_city;
        $object->invoice->seller_street = $seller_street;

        if ($transaction->bill_format == 'invoice') {
            $object->invoice->kind = 'vat';
            $object->invoice->buyer_email = $user['email'];
            $object->invoice->buyer_name = $user['address_company'];
            $object->invoice->buyer_tax_no = $user['tax_nip'];
            $object->invoice->buyer_post_code = $user['address_post_code'];
            $object->invoice->buyer_city = $user['address_city'];
            $object->invoice->buyer_street = $user['address_street_name'] . ' ' . $user['address_street_number'] . ( $user['address_apartment_number'] ? '/'. $user['address_apartment_number'] : '');
        } else {
            $object->invoice->kind = 'receipt';
            $object->invoice->buyer_name = $user['name'] . ' ' . $user['surname'];
            $object->invoice->buyer_email = $user['email'];
        }

        $object->invoice->paid = $transaction->amount_gross / 100;

        $position = new \stdClass();
        $position->name = $transaction->description;
        $position->tax = $transaction->tax;
        $position->total_price_gross = $transaction->amount_gross / 100;
        $position->quantity = 1;
        $object->invoice->positions = [ $position ];

        $data_string = json_encode($object);
        $parameters  = http_build_query($object);

        $ch = curl_init($urlAPI);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $info = curl_getinfo($ch);
        Log::debug('DEBUG curl:'.curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));

        $result = curl_exec($ch);
        $err = curl_error($ch);
        $response = json_decode($result);

        if ($err) {
              Log::debug('ERROR Generowanie faktury dla [ID='.$transaction->id.'] ' . serialize($err) . "\n" . serialize($response));
              return false;
        }

        Log::debug('DEBUG Generowanie faktury dla [ID='.$transaction->id.'] ' . serialize($err) . "\n" . serialize($response));

        $transaction->service_invoice_data = serialize($response);
        $transaction->service_invoice_token = $response->token;
        $transaction->billed_at = date('Y-m-d H:i:s');
        $transaction->save();
        Log::debug('END OK Generowanie faktury dla [ID='.$transaction->id.']');

        return $transaction;
    }

}
