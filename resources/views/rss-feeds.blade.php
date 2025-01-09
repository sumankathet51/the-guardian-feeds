{!! '<'.'?xml version="1.0" encoding="UTF-8" ?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>The Guardian RSS Feed</title>
        <link>{{ route('rss-feed.show', request()->route('section')) }}</link>
        <atom:link href="{{ route('rss-feed.show', request()->route('section')) }}" rel="self" type="application/rss+xml" />
        <description>Latest updates from The Guardian</description>
        <language>en-us</language>
        <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>

        @foreach ($feedItems as $feedItem)
            <item>
                <title>{{ $feedItem['webTitle'] }}</title>
                <link>{{ $feedItem['webUrl'] }}</link>
                <description>Updates in {{ $feedItem['webTitle'] }}</description>
                <pubDate>{{ now()->toRfc2822String() }}</pubDate>
                <guid isPermaLink="false">{{ $feedItem['id'] }}</guid>
            </item>
        @endforeach
    </channel>
</rss>
