{% set paginate = paginator.paginate() %}
{% if 'raw' === dispatcher.getParam('format') %}
    {% set isMultilingual = false %}
{% endif %}
<form id="admin-list-form" action="{{ uri.getActive() }}" method="post">
    {{ searchTools }}
    {% if paginate.getTotalItems() %}
        <div class="uk-overflow-auto">
            <table class="uk-table uk-table-small uk-table-striped uk-table-hover uk-table-middle uk-table-divider uk-table-striped"
                   data-sort-handle="content/{{ model.context }}/reorder">
                <thead>
                <tr>
                    <th class="uk-table-shrink uk-visible@s">
                        <input class="uk-checkbox check-all" type="checkbox"/>
                    </th>
                    <th class="uk-table-shrink uk-text-nowrap">
                        {{ partial('Grid/Sort', ['text': _('state'), 'column': 'state', 'activeOrder' : activeOrder]) }}
                    </th>
                    <th class="uk-table-expand uk-text-nowrap">
                        {{ partial('Grid/Sort', ['text': _('title'), 'column': 'title', 'activeOrder' : activeOrder]) }}
                    </th>

                    {{ trigger('onAfterUcmHeadTitle') | j2nl }}

                    <th class="uk-table-shrink uk-text-nowrap">
                        {{ partial('Grid/Sort', ['text': _('category'), 'column': 'parentId', 'activeOrder' : activeOrder]) }}
                    </th>

                    <th class="uk-table-shrink uk-text-nowrap">
                        {{ partial('Grid/Sort', ['text': _('created-at'), 'column': 'createdAt', 'activeOrder' : activeOrder]) }}
                    </th>

                    <th class="head-table-id uk-text-nowrap uk-visible@m">
                        {{ partial('Grid/Sort', ['text': _('ID'), 'column': 'id', 'activeOrder' : activeOrder]) }}
                    </th>

                    <th class="uk-text-nowrap uk-table-shrink" style="min-width: 35px">
                        {{ partial('Grid/Sort', ['text': icon('sort-alpha-asc'), 'column': 'ordering', 'activeOrder': activeOrder]) }}
                    </th>
                </tr>
                </thead>
                <tbody>
                {% set isOrderingActive = strpos(activeOrder, 'ordering ') === 0 %}
                {% for item in paginate.getItems() %}
                    {% set title = item.title %}
                    <tr data-sort-id="{{ item.id }}"
                        data-id="{{ item.id }}"
                        data-sort-group="{{ item.parentId }}"
                        data-title="{{ title | escape_attr }}"
                        data-ordering="{{ item.ordering }}">
                        <td class="uk-visible@s">
                            <input class="uk-checkbox cid" type="checkbox" name="cid[]"
                                   value="{{ item.id }}"/>
                        </td>
                        <td class="uk-text-nowrap">
                            {{ partial('Grid/Status', ['item': item]) }}
                        </td>
                        <td class="ucm-item-title uk-text-small">
                            {% if item.isCheckedIn() %}
                                {{ partial('Grid/CheckedIn', ['item': item, 'title': title]) }}
                                {% if item.route %}
                                    <a class="uk-link-muted"
                                       href="{{ helper('Uri::getInstance', ['uri': item.route, 'client': 'site']) }}"
                                       target="_blank"
                                       uk-icon="icon: link"></a>
                                {% endif %}
                            {% else %}
                                <a class="uk-link-reset" href="{{ uri.routeTo('edit/' ~ item.id) }}">
                                    {{ title }}
                                </a>
                                {% if item.route %}
                                    <a class="uk-link-muted"
                                       href="{{ helper('Uri::getInstance', ['uri': item.route, 'client': 'site']) }}"
                                       target="_blank"
                                       uk-icon="icon: link"></a>
                                {% endif %}
                            {% endif %}
                        </td>

                        {{ trigger('onAfterUcmBodyTitle', [item]) | j2nl }}

                        <td class="uk-text-nowrap uk-text-meta">
                            {{ item.category ? item.category.title : '' }}
                        </td>

                        <td class="uk-text-nowrap uk-text-meta">
                            {{ helper('Date::relative', item.createdAt) }}
                        </td>

                        <td class="uk-text-nowrap uk-text-meta uk-visible@m">
                            {{ item.id }}
                        </td>

                        <td class="uk-text-center uk-text-nowrap">
                            {% if isOrderingActive %}
                                <input class="uk-input" name="ordering" type="number" min="1"
                                       value="{{ item.ordering }}" style="width: 65px"/>
                            {% else %}
                                <div class="uk-text-small uk-text-muted">
                                    {{ item.ordering }}
                                </div>
                            {% endif %}
                        </td>
                    </tr>
                {% endfor %}
                </tbody>
            </table>
        </div>
    {% else %}
        <div class="uk-alert uk-alert-warning">
            {{ _('no-items-found') }}
        </div>
    {% endif %}
    {{ partial('Pagination/Pagination') }}
    {{ csrfInput() }}
</form>
