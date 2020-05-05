<?php

namespace Tests\Feature;

use App\Payment;
use App\User;
use Carbon\Factory;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentsTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function not_authenticated_users_cant_create_a_new_invoice()
    {
        $this->withoutExceptionHandling([AuthenticationException::class]);

        $this->get('/payments/create')
            ->assertRedirect('login');
    }

    /** @test */
    public function customer_can_see_a_form_for_creating_new_invoice()
    {
        $this->withoutExceptionHandling();
        $user = \factory(User::class)->create();

        $this->actingAs($user)
            ->get('/payments/create')
            ->assertStatus(200)
            ->assertSee('Create new Invoice');
    }

    /** @test */
    public function guest_cant_create_a_new_payment()
    {
        $this->withoutExceptionHandling([AuthenticationException::class]);
        $faker = \Faker\Factory::create();

        $response = $this->json('post', 'payments', [
            'email' => $faker->safeEmail(),
            'amount' => $faker->numberBetween(10, 10000),
            'currency' => $faker->currencyCode(),
            'name' => $faker->name(),
            'description' => $faker->text(),
            'message' => $faker->text(),

        ]);

        $response->assertStatus(401);
        $this->assertEquals(0, Payment::count());
    }

    /** @test */
    public function user_can_create_a_new_payment()
    {
        $this->withoutExceptionHandling([AuthenticationException::class]);
        $user = \factory(User::class)->create();
        $faker = \Faker\Factory::create();

        $email = $faker->safeEmail();
        $amount = $faker->numberBetween(10, 10000);
        $currency = $faker->currencyCode();
        $name = $faker->name();
        $description = $faker->text();

        $response = $this->actingAs($user)->json('post', 'payments', [
            'email' => $email,
            'amount' => $amount,
            'currency' => $currency,
            'name' => $name,
            'description' => $description,
            'message' => $description,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, Payment::count());

        tap(Payment::first(), function ($payment) use ($user, $description, $name, $currency, $amount, $email) {
            $this->assertEquals($user->id, $payment->user_id);
            $this->assertEquals($email, $payment->email);
            $this->assertEquals($amount, $payment->amount);
            $this->assertEquals($currency, $payment->currency);
            $this->assertEquals($description, $payment->description);
            $this->assertEquals($description, $payment->message);
        });
    }

    /** @test */
    public function email_field_is_required_to_create_a_payment()
    {
        $user = \factory(User::class)->create();
        $faker = \Faker\Factory::create();

        $response = $this->actingAs($user)->json('post', 'payments', [
            'amount' => $faker->numberBetween(10, 10000),
            'currency' => $faker->currencyCode(),
            'name' => $faker->name(),
            'description' => $faker->text(),
            'message' => $faker->text(),
        ]);

        $response->assertStatus(422);
        $this->assertEquals(0, Payment::count());
        $response->assertJsonValidationErrors('email');
    }

    /** @test */
    public function email_field_should_be_a_valid_email_to_create_a_payment()
    {
        $user = \factory(User::class)->create();
        $faker = \Faker\Factory::create();

        $response = $this->actingAs($user)->json('post', 'payments', [
            'email' => 'test',
            'amount' => $faker->numberBetween(10, 10000),
            'currency' => $faker->currencyCode(),
            'name' => $faker->name(),
            'description' => $faker->text(),
            'message' => $faker->text(),
        ]);

        $response->assertStatus(422);
        $this->assertEquals(0, Payment::count());
        $response->assertJsonValidationErrors('email');
    }

    /** @test */
    public function amount_field_is_required_to_create_a_payment()
    {
        $user = \factory(User::class)->create();
        $faker = \Faker\Factory::create();

        $response = $this->actingAs($user)->json('post', 'payments', [
            'email' => $faker->safeEmail(),
            'currency' => $faker->currencyCode(),
            'name' => $faker->name(),
            'description' => $faker->text(),
            'message' => $faker->text(),
        ]);

        $response->assertStatus(422);
        $this->assertEquals(0, Payment::count());
        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function amount_field_should_be_a_integer_to_create_a_payment()
    {
        $user = \factory(User::class)->create();
        $faker = \Faker\Factory::create();

        $response = $this->actingAs($user)->json('post', 'payments', [
            'email' => $faker->safeEmail(),
            'amount' => 'test',
            'currency' => $faker->currencyCode(),
            'name' => $faker->name(),
            'description' => $faker->text(),
            'message' => $faker->text(),
        ]);

        $response->assertStatus(422);
        $this->assertEquals(0, Payment::count());
        $response->assertJsonValidationErrors('amount');
    }
}
