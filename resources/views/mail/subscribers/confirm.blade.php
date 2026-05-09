<x-mail::message>
# Confirm your subscription

Thanks for subscribing to {{ config('app.name') }}. Click the button below to confirm your email address and start receiving new release notifications.

<x-mail::button :url="$verifyUrl">
Confirm subscription
</x-mail::button>

If you didn't subscribe, you can safely ignore this email.

Thanks,<br>
Andrew Krzynowek
</x-mail::message>
