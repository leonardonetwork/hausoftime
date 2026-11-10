<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Coupon;
use App\Models\ShippingRate;
use App\Models\User;
use App\Models\Watch;
use Illuminate\Support\Collection;

/**
 * Preventivo del checkout: per ogni orologio prezzo, quota di sconto, porto e assicurazione
 * (ogni pezzo viaggia in una spedizione FedEx assicurata separata, dal suo venditore),
 * commissione HOT e importo da liquidare al venditore.
 *
 * Lo sconto coupon è a carico della piattaforma: non riduce l'importo del venditore.
 */
class Pricing
{
    /**
     * @param Collection<int,Watch> $watches
     * @return array{lines:array, subtotal:float, discount:float, shipping:float, total:float,
     *               coupon:?Coupon, coupon_error:?string, country:?Country, shipping_error:?string, notice:?string}
     */
    public function quote(Collection $watches, ?string $countryCode, ?string $couponCode = null, ?string $email = null, ?User $user = null): array
    {
        $country = $countryCode ? Country::with('zone.rates')->where('code', strtoupper($countryCode))->first() : null;
        $shippingError = null;
        if ($countryCode && (!$country || !$country->enabled || !$country->zone || !$country->zone->active)) {
            $shippingError = __('shop.checkout.country_not_available');
        }

        // Coupon
        $coupon = null;
        $couponError = null;
        if ($couponCode) {
            $coupon = Coupon::findByCode($couponCode);
            if (!$coupon) {
                $couponError = __('shop.coupon.invalid');
            } elseif ($err = $coupon->validationError($watches, $email, $user)) {
                $couponError = $err;
                $coupon = null;
            }
        }
        $discountTotal = $coupon ? $coupon->discountFor($watches) : 0.0;
        $eligibleBase = $coupon ? $coupon->eligibleSubtotal($watches) : 0.0;

        $lines = [];
        $allocated = 0.0;
        $eligibleWatches = $coupon ? $watches->filter(fn ($w) => $coupon->appliesToWatch($w))->values() : collect();

        foreach ($watches->values() as $w) {
            $price = $w->effective_price;

            // Ripartizione proporzionale dello sconto; l'ultima riga assorbe gli arrotondamenti
            $discount = 0.0;
            if ($coupon && $eligibleBase > 0 && $coupon->appliesToWatch($w)) {
                $isLast = $eligibleWatches->last()?->id === $w->id;
                $discount = $isLast ? round($discountTotal - $allocated, 2) : round($discountTotal * $price / $eligibleBase, 2);
                $allocated += $discount;
            }

            [$ship, $insurance, $rateName, $eta] = [0.0, 0.0, null, null];
            if ($country && !$shippingError) {
                $rate = $country->zone->rates->first(fn (ShippingRate $r) => $r->appliesTo($price));
                if (!$rate) {
                    $shippingError = __('shop.checkout.no_rate');
                } else {
                    [$ship, $insurance] = $rate->quote($price);
                    $rateName = $rate->name;
                    $eta = $rate->delivery_estimate;
                }
            }
            if ($coupon && $coupon->free_shipping && $coupon->appliesToWatch($w)) {
                [$ship, $insurance] = [0.0, 0.0];
            }

            $commissionRate = $w->seller ? $w->seller->commissionRate() : (float) \App\Models\Setting::get('commission_standard', 7);
            $commission = round($price * $commissionRate / 100, 2);

            $lines[] = [
                'watch'             => $w,
                'price'             => round($price, 2),
                'discount'          => $discount,
                'shipping'          => $ship,
                'insurance'         => $insurance,
                'rate_name'         => $rateName,
                'eta'               => $eta,
                'commission_rate'   => $commissionRate,
                'commission_amount' => $commission,
                // Al venditore: prezzo − commissione + porto/assicurazione (spedisce lui)
                'seller_payout'     => round($price - $commission + $ship + $insurance, 2),
            ];
        }

        $subtotal = round(array_sum(array_column($lines, 'price')), 2);
        $shipping = round(array_sum(array_column($lines, 'shipping')) + array_sum(array_column($lines, 'insurance')), 2);
        $discount = round(array_sum(array_column($lines, 'discount')), 2);

        return [
            'lines'          => $lines,
            'subtotal'       => $subtotal,
            'discount'       => $discount,
            'shipping'       => $shipping,
            'total'          => round($subtotal - $discount + $shipping, 2),
            'coupon'         => $coupon,
            'coupon_error'   => $couponError,
            'country'        => $country,
            'shipping_error' => $shippingError,
            'notice'         => $country?->zone?->notice,
        ];
    }
}
