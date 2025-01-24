<?php

namespace App\Http\Controllers\Callback;

use App\Enums\TransactionStatus;
use App\Events\Client\UserBalanceUpdated;
use App\Http\Controllers\Controller;
use App\Models\Transaction\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Nekoding\Tripay\TripayFacade;

class TripayCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $privateKey = env('TRIPAY_PRIVATE_KEY');

        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        
        $json = $request->getContent();

        $signature = hash_hmac('sha256', $json, $privateKey);

        if ($signature !== (string) $callbackSignature) {
            return Response::json([
                'success' => false,
                'message' => 'Invalid signature',
            ]);
        }

        if ('payment_status' !== (string) $request->server('HTTP_X_CALLBACK_EVENT')) {
            return Response::json([
                'success' => false,
                'message' => 'Unrecognized callback event, no action was taken',
            ]);
        }

        $data = json_decode($json);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return Response::json([
                'success' => false,
                'message' => 'Invalid data sent by tripay',
            ]);
        }

        $invoiceId = $data->merchant_ref;
        $tripayReference = $data->reference;
        $status = strtoupper((string) $data->status);

        if ($data->is_closed_payment === 1) {
            $transaction = Transaction::where('reference', $invoiceId)
                ->where('status', TransactionStatus::Pending)
                ->first();
            // $invoice = Invoice::where('id', $invoiceId)
            //     ->where('tripay_reference', $tripayReference)
            //     ->where('status', '=', 'UNPAID')
            //     ->first();

            if (!$transaction) {
                return Response::json([
                    'success' => false,
                    'message' => 'No transaction found or already paid: ' . $invoiceId,
                ]);
            }

            $amount = $transaction->amount;

            

            switch ($status) {
                case 'PAID':
                    $transaction->update(['status' => TransactionStatus::Accepted]);
                    
                    break;

                case 'EXPIRED':
                    $transaction->update(['status' => TransactionStatus::Rejected]);
                    break;

                case 'FAILED':
                    $transaction->update(['status' => TransactionStatus::Rejected]);
                    break;

                default:
                    return Response::json([
                        'success' => false,
                        'message' => 'Unrecognized payment status',
                    ]);
            }

            $user = $transaction->causer;
            $user->balance += abs($amount);
            $user->save();

            UserBalanceUpdated::dispatch($user);

            return Response::json(['success' => true]);
        }
    }
}
