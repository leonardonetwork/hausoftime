<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request)
    {
        $q = Order::with(['items', 'user'])->latest();
        if ($s = $request->query('status')) {
            $q->where('status', $s);
        } else {
            $q->where('status', '!=', 'expired');
        }
        if ($term = trim((string) $request->query('q'))) {
            $q->where(fn ($w) => $w->where('number', 'like', "%$term%")->orWhere('email', 'like', "%$term%")
                ->orWhereHas('items', fn ($i) => $i->where('brand', 'like', "%$term%")->orWhere('model', 'like', "%$term%")));
        }
        $orders = $q->paginate(25)->withQueryString();

        $paid = Order::whereNotNull('paid_at');
        $stats = [
            'revenue'    => (clone $paid)->whereNotIn('status', ['refunded'])->sum('total'),
            'orders'     => (clone $paid)->count(),
            'to_ship'    => OrderItem::where('status', 'awaiting_shipment')->count(),
            'late'       => OrderItem::where('status', 'awaiting_shipment')->where('ship_by', '<', now())->count(),
            'disputed'   => OrderItem::where('status', 'disputed')->count(),
            'commission' => OrderItem::whereIn('status', ['awaiting_shipment', 'shipped', 'received', 'released'])->sum('commission_amount'),
            'escrow'     => OrderItem::whereIn('status', ['awaiting_shipment', 'shipped', 'received', 'disputed'])->sum('seller_payout'),
        ];
        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load(['items.seller', 'items.watch', 'events.user', 'user', 'coupon']);
        return view('admin.orders.show', compact('order'));
    }

    public function notes(Request $request, Order $order)
    {
        $order->update($request->validate(['admin_notes' => 'nullable|string|max:5000']));
        return back()->with('success', 'Notas guardadas.');
    }

    public function ship(Request $request, Order $order, OrderItem $item)
    {
        $this->check($order, $item);
        $data = $request->validate(['carrier' => 'required|in:fedex,dhl,ups,other', 'tracking_number' => 'required|string|max:80',
                                    'tracking_url' => 'nullable|url|max:255']);
        try {
            $this->orders->ship($item, $data['carrier'], $data['tracking_number'], $data['tracking_url'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'Envío registrado y comprador notificado.');
    }

    public function release(Order $order, OrderItem $item)
    {
        $this->check($order, $item);
        if ($item->status === 'disputed') {
            $this->orders->closeDispute($item, 'Incidencia cerrada a favor del vendedor');
            $item->refresh();
        }
        if ($item->status === 'awaiting_shipment') {
            return back()->with('error', 'El artículo aún no se ha enviado.');
        }
        $ok = $this->orders->release($item, true);
        return back()->with($ok ? 'success' : 'error', $ok
            ? 'Fondos transferidos al vendedor.'
            : 'No se pudo transferir (revisa el historial: el vendedor puede no tener Stripe activo).');
    }

    public function refund(Request $request, Order $order, OrderItem $item)
    {
        $this->check($order, $item);
        $data = $request->validate(['amount' => 'nullable|numeric|min:0.5', 'relist' => 'nullable|boolean', 'note' => 'nullable|string|max:500']);
        try {
            $this->orders->refundItem($item, isset($data['amount']) ? (float) $data['amount'] : null, $request->boolean('relist'), $data['note'] ?? '');
        } catch (\Throwable $e) {
            return back()->with('error', 'Reembolso no realizado: ' . $e->getMessage());
        }
        return back()->with('success', 'Reembolso emitido en Stripe.');
    }

    public function closeDispute(Request $request, Order $order, OrderItem $item)
    {
        $this->check($order, $item);
        $data = $request->validate(['note' => 'required|string|max:1000']);
        $this->orders->closeDispute($item, $data['note']);
        return back()->with('success', 'Incidencia cerrada.');
    }

    public function cancel(Order $order)
    {
        if ($order->status !== 'pending_payment') {
            return back()->with('error', 'Solo se pueden cancelar pedidos pendientes de pago.');
        }
        $this->orders->expire($order, 'cancelled');
        return back()->with('success', 'Pedido cancelado y relojes liberados.');
    }

    private function check(Order $order, OrderItem $item): void
    {
        abort_unless((int) $item->order_id === (int) $order->id, 404);
    }
}
