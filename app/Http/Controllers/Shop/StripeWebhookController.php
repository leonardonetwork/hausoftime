<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\StripeEvent;
use App\Models\User;
use App\Services\OrderService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Endpoint: https://hausoftime.com/stripe/webhook
 * Eventi account piattaforma: checkout.session.completed, checkout.session.async_payment_succeeded,
 *   checkout.session.async_payment_failed, checkout.session.expired, charge.dispute.created, charge.refunded
 * Eventi Connect (account collegati): account.updated
 */
class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeService $stripe, OrderService $orders)
    {
        try {
            $event = $stripe->constructEvent($request->getContent(), (string) $request->header('Stripe-Signature'));
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook rejected: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        $record = StripeEvent::firstOrCreate(['event_id' => $event->id], ['type' => $event->type]);
        if ($record->processed_at) {
            return response('Already processed', 200);
        }

        $obj = $event->data->object;
        try {
            switch ($event->type) {
                case 'checkout.session.completed':
                case 'checkout.session.async_payment_succeeded':
                    $order = $this->orderFor($obj);
                    if ($order && $obj->payment_status === 'paid') {
                        $pi = $obj->payment_intent ? $stripe->client()->paymentIntents->retrieve($obj->payment_intent) : null;
                        $orders->markPaid($order, $pi?->id, $pi?->latest_charge, $pi?->payment_method_types[0] ?? null);
                    } elseif ($order && $event->type === 'checkout.session.completed') {
                        $order->update(['reserved_until' => now()->addDays(7)]);
                        \App\Models\Watch::where('reserved_order_id', $order->id)->update(['reserved_until' => now()->addDays(7)]);
                        $order->log('awaiting_async_payment', 'Pago pendiente de confirmación (método asíncrono)');
                    }
                    break;

                case 'checkout.session.async_payment_failed':
                case 'checkout.session.expired':
                    if ($order = $this->orderFor($obj)) {
                        $orders->expire($order, $event->type === 'checkout.session.expired' ? 'expired' : 'cancelled');
                    }
                    break;

                case 'account.updated':
                    if ($user = User::where('stripe_account_id', $obj->id)->first()) {
                        $stripe->syncAccount($user, $obj);
                    }
                    break;

                case 'charge.dispute.created':
                    if ($order = Order::where('stripe_charge_id', $obj->charge)->first()) {
                        $order->items()->whereIn('status', ['awaiting_shipment', 'shipped', 'received'])
                            ->update(['status' => 'disputed', 'dispute_opened_at' => now(), 'dispute_reason' => 'Contracargo Stripe: ' . $obj->reason]);
                        $order->log('chargeback', 'Contracargo abierto en Stripe (' . $obj->reason . ')');
                        $order->refreshStatus();
                        $orders->mailAdmin("CONTRACARGO · {$order->number}", 'admin-notice', [
                            'title' => 'Se ha abierto un contracargo en Stripe',
                            'lines' => ["Pedido: {$order->number}", "Motivo: {$obj->reason}", 'Importe: ' . number_format($obj->amount / 100, 2, ',', '.') . ' €'],
                            'url'   => route('admin.orders.show', $order),
                        ]);
                    }
                    break;

                case 'charge.refunded':
                    if ($order = Order::where('stripe_charge_id', $obj->id)->first()) {
                        $order->log('stripe_refund', 'Stripe confirma reembolso: ' . number_format($obj->amount_refunded / 100, 2, ',', '.') . ' €');
                    }
                    break;
            }
        } catch (\Throwable $e) {
            Log::error("Stripe webhook {$event->type} {$event->id}: " . $e->getMessage());
            return response('Error', 500); // Stripe riproverà
        }

        $record->update(['processed_at' => now()]);
        return response('OK', 200);
    }

    private function orderFor(object $session): ?Order
    {
        $id = $session->metadata->order_id ?? $session->client_reference_id ?? null;
        $order = $id ? Order::find($id) : null;
        return $order ?? Order::where('stripe_session_id', $session->id)->first();
    }
}
