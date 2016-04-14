
    {% if init %}
        <div class="block">
    {% endif %}

        <div id="cal{{ id }}" ce="{{ onclick }}" cm="{{ onclickmsg|default('Loading ...') }}" ev="{{ url }}"></div>

    {% if init %}
        </div>
    {% endif %}