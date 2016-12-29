
    {% if init %}
        <div class="block">
    {% endif %}

        <div id="cal{{ id }}" ce="{{ onclick }}" lo="1" cm="{{ onclickmsg|default('Loading ...') }}" ev="{{ url }}"></div>

    {% if init %}
        </div>
    {% endif %}