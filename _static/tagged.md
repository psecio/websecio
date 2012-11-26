{% for link in links %}
{% if link['tags'] %}
{% for tag in link['tags'] %}
{{ tag }}|{{ link['title']}}|{{ link['url'] }}
{% endfor %}
{% endif %}
{% endfor %}