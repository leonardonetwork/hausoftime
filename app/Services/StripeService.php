<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use App\Support\Countries;
use RuntimeException;
use Stripe\StripeClient;

/**
 * Stripe Connect (account Express, "separate charges and transfers"):
 *  - l'acquirente paga la piattaforma (Checkout Session, transfer_group = numero ordine)
 *  - i fondi restano sul saldo HOT finché l'acquirente conferma la ricezione o scadono i giorni
 *  - poi si crea un Transfer verso l'account del venditore (prezzo − commissione + spedizione)
 *
 * Chiavi e segreti si gestiscono da Admin → Configuración → Pagos (cifrati in DB).
 */
class StripeService
{
    /** Paesi in cui il venditore può avere un account con contratto "full" (stessa area della piattaforma). */
    private const FULL_AGREEMENT = ['AT','BE','BG','CY','CZ','DE','DK','EE','ES','FI','FR','GR','HR','HU','IE','IT','LI','LT','LU',
                                    'LV','MT','NL','NO','PL','PT','RO','SE','SI','SK','IS','GB','CH'];

    private ?StripeClient $client = null;

    public function mode(): string
    {
        return Setting::get('stripe_mode', 'test') === 'live' ? 'live' : 'test';
    }

    public function secretKey(): ?string
    {
        return Setting::get("stripe_{$this->mode()}_sk") ?: null;
    }

    public function publishableKey(): ?string
    {
        return Setting::get("stripe_{$this->mode()}_pk") ?: null;
    }

    /** @return string[] */
    public function webhookSecrets(): array
    {
        return array_values(array_filter([
            Setting::get("stripe_{$this->mode()}_whsec"),
            Setting::get("stripe_{$this->mode()}_whsec_connect"),
        ]));
    }

    public function isConfigured(): bool
    {
        return (bool) $this->secretKey();
    }

    public function client(): StripeClient
    {
        if (!$this->client) {
            $key = $this->secretKey();
            if (!$key) {
                throw new RuntimeException('Stripe no está configurado (Admin → Configuración → Pagos).');
            }
            $this->client = new StripeClient(['api_key' => $key, 'max_network_retries' => 2]);
        }
        return $this->client;
    }

