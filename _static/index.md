---
layout: default
title: web application security
---
<h2>Latest Articles</h2>
<br />

{% for link in links %}
    <span class="post_date">@{{ link['posted'] }}</span>&nbsp;&nbsp;
    <a class="post_title" href="{{ link['url'] }}">{{ link['title'] }}</a><br />
    {% if link['summary'] %} 
    <span class="summary">{{ link['summary'] }}</span><br />
    {% endif %}
    <span class="author">by {{ link['author'] }}</span>
    {% if link['tags'] %}
    <span class="tagline">
        {% for tag in link['tags'] %}
            <a style="font-size:11px" href="/tagged/{{ tag }}">#{{ tag }}</a>
        {% endfor %}
    </span>
    {% endif %}
    <br /><br />

{% endfor %}