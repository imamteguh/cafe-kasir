<?php
namespace App\Helpers;

use App\Models\Product;
use Illuminate\Support\Facades\Cookie;

class CartManagement {
    // add item to cart
    static public function addItemToCart($product_id) {
        $cartItems = self::getCartItemsFromCookie();
        $existingItem = null;
        foreach ($cartItems as $key => $item) {
            if ($item['product_id'] == $product_id) {
                $existingItem = $key;
                break;
            }
        }

        if ($existingItem !== null) {
            $cartItems[$existingItem]['quantity']++;
            $cartItems[$existingItem]['subtotal'] = $cartItems[$existingItem]['quantity'] * $cartItems[$existingItem]['price'];
        } else {
            $product = Product::where('id', $product_id)->first();
            if ($product) {
                $cartItems[] = [
                    'product_id' => $product_id,
                    'name' => $product->name,
                    'quantity' => 1,
                    'price' => $product->price,
                    'subtotal' => $product->price,
                    'image' => $product->image,
                ];
            }
        }

        self::addCartItemsToCookie($cartItems);
        return count($cartItems);
    }

    // remove item from cart
    static public function removeCartItem($product_id) {
        $cartItems = self::getCartItemsFromCookie();
        foreach ($cartItems as $key => $item) {
            if ($item['product_id'] == $product_id) {
                unset($cartItems[$key]);
                break;
            }
        }
        self::addCartItemsToCookie($cartItems);
        return $cartItems;
    }

    // add cart items to cookie
    static public function addCartItemsToCookie($cartItems) {
        Cookie::queue('cart_items', json_encode($cartItems), 60 * 24 * 30);
    }

    // clear cart items from cookie
    static public function clearCartItems() {
        Cookie::queue(Cookie::forget('cart_items'));
    }

    // get all cart items from cookie
    static public function getCartItemsFromCookie() {
        $cartItems = json_decode(Cookie::get('cart_items'), true);
        if (!$cartItems) {
            $cartItems = [];
        }

        return $cartItems;
    }

    // increment item quantity
    static public function incrementQuantityToCartItem($product_id) {
        $cartItems = self::getCartItemsFromCookie();
        foreach ($cartItems as $key => $item) {
            if ($item['product_id'] == $product_id) {
                $cartItems[$key]['quantity']++;
                $cartItems[$key]['subtotal'] = $cartItems[$key]['quantity'] * $cartItems[$key]['price'];
                break;
            }
        }
        self::addCartItemsToCookie($cartItems);
        return $cartItems;
    }

    // decrement item quantity
    static public function decrementQuantityToCartItem($product_id) {
        $cartItems = self::getCartItemsFromCookie();
        foreach ($cartItems as $key => $item) {
            if ($item['product_id'] == $product_id) {
                if ($cartItems[$key]['quantity'] > 1) {
                    $cartItems[$key]['quantity']--;
                    $cartItems[$key]['subtotal'] = $cartItems[$key]['quantity'] * $cartItems[$key]['price'];
                    break;
                }
            }
        }
        self::addCartItemsToCookie($cartItems);
        return $cartItems;
    }

    // calculate total price
    static public function calculateTotalPrice($cartItems) {
        return array_sum(array_column($cartItems, 'subtotal'));
    }
}