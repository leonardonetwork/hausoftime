<?php

namespace App\Services;

use App\Mail\ShopMail;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use App\Models\Watch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

/**
 * Ciclo di vita dell'ordine:
 *  pending_payment → paid → (righe: awaiting_shipment → shipped → received → released) → completed
 *  con incidenze (disputed), rimborsi (refunded) e scadenza del pagamento (expired).
 */
class OrderService
{
    public function __construct(private StripeService $stripe) {}

    // ── Creazione + riserva ───────────────────────────────────────────────
    /**
     * Crea l'ordine dal preventivo e riserva gli orologi (lock pessimista: un pezzo unico
     * non può finire in due checkout contemporanei).
     */
    public function createFromQuote(array $quote, User $user, array $shipping, ?array $billing, ?string $notes): Order
    {
        $minutes = max(30, (int) Setting::get('reservation_minutes', 30)) + 5;

        return DB::transaction(function () use ($quote, $user, $shipping, $billing, $notes, $minutes) {
            $ids = collect($quote['lines'])->map(fn ($l) => $l['watch']->id)->all();
            $locked = Watch::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

            foreach ($ids as $id) {
                $w = $locked->get($id);
                if (!$w || !$w->isPublished() || $w->isReserved()) {
                    throw new RuntimeException(__('shop.checkout.item_unavailable', ['item' => $w?->title ?? '#' . $id]));
                }
            }

            $order = null;
            for ($attempt = 0; $attempt < 3 && !$order; $attempt++) {
                try {
                    $order = Order::create([
                        'number'           => Order::nextNumber(),
                        'user_id'          => $user->id,
                        'email'            => $user->email,
                        'status'           => 'pending_payment',
                        'currency'         => 'EUR',
                        'subtotal'         => $quote['subtotal'],
                        'discount_total'   => $quote['discount'],
                        'shipping_total'   => $quote['shipping'],
                        'total'            => $quote['total'],
                        'coupon_id'        => $quote['coupon']?->id,
                        'coupon_code'      => $quote['coupon']?->code,
                        'shipping_address' => $shipping,
                        'billing_address'  => $billing,
                        'shipping_country' => $shipping['country_code'] ?? null,
                        'customer_notes'   => $notes,
                        'reserved_until'   => now()->addMinutes($minutes),
                        'locale'           => app()->getLocale(),
                        'ip_address'       => request()->ip(),
                    ]);
                } catch (\Illuminate\Database\UniqueConstraintViolationException) {
                    $order = null; // numero già usato da un checkout concorrente: si riprova
                }
            }
            if (!$order) throw new RuntimeException('No se pudo generar el número de pedido.');

            foreach ($quote['lines'] as $l) {
                $w = $l['watch'];
                $order->items()->create([
                    'watch_id'           => $w->id,
                    'seller_id'          => $w->seller_id,
                    'status'             => 'pending',
                    'brand'              => $w->brand,
                    'model'              => $w->model,
                    'reference'          => $w->reference,
                    'image'              => $w->images[0] ?? null,
                    'price'              => $l['price'],
                    'discount'           => $l['discount'],
                    'shipping'           => $l['shipping'],
                    'insurance'          => $l['insurance'],
                    'shipping_rate_name' => $l['rate_name'],
                    'commission_rate'    => $l['commission_rate'],
                    'commission_amount'  => $l['commission_amount'],
                    'seller_payout'      => $l['seller_payout'],
                ]);
            }

            Watch::whereIn('id', $ids)->update(['reserved_until' => $order->reserved_until, 'reserved_order_id' => $order->id]);
            $order->log('created', 'Pedido creado, piezas reservadas hasta ' . $order->reserved_until->format('H:i'));

            return $order;
        });
    }

    // ── Pagamento ─────────────────────────────────────────────────────────
    public function markPaid(Order $order, ?string $paymentIntentId, ?string $chargeId, ?string $method = null): bool
    {
        $done = DB::transaction(function () use ($order, $paymentIntentId, $chargeId, $method) {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();
            if ($order->paid_at) return false;

            $shipDays = max(1, (int) Setting::get('ship_within_days', 2));
            $order->update([
                'status'                => 'paid',
                'paid_at'               => now(),
                'reserved_until'        => null,
                'stripe_payment_intent' => $paymentIntentId ?: $order->stripe_payment_intent,
                'stripe_charge_id'      => $chargeId ?: $order->stripe_charge_id,
                'payment_method'        => $method,
            ]);

            foreach ($order->items as $item) {
                $item->update(['status' => 'awaiting_shipment', 'ship_by' => self::addBusinessDays(now(), $shipDays)]);
                if ($item->watch_id) {
                    Watch::whereKey($item->watch_id)->update([
                        'status' => 'sold', 'sold_at' => now(), 'reserved_until' => null, 'reserved_order_id' => null,
                    ]);
                }
            }

            if ($order->coupon_id && (float) $order->discount_total > 0) {
                Coupon::whereKey($order->coupon_id)->increment('used_count');
                CouponRedemption::create([
                    'coupon_id' => $order->coupon_id, 'order_id' => $order->id, 'user_id' => $order->user_id,
                    'email' => $order->email, 'amount' => $order->discount_total,
                ]);
            }
            $order->log('paid', 'Pago confirmado por Stripe' . ($method ? " ($method)" : ''));
            return true;
        });

        if ($done) {
            $order->refresh()->load('items.seller');
            $this->notifyPaid($order);
        }
        return $done;
    }

