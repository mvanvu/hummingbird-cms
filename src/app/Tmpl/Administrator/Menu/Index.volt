<div id="menus-container" data-token="{{ helper('Form::getToken') }}">
    <div class="uk-background-muted uk-padding-small uk-margin-medium">
        <div class="uk-background-default uk-padding-small">
            <div class="uk-grid-small uk-child-width-1-2@s" uk-grid>
                <div>
                    <div class="uk-grid-small uk-flex-middle" uk-grid>
                        <div class="uk-width-medium@s">
                            <select class="uk-select uk-form-small not-chosen menu-type-select">
                                {% if (menuTypes) %}
                                    {% for menu in menuTypes %}
                                        <option value="{{ menu['data'] | escape_attr }}"{{ menuType === menu['data'] ? ' selected' : '' }}>
                                            {{ menu['data'] | escape }}
                                        </option>
                                    {% endfor %}
                                {% endif %}
                            </select>
                        </div>
                        <div class="uk-width-auto@s">
                            <button class="uk-button uk-button-text uk-text-meta" id="menu-type-create" type="button">
                                {{ _('create-new-menu-types') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="uk-grid-small uk-flex-middle" uk-grid>
                        <div class="uk-width-medium@s">
                            <select class="uk-select uk-form-small not-chosen item-type-select">
                                {% for type, formData in registeredMenus %}
                                    <option value="{{ type | escape_attr }}">
                                        {{ _('menu-item-type-' ~ type) | escape }}
                                    </option>
                                {% endfor %}
                            </select>
                        </div>
                        <div class="uk-width-auto@s">
                            <button class="uk-button uk-button-text uk-text-meta" id="item-type-create" type="button">
                                {{ _('create-menu-item') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="uk-grid-small uk-child-width-1-2@s" uk-grid>
        <div>
            <div class="uk-margin menu-item-list">
                {% if menuType is not empty %}
                    {{ partial('Menu/AdminList', ['menuType': menuType]) }}
                {% endif %}
            </div>
        </div>
        <div>
            <div class="uk-hidden" id="item-body">
                <div class="uk-card uk-card-small uk-card-body uk-background-muted">
                    <iframe class="uk-width-1-1 uk-height-large"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
