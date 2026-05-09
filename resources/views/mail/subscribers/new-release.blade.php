<x-mail::message>
# New release: {{ $release->title }}

@if ($release->artist)
**{{ $release->artist->name }}** just released **{{ $release->title }}**.
@else
**{{ $release->title }}** is out now.
@endif

@if ($release->description)
{{ $release->description }}
@endif

<x-mail::button :url="$releaseUrl">
Listen now
</x-mail::button>

Thanks,<br>
Andrew Krzynowek

---

You're receiving this because you subscribed at {{ config('app.url') }}. [Unsubscribe]({{ $unsubscribeUrl }}).
</x-mail::message>
