{% set count = filterForm.count() %}
<div class="filter-form uk-margin uk-display-inline-block">
    {% if hasSearchBox %}
        <div class="uk-inline">
            <a class="uk-form-icon uk-form-icon-flip search-icon" uk-icon="icon: search"></a>
            <input class="uk-input" type="search" name="filters[search]" autocomplete="off"
                   placeholder="{{ _('search-hint') }}" value="{{ searchValue ? searchValue : '' }}"/>
        </div>
    {% endif %}

    <div class="uk-inline uk-position-z-index">
        <select class="uk-select no-choices" name="filters[limit]">
            <option value="5"{{ limit === 5 ? ' selected' : '' }}>5</option>
            <option value="15"{{ limit === 15 ? ' selected' : '' }}>15</option>
            <option value="25"{{ limit === 25 ? ' selected' : '' }}>25</option>
            <option value="35"{{ limit === 35 ? ' selected' : '' }}>35</option>
            <option value="45"{{ limit === 45 ? ' selected' : '' }}>45</option>
            <option value="55"{{ limit === 55 ? ' selected' : '' }}>55</option>
            <option value="65"{{ limit === 65 ? ' selected' : '' }}>65</option>
            <option value="75"{{ limit === 75 ? ' selected' : '' }}>75</option>
            <option value="85"{{ limit === 85 ? ' selected' : '' }}>85</option>
            <option value="95"{{ limit === 95 ? ' selected' : '' }}>95</option>
            <option value="100"{{ limit === 100 ? ' selected' : '' }}>100</option>
            <option value="200"{{ limit === 200 ? ' selected' : '' }}>200</option>
        </select>
    </div>

    {% if count < 1 AND hasSearchBox %}
        <div class="uk-inline">
            <ul class="uk-subnav uk-subnav-divider uk-margin-remove-bottom">
                <li>
                    <a class="uk-text-uppercase reset-filter" href="#">
                        <span uk-icon="icon: refresh"></span>
                        {{ _('reset-filter') }}
                    </a>
                </li>
            </ul>
        </div>
    {% endif %}

    {% if count %}
        <div class="uk-inline">
            <ul class="uk-subnav uk-subnav-divider uk-margin-remove-bottom">
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
