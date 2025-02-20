<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;

class PaymentController extends Controller
{
    public function deposit(Request $request)
    {
        $paymentMethod = $request->input('pay-method');
        $amount = $request->input('amount');
        $currency = $request->input('currency');

        if ($paymentMethod == 'easymoney') {
            return $this->processEasyMoney($amount, $currency);
        } elseif ($paymentMethod == 'superwalletz') {
            return $this->paySuperWalletz($amount, $currency);
        } else {
            return response()->json(['message' => 'Invalid payment method'], 400);
        }
    }

    private function processEasyMoney($amount, $currency)
    {
        $amount = (int) $amount;

        $response = Http::timeout(120)->post('http://localhost:3000/process', [
            'amount' => $amount,
            'currency' => $currency,
        ]);

        $this->logRequest('EasyMoney', compact('amount', 'currency'), $response->json());

        if ($response->successful()) {
            $this->saveTransaction('EasyMoney', $amount, $currency, 'success');
            return response()->json(['message' => 'Payment processed successfully']);
        } else {
            $this->saveTransaction('EasyMoney', $amount, $currency, 'failed');
            return response()->json(['message' => 'Payment failed'], 400);
        }
    }

    private function paySuperWalletz($amount, $currency)
    {
        $callbackUrl = url('/superwalletz-callback');

        $response = Http::post('http://localhost:3000/pay', [
            'amount' => $amount,
            'currency' => $currency,
            'callback_url' => $callbackUrl,
        ]);

        $this->logRequest('SuperWalletz', compact('amount', 'currency', 'callbackUrl'), $response->json());

        if ($response->successful()) {
            $transactionId = $response->json()['transaction_id'];
            $this->saveTransaction('SuperWalletz', $amount, $currency, 'pending', $transactionId);
            return response()->json(['message' => 'Payment initiated successfully', 'transaction_id' => $transactionId]);
        } else {
            $this->saveTransaction('SuperWalletz', $amount, $currency, 'failed');
            return response()->json(['message' => 'Payment failed'], 400);
        }
    }

    public function superWalletzCallback(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $status = $request->input('status');

        $transaction = Transaction::where('transaction_id', $transactionId)->first();
        if ($transaction) {
            $transaction->status = $status;
            $transaction->save();
        }

        $this->logRequest('SuperWalletz Callback', $request->all(), []);

        return response()->json(['message' => 'Callback received']);
    }

    private function saveTransaction($provider, $amount, $currency, $status, $transactionId = null)
    {
        Transaction::create([
            'provider' => $provider,
            'amount' => $amount,
            'currency' => $currency,
            'status' => $status,
            'transaction_id' => $transactionId,
        ]);
    }

    private function logRequest($provider, $request, $response)
    {
        $log = [
            'provider' => $provider,
            'request' => $request,
            'response' => $response,
        ];

        info(json_encode($log));

        return $log;
    }
}