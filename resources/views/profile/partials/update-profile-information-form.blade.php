<section class="mt-8">
@if ($user->plan_id != 4)
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Atualização de Plano') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Escolha um novo plano e configure os detalhes de pagamento.") }}
        </p>
    </header>

    <form id="subscription-form" method="post" action="{{ route('subscription.update') }}" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-input-label for="plan" :value="__('Plano')" />
            <select id="plan" name="plan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300">
                <option value="bronze" {{ $user->plan_id == 1 ? 'selected' : '' }}>Bronze - R$80</option>
                <option value="prata" {{ $user->plan_id == 2 ? 'selected' : '' }}>Prata - R$120</option>
                <option value="ouro" {{ $user->plan_id == 3 ? 'selected' : '' }}>Ouro - R$180</option>
            </select>
        </div>

        <!-- Elemento de cartão de crédito do Stripe -->
        <div>
            <x-input-label :value="__('Dados do Cartão')" />
            <div id="card-element" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></div>
            <!-- Mensagem de erro do Stripe -->
            <div id="card-errors" role="alert" class="text-red-500 mt-2"></div>
        </div>

        <div>
            <x-input-label for="billing_date" :value="__('Data de Cobrança')" />
            <x-text-input id="billing_date" name="billing_date" type="date" class="mt-1 block w-full" required />
            <x-input-error class="mt-2" :messages="$errors->get('billing_date')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Atualizar Plano') }}</x-primary-button>

            @if (session('status') === 'subscription-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Plano atualizado com sucesso.') }}</p>
            @endif
        </div>
    </form>
    @endif
</section>

<script src="https://js.stripe.com/v3/"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const stripe = Stripe("your-publishable-key");
        const elements = stripe.elements();
        const cardElement = elements.create("card");
        cardElement.mount("#card-element");

        const form = document.getElementById("subscription-form");
        form.addEventListener("submit", async (event) => {
            event.preventDefault();
            
            const { token, error } = await stripe.createToken(cardElement);
            if (error) {
                // Exibir erro ao usuário
                document.getElementById("card-errors").textContent = error.message;
            } else {
                // Adicionar o token ao formulário e enviá-lo
                const hiddenInput = document.createElement("input");
                hiddenInput.setAttribute("type", "hidden");
                hiddenInput.setAttribute("name", "stripeToken");
                hiddenInput.setAttribute("value", token.id);
                form.appendChild(hiddenInput);
                
                form.submit();
            }
        });
    });
</script>
