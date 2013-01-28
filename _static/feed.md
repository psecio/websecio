<?xml version="1.0"?>
<rss version="2.0">
    <channel>
    <title>Websec.io - Web Application Security</title>
    <link>http://websec.io</link>
    <description>Latest Articles</description>
    <language>en-us</language>
    <pubDate>{{ pubDate }}</pubDate>
    <ttl>30</ttl>
    {% for link in links %}
    <item>
        <title>{{ link['title'] }}</title>
        <guid>http://websec.io{{ link['url'] }}</guid>
        <link>http://websec.io{{ link['url'] }}</link>
        <description>
            {{ link['summary'] }}
        </description>
        <pubDate>{{ link['pubdate'] }}</pubDate>
    </item>
    {% endfor %}
    </channel>
</rss>
