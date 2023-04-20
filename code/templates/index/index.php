{% extends 'layout/main.php' %}

{% block content %}

this is my content

{{ matches }}
<hr/>
{{ events }}
<hr/>

{% for e in events %}
    {{ e.name }}
    <ul>
        {% for m in e.matches %}
            <li>{{ m.pattern }}</li>
        {% endfor %}
    </ul>
{% endfor %}

{% endblock %}