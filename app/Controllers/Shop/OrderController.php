<?php

namespace app\Controllers\Shop;

use App\Core\Controller;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Template;
use App\Core\Validator;
use App\Models\Order;
use App\Models\Product;

class OrderController extends Controller
{

    public function show(Request $request): Template
    {
        $order = (new Order())
            ->query()
            ->with('products')
            ->find($request->get('order'))
            ->get();

        // load the category for each product
        foreach ($order->products as $product) {
            /* @var Product $product */
            $product->load('category');
        }

        return view('shop.order', [
            'title' => 'Order',
            'order' => $order,
            'shipping' => '10',
            'tax' => '.10',
        ]);
    }

    public function store(Request $request): Response
    {

        $validated = (new Validator())->validate($request->all(), [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'address' => ['required'],
            'city' => ['required'],
            'state' => ['required'],
            'postcode' => ['required'],
            'contact_number' => 'required',
            'card_name' => 'required',
            'card_number' => 'required',
            'expiry_date' => 'required',
            'ccv' => 'required',
        ]);

        $ids = array_values(
            array_map(fn($item) => $item['product_id'], session()->get('cart'))
        );

        $products = (new Product())
            ->query()
            ->select('id', 'price')
            ->whereIn('id', $ids)
            ->get();

        $items = session()->get('cart');

        $total = 0;
        foreach ($products->toArray() as $product) {
            foreach ($items as $item) {
                if ($item['product_id'] == $product['id']) {
                    $total += $product['price'] * $item['quantity'];
                }
            }
        }

        // concatenate the address
        $address = $validated['address'] . ', ' . $validated['city'] . ', ' . $validated['state'] . ', ' . $validated['postcode'];

        $new = (new Order())
            ->query()
            ->create([
                'status' => 'pending',
                'user_id' => auth()->user()->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'address' => $address,
                'contact_number' => $validated['contact_number'],
                'card_name' => $validated['card_name'],
                'card_number' => $validated['card_number'],
                'expiry_date' => $validated['expiry_date'],
                'ccv' => $validated['ccv'],
                'purchase_date' => now(),
                'total' => $total,
            ])->save();

        if ($new === false) {
            session()->flash('flash-message', 'Error: Order not created');
            return redirect()->back();
        }

        foreach ($products->toArray() as $product) {
            foreach ($items as &$item) {
                // do not use strict comparison here
                if ($item['product_id'] == $product['id']) {
                    $item['price'] = $product['price'];
                }
            }
        }
        unset($item);


        // find the order we just created
        $order = (new Order())
            ->query()
            ->where('user_id', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // attach the products to the order
        $order->products()->attach($items);

        // now empty the cart
        session()->remove('cart');

        // add the cart items to the order
        session()->flash('flash-message', 'Order created successfully');

        return redirect()->route('orders.show', ['order' => $order->id]);
    }

    public function destroy(Request $request): Response
    {

        // find the order & delete it,
        //products will be deleted to because of cascade
        $removed = (new Order())->query()->find($request->get('order'))
            ->delete()->save();

        // check order was deleted
        if ($removed === false) {
            session()->flash('flash-message', 'Error: Order not deleted');
            return redirect()->back();
        }

        session()->flash('flash-message', 'Order deleted successfully');
        return redirect()->route('dashboard.index');
    }

}