    public function expire(Order $order, string $reason = 'expired'): void
    {
        DB::transaction(function () use ($order, $reason) {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();
            if ($order->paid_at || !in_array($order->status, ['pending_payment'], true)) return;
            $order->update(['status' => $reason === 'cancelled' ? 'cancelled' : 'expired', 'reserved_until' => null,
                            'cancelled_at' => now()]);
            $order->items()->update(['status' => 'cancelled']);
            Watch::where('reserved_order_id', $order->id)->update(['reserved_until' => null, 'reserved_order_id' => null]);
            $order->log($reason, $reason === 'cancelled' ? 'Pago cancelado por el cliente' : 'Reserva caducada sin pago');
        });
        if ($order->stripe_session_id) {
            $this->stripe->expireSession($order->stripe_session_id);
        }
    }

    // ── Spedizione (venditore) ────────────────────────────────────────────
    public function ship(OrderItem $item, string $carrier, string $tracking, ?string $trackingUrl = null): void
    {
        if ($item->status !== 'awaiting_shipment') {
            throw new RuntimeException('Este artículo no está pendiente de envío.');
        }
        $days = max(1, (int) Setting::get('release_after_days', 30));
        $item->update([
            'status'          => 'shipped',
            'carrier'         => $carrier,
            'tracking_number' => trim($tracking),
            'tracking_url'    => $trackingUrl ?: null,
            'shipped_at'      => now(),
            'release_due_at'  => now()->addDays($days),
        ]);
        $order = $item->order;
        $order->log('shipped', "Enviado con {$carrier} · {$tracking}", [], $item);
        $order->refreshStatus();

        $this->mail($order->email, $order->locale, 'order-shipped',
            $order->locale === 'en' ? "Your watch is on its way · {$order->number}" : "Tu reloj está en camino · {$order->number}",
            ['order' => $order, 'item' => $item]);
    }

    // ── Conferma ricezione (acquirente) e liquidazione ────────────────────
    public function confirmReceived(OrderItem $item): void
    {
        if (!$item->canBeConfirmedByBuyer()) {
            throw new RuntimeException('No se puede confirmar la recepción de este artículo.');
        }
        $item->update(['status' => 'received', 'received_at' => now()]);
        $item->order->log('received', 'El comprador confirmó la recepción', [], $item);
        $this->release($item);
    }

    /** Trasferisce al venditore. Se il venditore non ha ancora Stripe attivo resta "received" e si riprova. */
    public function release(OrderItem $item, bool $force = false): bool
    {
        $item->refresh();
        if ($item->released_at || in_array($item->status, ['refunded', 'cancelled'], true)) return false;
        if ($item->status === 'disputed' && !$force) return false;

        $order = $item->order;
        $seller = $item->seller;
        if (!$seller || !$seller->hasPayoutsReady()) {
            if ($item->status === 'shipped') {
                $item->update(['status' => 'received', 'received_at' => $item->received_at ?? now()]);
            }
            $order->log('release_pending', 'Liberación pendiente: el vendedor aún no tiene los cobros Stripe activos', [], $item);
            if ($seller) {
                $this->mail($seller->email, $seller->locale, 'payout-setup-needed',
                    $seller->locale === 'en' ? 'Activate payouts to receive your sale' : 'Activa tus cobros para recibir tu venta',
                    ['item' => $item, 'order' => $order, 'seller' => $seller]);
            }
            return false;
        }

        try {
            $transferId = $this->stripe->transferToSeller($item);
        } catch (\Throwable $e) {
            Log::error("Transfer item {$item->id} failed: " . $e->getMessage());
            $order->log('release_failed', 'Error en la transferencia: ' . $e->getMessage(), [], $item);
            return false;
        }

        $item->update(['status' => 'released', 'released_at' => now(), 'stripe_transfer_id' => $transferId,
                       'received_at' => $item->received_at ?? now()]);
        $order->log('released', 'Fondos liberados al vendedor (' . Watch::money($item->seller_payout, true) . ')', ['transfer' => $transferId], $item);
        $order->refreshStatus();

        $this->mail($seller->email, $seller->locale, 'funds-released',
            $seller->locale === 'en' ? "Funds released · {$order->number}" : "Fondos liberados · {$order->number}",
            ['item' => $item, 'order' => $order, 'seller' => $seller]);
        return true;
    }

