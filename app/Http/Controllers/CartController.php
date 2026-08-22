<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddPackageToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\SubscriptionPackage;
use App\Services\Marketplace\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
    ) {}

    public function show(): View
    {
        $user = auth()->user();
        $cart = $this->cartService->getOrCreateOpenCart($user);
        $cart->load(['items.subscriptionPackage.tools']);
        $summary = $this->cartService->summarizeCart($cart);

        return view('cart.show', [
            'cart' => $cart,
            'summary' => $summary,
        ]);
    }

    public function store(AddPackageToCartRequest $request): RedirectResponse
    {
        $package = SubscriptionPackage::with('tools')->findOrFail($request->integer('subscription_package_id'));

        $this->cartService->addPackage(
            $request->user(),
            $package,
            $request->integer('quantity'),
            $request->string('billing_interval')->toString(),
        );

        return back()->with('success', 'Package added to your cart.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        $this->cartService->updateItemQuantity(
            $request->user(),
            $cartItem,
            $request->integer('quantity'),
        );

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(CartItem $cartItem): RedirectResponse
    {
        $this->cartService->removeItem(auth()->user(), $cartItem);

        return back()->with('success', 'Item removed.');
    }
}
