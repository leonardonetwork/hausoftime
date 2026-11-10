<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
 * Cron cPanel (utente hausoftime):
 *   * * * * * cd /home/hausoftime/public_html && php artisan schedule:run >> /dev/null 2>&1
 */

// Ordini non pagati: libera i pezzi riservati
Artisan::command('hot:expire-reservations', function (OrderService $orders) {
    $n = 0;
    Order::where('status', 'pending_payment')->whereNotNull('reserved_until')->where('reserved_until', '<', now())
        ->each(function (Order $o) use ($orders, &$n) { $orders->expire($o); $n++; });
    \App\Models\Watch::whereNotNull('reserved_until')->where('reserved_until', '<', now())
        ->update(['reserved_until' => null, 'reserved_order_id' => null]);
    $this->info("Reservas caducadas: $n");
})->purpose('Caduca los pedidos no pagados y libera los relojes');

// Liquidazione automatica: X giorni dopo la spedizione senza incidenze, o ricevuti in attesa di Stripe del venditore
Artisan::command('hot:release-funds', function (OrderService $orders) {
    $ok = 0;
    OrderItem::with(['order', 'seller'])
        ->where(fn ($q) => $q->where(fn ($q) => $q->where('status', 'shipped')->where('release_due_at', '<=', now()))
            ->orWhere('status', 'received'))
        ->whereNull('released_at')
        ->each(function (OrderItem $i) use ($orders, &$ok) { if ($orders->release($i)) $ok++; });
    $this->info("Fondos liberados: $ok");
})->purpose('Libera los fondos a los vendedores al vencer el plazo');

// Promemoria spedizione ai venditori in ritardo (una volta al giorno per riga)
Artisan::command('hot:ship-reminders', function (OrderService $orders) {
    OrderItem::with(['order', 'seller'])->where('status', 'awaiting_shipment')
        ->where('ship_by', '<', now()->addHours(12))
        ->where(fn ($q) => $q->whereNull('last_reminder_at')->orWhere('last_reminder_at', '<', now()->subHours(23)))
        ->each(function (OrderItem $i) use ($orders) {
            $s = $i->seller;
            if ($s) {
                $orders->mail($s->email, $s->locale, 'ship-reminder',
                    $s->locale === 'en' ? "Reminder: ship order {$i->order->number}" : "Recordatorio: envía el pedido {$i->order->number}",
                    ['item' => $i, 'order' => $i->order, 'seller' => $s]);
            }
            if ($i->ship_by && $i->ship_by->isPast()) {
                $orders->mailAdmin("Envío con retraso · {$i->order->number}", 'admin-notice', [
                    'title' => 'Un vendedor no ha enviado en plazo',
                    'lines' => ["Pedido: {$i->order->number}", "Artículo: {$i->title}", 'Vendedor: ' . ($s?->email ?? '—'),
                                'Plazo: ' . $i->ship_by->format('d/m/Y')],
                    'url'   => route('admin.orders.show', $i->order),
                ]);
            }
            $i->update(['last_reminder_at' => now()]);
        });
})->purpose('Recuerda a los vendedores los envíos pendientes');

Schedule::command('hot:expire-reservations')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('hot:release-funds')->hourly()->withoutOverlapping();
Schedule::command('hot:ship-reminders')->dailyAt('09:30')->timezone('Europe/Madrid');
Schedule::command('queue:work --stop-when-empty --max-time=50')->everyMinute()->withoutOverlapping();
