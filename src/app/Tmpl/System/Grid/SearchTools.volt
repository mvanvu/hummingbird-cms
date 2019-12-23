{% set count = filterForm.count() %}
<div class="filter-form uk-margin uk-display-inline-block">
    {% if hasSearchBox %}
        <div class="uk-inline">
            <a class="uk-form-icon uk-form-icon-flip search-icon" uk-icon="icon: search"></a>
            <input class="uk-input" type="search" name="FilterForm[search]" autocomplete="off"
                   placeholder="{{ _('search-hint') }}" value="{{ searchValue ? searchValue : '' }}"/>
        </div>

        {% if count < 1 %}
            <div class="uk-inline">
                <ul class="uk-subnav uk-subnav-divider uk-margin-remove">
                    <li>
                        <a class="uk-text-uppercase reset-filter" href="#">
                            <span uk-icon="icon: refresh"></span>
                            {{ _('reset-filter') }}
                        </a>
                    </li>
                </ul>
            </div>
        {% endif %}
    {% endif %}

    {% if count %}
        <div class="uk-inline">
            <ul class="uk-subnav uk-subnav-divider uk-margin-remove">
                <li>
                    <a class="{{ activeFilter ? 'uk-text-primary ' : '' }}uk-text-uppercase" href="#">
                        <span uk-icon="icon: more-vertical"></span>
                        {{ _('filter') }}
                    </a>
                    <div class="uk-drop uk-position-z-index"
                         uk-drop="mode: click; pos: bottom-justify; offset: 15; boundary: .filter-form; boundary-align: true">
                        <div class="uk-card uk-card-default uk-card-body uk-card-small" uk-margin>
                            {% for field in filterForm.getFields() %}
                                <div>{{ field.toString() }}</div>
                            {% endfor %}
                        </div>
                    </div>
                </li>
                <li>
                    <a class="uk-text-uppercase reset-filter" href="#">
                        <span uk-icon="icon: refresh"></span>
                        {{ _('reset-filter') }}
                    </a>
                </li>
            </ul>
        </div>
    {% endif %}
</div>