    // ── Incidenze ─────────────────────────────────────────────────────────
    public function openDispute(OrderItem $item, string $reason): void
    {
        if (!$item->canOpenDispute()) {
            throw new RuntimeException('No se puede abrir una incidencia para este artículo.');
        }
        $item->update(['status' => 'disputed', 'dispute_opened_at' => now(), 'dispute_reason' => $reason, 'dispute_closed_at' => null]);
        $order = $item->order;
        $order->log('dispute_opened', $reason, [], $item);
        $order->refreshStatus();
        $this->mailAdmin("Incidencia abierta · {$order->number}", 'admin-notice', [
            'title' => 'El comprador ha abierto una incidencia',
            'lines' => ["Pedido: {$order->number}", "Artículo: {$item->title}", "Motivo: {$reason}"],
            'url'   => route('admin.orders.show', $order),
        ]);
        if ($item->seller) {
            $this->mail($item->seller->email, $item->seller->locale, 'dispute-opened',
                $item->seller->locale === 'en' ? "Issue opened · {$order->number}" : "Incidencia abierta · {$order->number}",
                ['item' => $item, 'order' => $order]);
        }
    }

    /** Admin: chiude l'incidenza tornando allo stato precedente (spedito / da spedire). */
    public function closeDispute(OrderItem $item, string $note): void
    {
        $back = $item->shipped_at ? 'shipped' : 'awaiting_shipment';
        $item->update(['status' => $back, 'dispute_closed_at' => now()]);
        $item->order->log('dispute_closed', $note, [], $item);
        $item->order->refreshStatus();
    }

    /** Admin: rimborso di una riga (prima della liquidazione storna anche il transfer se esiste). */
    public function refundItem(OrderItem $item, ?float $amount = null, bool $relist = false, string $note = ''): void
    {
        $order = $item->order;
        $amount = round($amount ?? $item->line_total, 2);
        if ($amount <= 0 || $amount > $item->line_total + 0.001) {
            throw new RuntimeException('Importe de reembolso no válido.');
        }
        if ($item->stripe_transfer_id) {
            $this->stripe->reverseTransfer($item);
        }
        $refundId = $this->stripe->refund($order, $amount);
        $item->update([
            'status' => 'refunded', 'refunded_at' => now(), 'refunded_amount' => $amount, 'stripe_refund_id' => $refundId,
            'dispute_closed_at' => $item->dispute_opened_at ? now() : null,
        ]);
        if ($relist && $item->watch_id) {
            Watch::whereKey($item->watch_id)->update(['status' => 'approved', 'sold_at' => null]);
        }
        $order->log('refunded', 'Reembolso ' . Watch::money($amount, true) . ($note ? " · $note" : ''), ['refund' => $refundId], $item);
        $order->refreshStatus();
        $this->mail($order->email, $order->locale, 'order-refunded',
            $order->locale === 'en' ? "Refund issued · {$order->number}" : "Reembolso emitido · {$order->number}",
            ['order' => $order, 'item' => $item, 'amount' => $amount]);
    }

    // ── Notifiche ─────────────────────────────────────────────────────────
    private function notifyPaid(Order $order): void
    {
        $en = $order->locale === 'en';
        $this->mail($order->email, $order->locale, 'order-confirmation',
            $en ? "Order confirmed · {$order->number}" : "Pedido confirmado · {$order->number}", ['order' => $order]);

        /** @var Collection<int,OrderItem> $bySeller */
        foreach ($order->items->groupBy('seller_id') as $items) {
            $seller = $items->first()->seller;
            if (!$seller) continue;
            $this->mail($seller->email, $seller->locale, 'seller-new-sale',
                $seller->locale === 'en' ? "New sale · ship within the deadline · {$order->number}" : "Nueva venta · envíalo en plazo · {$order->number}",
                ['order' => $order, 'items' => $items, 'seller' => $seller]);
        }

        $this->mailAdmin("Nuevo pedido pagado · {$order->number} · " . Watch::money($order->total, true), 'admin-notice', [
            'title' => 'Nuevo pedido pagado',
            'lines' => array_merge(
                ["Pedido: {$order->number}", "Cliente: {$order->email}", 'Total: ' . Watch::money($order->total, true)],
                $order->items->map(fn ($i) => "· {$i->title} — vendedor: " . ($i->seller?->email ?? '—'))->all()
            ),
            'url'   => route('admin.orders.show', $order),
        ]);
    }

    public function mail(?string $to, ?string $locale, string $template, string $subject, array $data): void
    {
        if (!$to) return;
        try {
            Mail::to($to)->send(new ShopMail($template, $subject, $data, $locale === 'en' ? 'en' : 'es'));
        } catch (\Throwable $e) {
            Log::error("Shop mail [$template] to $to failed: " . $e->getMessage());
        }
    }

    public function mailAdmin(string $subject, string $template, array $data): void
    {
        $this->mail(Setting::get('orders_email') ?: Setting::get('contact_email', 'support@hausoftime.com'), 'es', $template, $subject, $data);
    }

    public static function addBusinessDays(Carbon $from, int $days): Carbon
    {
        $d = $from->copy();
        while ($days > 0) {
            $d->addDay();
            if (!$d->isWeekend()) $days--;
        }
        return $d->endOfDay();
    }
}