    public static function cents(float|string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    // ── Checkout ──────────────────────────────────────────────────────────
    public function createCheckoutSession(Order $order): \Stripe\Checkout\Session
    {
        $order->loadMissing('items');
        $lineItems = [];
        foreach ($order->items as $item) {
            $image = $item->image_url;
            $lineItems[] = [
                'quantity'   => 1,
                'price_data' => [
                    'currency'     => strtolower($order->currency),
                    'unit_amount'  => self::cents($item->price),
                    'product_data' => array_filter([
                        'name'        => $item->title,
                        'description' => $item->reference ? 'Ref. ' . $item->reference : null,
                        'images'      => ($image && str_starts_with($image, 'https://')) ? [$image] : null,
                        'metadata'    => ['watch_id' => (string) $item->watch_id, 'order_item_id' => (string) $item->id],
                    ]),
                ],
            ];
        }

        $en = $order->locale === 'en';
        $params = [
            'mode'                 => 'payment',
            'line_items'           => $lineItems,
            'customer_email'       => $order->email,
            'client_reference_id'  => (string) $order->id,
            'locale'               => $en ? 'en' : 'es',
            'expires_at'           => now()->addMinutes(max(30, (int) Setting::get('reservation_minutes', 30)))->timestamp,
            'success_url'          => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => route('checkout.cancel', ['order' => $order->number]),
            'metadata'             => ['order_id' => (string) $order->id, 'order_number' => $order->number],
            'payment_intent_data'  => [
                'transfer_group' => $order->number,
                'description'    => 'HAUS OF TIME · ' . $order->number,
                'metadata'       => ['order_id' => (string) $order->id, 'order_number' => $order->number],
            ],
            'shipping_options' => [[
                'shipping_rate_data' => [
                    'type'         => 'fixed_amount',
                    'display_name' => $en ? 'FedEx · fully insured' : 'FedEx · asegurado',
                    'fixed_amount' => ['amount' => self::cents($order->shipping_total), 'currency' => strtolower($order->currency)],
                ],
            ]],
        ];

        if ((float) $order->discount_total > 0) {
            $coupon = $this->client()->coupons->create([
                'amount_off'      => self::cents($order->discount_total),
                'currency'        => strtolower($order->currency),
                'duration'        => 'once',
                'max_redemptions' => 1,
                'name'            => $order->coupon_code ?: ($en ? 'Discount' : 'Descuento'),
                'metadata'        => ['order_number' => $order->number],
            ]);
            $params['discounts'] = [['coupon' => $coupon->id]];
        }

        return $this->client()->checkout->sessions->create($params, [
            'idempotency_key' => 'hot-checkout-' . $order->number . '-' . $order->updated_at?->timestamp,
        ]);
    }

    public function retrieveSession(string $id): \Stripe\Checkout\Session
    {
        return $this->client()->checkout->sessions->retrieve($id, ['expand' => ['payment_intent']]);
    }

    public function expireSession(string $id): void
    {
        try {
            $this->client()->checkout->sessions->expire($id);
        } catch (\Throwable) {
            // già completata/scaduta
        }
    }

    // ── Connect ───────────────────────────────────────────────────────────
    public function ensureAccount(User $seller, ?string $countryCode = null): string
    {
        if ($seller->stripe_account_id) return $seller->stripe_account_id;

        $country = strtoupper($countryCode ?: (Countries::codeFromName($seller->country) ?: 'ES'));
        $full = in_array($country, self::FULL_AGREEMENT, true);

        $params = [
            'type'          => 'express',
            'country'       => $country,
            'email'         => $seller->email,
            'capabilities'  => $full
                ? ['card_payments' => ['requested' => true], 'transfers' => ['requested' => true]]
                : ['transfers' => ['requested' => true]],
            'business_profile' => [
                'mcc'                 => '5944', // Jewelry, Watch, Clock, and Silverware Stores
                'product_description' => 'Venta de relojes de lujo en el marketplace HAUS OF TIME',
                'url'                 => config('app.url'),
            ],
            'metadata'      => ['user_id' => (string) $seller->id],
        ];
        if (!$full) {
            $params['tos_acceptance'] = ['service_agreement' => 'recipient'];
        }

        $account = $this->client()->accounts->create($params);
        $seller->forceFill(['stripe_account_id' => $account->id, 'stripe_country' => $country])->save();
        return $account->id;
    }

    public function onboardingLink(User $seller): string
    {
        $link = $this->client()->accountLinks->create([
            'account'     => $seller->stripe_account_id,
            'refresh_url' => route('seller.payouts.connect.refresh'),
            'return_url'  => route('seller.payouts.return'),
            'type'        => 'account_onboarding',
        ]);
        return $link->url;
    }

    public function dashboardLink(User $seller): string
    {
        return $this->client()->accounts->createLoginLink($seller->stripe_account_id)->url;
    }

    public function syncAccount(User $seller, ?\Stripe\Account $account = null): void
    {
        if (!$seller->stripe_account_id) return;
        $account ??= $this->client()->accounts->retrieve($seller->stripe_account_id);
        $seller->forceFill([
            'stripe_details_submitted' => (bool) $account->details_submitted,
            'stripe_payouts_enabled'   => (bool) $account->payouts_enabled
                && (($account->capabilities->transfers ?? null) === 'active'),
            'stripe_country'           => $account->country,
        ])->save();
    }

    // ── Liquidazione e rimborsi ───────────────────────────────────────────
    public function transferToSeller(OrderItem $item): string
    {
        $order = $item->order;
        $seller = $item->seller;
        if (!$seller || !$seller->stripe_account_id) {
            throw new RuntimeException('El vendedor no tiene cuenta de cobros Stripe.');
        }
        $params = [
            'amount'         => self::cents($item->seller_payout),
            'currency'       => strtolower($order->currency),
            'destination'    => $seller->stripe_account_id,
            'transfer_group' => $order->number,
            'description'    => "HAUS OF TIME · {$order->number} · {$item->title}",
            'metadata'       => ['order_id' => (string) $order->id, 'order_item_id' => (string) $item->id],
        ];
        $opts = ['idempotency_key' => 'hot-transfer-item-' . $item->id];

        // Collegare il transfer all'addebito permette di trasferire anche se il saldo è ancora "pending".
        if ($order->stripe_charge_id) {
            try {
                return $this->client()->transfers->create($params + ['source_transaction' => $order->stripe_charge_id], $opts)->id;
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                // es. somma dei transfer > addebito (coupon a carico piattaforma): si usa il saldo disponibile
                $opts['idempotency_key'] .= '-balance';
            }
        }
        return $this->client()->transfers->create($params, $opts)->id;
    }

    public function refund(Order $order, float $amount, string $reason = 'requested_by_customer'): string
    {
        if (!$order->stripe_payment_intent) {
            throw new RuntimeException('Pedido sin pago Stripe.');
        }
        return $this->client()->refunds->create([
            'payment_intent' => $order->stripe_payment_intent,
            'amount'         => self::cents($amount),
            'reason'         => $reason,
            'metadata'       => ['order_number' => $order->number],
        ])->id;
    }

    public function reverseTransfer(OrderItem $item): void
    {
        if ($item->stripe_transfer_id) {
            $this->client()->transfers->createReversal($item->stripe_transfer_id, [
                'amount' => self::cents($item->seller_payout),
            ]);
        }
    }

    public function constructEvent(string $payload, string $signature): \Stripe\Event
    {
        $last = null;
        foreach ($this->webhookSecrets() as $secret) {
            try {
                return \Stripe\Webhook::constructEvent($payload, $signature, $secret);
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                $last = $e;
            }
        }
        throw $last ?? new RuntimeException('Webhook secret no configurado.');
    }
}
