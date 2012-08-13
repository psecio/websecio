---
layout: default
---
<h2>Latest Articles</h2>
<br />
{% for link in links %}
    <span class="post_date">@{{ link['posted'] }}</span>&nbsp;
    <a class="post_title" href="{{ link['url'] }}">{{ link['title'] }}</a>
    <br /><br />
{% endfor %}