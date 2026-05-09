<?php

use App\Mail\ConfirmSubscriptionMail;
use App\Models\Subscriber;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    use WithRateLimiting;

    #[Validate(['required', 'email:rfc', 'max:255'])]
    public string $email = '';

    public bool $submitted = false;

    public function submit(): void
    {
        try {
            $this->rateLimit(5, 600);
        } catch (TooManyRequestsException $exception) {
            $this->addError('email', "Too many attempts. Try again in {$exception->minutesUntilAvailable} minutes.");

            return;
        }

        $validated = $this->validate();

        $subscriber = Subscriber::query()->firstOrCreate(
            ['email' => $validated['email']],
        );

        if (! $subscriber->isVerified()) {
            Mail::to($subscriber->email)->send(new ConfirmSubscriptionMail($subscriber));
        }

        $this->submitted = true;
        $this->reset('email');
    }
};
?>

<div class="w-full">
    @if ($submitted)
        <p class="text-sm text-[#1a1a1a]">
            Check your email to confirm your subscription.
        </p>
    @else
        <form wire:submit="submit" class="flex flex-col sm:flex-row gap-2 w-full">
            <label for="subscribe-email-{{ $this->getId() }}" class="sr-only">Email address</label>
            <input
                type="email"
                id="subscribe-email-{{ $this->getId() }}"
                wire:model="email"
                placeholder="you@example.com"
                required
                class="flex-1 border-2 border-[#1a1a1a] bg-transparent px-3 py-2 text-sm focus:outline-none focus:bg-white"
            >
            <button
                type="submit"
                class="cursor-pointer border-2 border-ink bg-ink text-white px-4 py-2 uppercase tracking-widest text-xs font-bold transition-colors hover:bg-accent hover:border-accent hover:text-ink disabled:opacity-50 disabled:cursor-wait"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="submit">Subscribe</span>
                <span wire:loading wire:target="submit">Sending…</span>
            </button>
        </form>
        @error('email')
            <p class="text-sm text-red-700 mt-2">{{ $message }}</p>
        @enderror
    @endif
</div>
