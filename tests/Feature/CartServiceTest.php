<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private function cart(): CartService
    {
        return $this->app->make(CartService::class);
    }

    private function product(int $id, string $name, int $cents): Product
    {
        return Product::factory()->create(['id' => $id, 'name' => $name, 'price_cents' => $cents]);
    }

    public function test_a_new_cart_is_empty()
    {
        $cart = $this->cart();

        $this->assertTrue($cart->isEmpty());
        $this->assertSame(0, $cart->totalCents());
        $this->assertCount(0, $cart->lines());
    }

    public function test_add_puts_a_product_in_the_cart_and_repeating_it_adds_one_more()
    {
        $product = $this->product(1, 'Camiseta', 1999);
        $cart = $this->cart();

        $cart->add($product);
        $this->assertFalse($cart->isEmpty());
        $this->assertSame(1, $cart->lines()->first()['quantity']);

        $cart->add($product);
        $this->assertSame(2, $cart->lines()->first()['quantity']);
    }

    public function test_there_is_no_maximum_quantity()
    {
        $product = $this->product(1, 'Camiseta', 100);
        $cart = $this->cart();

        foreach (range(1, 150) as $ignored) {
            $cart->add($product);
        }

        $this->assertSame(150, $cart->lines()->first()['quantity']);
        $this->assertSame(15000, $cart->totalCents());
    }

    public function test_lines_keep_the_order_in_which_products_were_added()
    {
        $first = $this->product(1, 'Camiseta', 1999);
        $second = $this->product(2, 'Taza', 950);
        $third = $this->product(3, 'Mochila', 3900);
        $cart = $this->cart();

        $cart->add($third);
        $cart->add($first);
        $cart->add($second);
        $cart->add($third);

        $this->assertSame([3, 1, 2], $cart->lines()->pluck('product.id')->all());
    }

    public function test_each_line_has_its_subtotal_in_cents_and_the_total_is_their_sum()
    {
        $shirt = $this->product(1, 'Camiseta', 1999);
        $mug = $this->product(2, 'Taza', 950);
        $cart = $this->cart();

        $cart->add($shirt);
        $cart->add($shirt);
        $cart->add($mug);

        $subtotals = $cart->lines()->pluck('subtotal_cents')->all();

        $this->assertSame([3998, 950], $subtotals);
        $this->assertSame(4948, $cart->totalCents());
        $this->assertSame(array_sum($subtotals), $cart->totalCents());
        $this->assertIsInt($cart->totalCents());
    }

    public function test_prices_are_read_from_the_database_every_time()
    {
        $product = $this->product(1, 'Camiseta', 1000);
        $cart = $this->cart();
        $cart->add($product);

        $product->update(['price_cents' => 2500]);

        $this->assertSame(2500, $cart->totalCents());
    }

    public function test_clear_empties_the_cart()
    {
        $cart = $this->cart();
        $cart->add($this->product(1, 'Camiseta', 1999));

        $cart->clear();

        $this->assertTrue($cart->isEmpty());
        $this->assertSame(0, $cart->totalCents());
    }

    public function test_a_product_deleted_from_the_database_is_dropped_and_pruned()
    {
        $kept = $this->product(1, 'Camiseta', 1999);
        $gone = $this->product(2, 'Taza', 950);
        $cart = $this->cart();
        $cart->add($kept);
        $cart->add($gone);

        $gone->delete();

        $this->assertSame([1], $cart->lines()->pluck('product.id')->all());
        $this->assertSame(1999, $cart->totalCents());
        $this->assertSame([1 => 1], session('cart'));
    }

    public function test_a_cart_whose_products_are_all_deleted_is_empty()
    {
        $product = $this->product(1, 'Camiseta', 1999);
        $cart = $this->cart();
        $cart->add($product);

        $product->delete();

        $this->assertTrue($cart->isEmpty());
    }
}
