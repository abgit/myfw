
    {% if init %}
        <div class="block">
    {% endif %}

        <div id="cal{{ id }}" ce="{{ onclick }}" cm="{{ onclickmsg|default('Loading ...') }}" ev="{{ values|default('[]') }}"></div>

    {% if init %}
        </div>
    {% endif %}