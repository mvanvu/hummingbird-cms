<div class="ucm-item-element-container" id="{{ id }}-container" data-ucm-item-element-id="{{ id }}">
    <a class="uk-button uk-button-text" href="#{{ id }}-modal" uk-toggle>
        {{ selectText }}
    </a>

    <ul class="uk-list uk-list-line" id="{{ id }}-list" uk-sortable>
        {% for item in items %}
            <li class="list-item" data-id="{{ item['id'] }}">
                <div class="uk-grid-small uk-flex-middle" uk-grid>
                    <div class="uk-width-expand uk-text-emphasis uk-text-truncate title">
                        {{ item['title'] }}
                    </div>
                    <div class="uk-width-auto">
                        <a class="uk-text-danger remove"
                           style="padding: 3px">
                            {{ helper('IconSvg::render', 'trash') }}
                        </a>
                    </div>
                </div>
            </li>
        {% endfor %}
    </ul>
    <div class="{{ modalClass }}" id="{{ id }}-modal" uk-modal>
        <div class="uk-modal-dialog">
            <button class="{{ modalClose }}" type="button" uk-close></button>
            <div class="uk-padding-small">
                <iframe class="uk-width-1-1 uk-height-large" id="{{ id }}-frame"
                        data-src="{{ helper('Uri::getInstance', ['uri': 'content/' ~ context ~ '/index', 'format': 'raw']) }}"></iframe>
            </div>
        </div>
    </div>
    <script type="template/ucm-item">
        <li class="list-item">
            <div class="uk-grid-small uk-flex-middle" uk-grid>
                <div class="uk-width-expand uk-text-emphasis uk-text-truncate title"></div>
                <div class="uk-width-auto">
                    <a class="uk-text-danger remove"
                       style="padding: 3px">
                        {{ helper('IconSvg::render', 'trash') }}
                    </a>
                </div>
            </div>
        </li>
    </script>
    {{ input }}
</div>
