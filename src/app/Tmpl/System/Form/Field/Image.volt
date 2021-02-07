{% set id = field.getId() %}
{% set multiple = field.get('multiple', false) ? 'true' : 'false' %}

<div class="media-element-container" id="{{ id }}-container" data-element-id="{{ id }}"
     data-multiple="{{ multiple }}">
    <a class="uk-button uk-button-text" href="#{{ id }}-modal">
        {% if ('true' === multiple) %}
            {{ _('choose-images') ~ ' ' ~ helper('IconSvg::render', 'picture-1') }}
        {% else %}
            {{ _('choose-image') ~ ' ' ~ helper('IconSvg::render', 'picture-1') }}
        {% endif %}
    </a>
    <div class="uk-grid-small uk-margin" id="{{ id }}-preview" uk-grid uk-sortable></div>
    <div class="uk-modal uk-modal-container" id="{{ id }}-modal">
        <div class="uk-modal-dialog">
            <button class="uk-modal-close-default" type="button" uk-close></button>
            <div class="uk-padding-small">
                <iframe class="uk-width-1-1 uk-height-large" id="{{ id }}-frame"
                        data-src="{{ helper('Uri::getInstance', ['uri': 'media/index', 'format': 'raw']) }}"></iframe>
            </div>
        </div>
    </div>
    <script type="template/media">
        <div class="col-image">
            <div class="uk-padding-small uk-background-muted uk-position-relative">
                <a class="uk-position-top-right uk-position-small uk-position-z-index uk-text-danger uk-background-default remove"
                   style="padding: 3px">
                    {{ helper('IconSvg::render', 'trash') }}
                </a>
                <div class="uk-position-relative" style="width: 120px; height: 120px">
                    <img class="uk-position-center" src="{src}"/>
                </div>
            </div>
        </div>
    </script>
    {{ input }}
</div>
