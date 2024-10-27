<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentMethod;

class PlanController extends Controller
{
    public function updateSubscription(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'plan' => 'required|in:bronze,prata,ouro',
            'billing_date' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Configura o Stripe
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Cria ou atualiza o cliente no Stripe
        $customer = Customer::updateOrCreate(
            ['email' => $user->email],
            ['name' => $user->name]
        );

        // Associa o método de pagamento (token) ao cliente
        $paymentMethod = PaymentMethod::create([
            'type' => 'card',
            'card' => ['token' => $request->input('stripeToken')]
        ]);

        $paymentMethod->attach(['customer' => $customer->id]);

        // Atualize o plano do usuário
        $planId = match($request->input('plan')) {
            'bronze' => 1,
            'prata' => 2,
            'ouro' => 3,
        };

        $user->update([
            'plan_id' => $planId,
            'billing_date' => $request->input('billing_date'),
            'stripe_customer_id' => $customer->id, // Salve o ID do cliente no Stripe
        ]);

        return redirect()->route('profile.show')->with('status', 'subscription-updated');
    }
}
