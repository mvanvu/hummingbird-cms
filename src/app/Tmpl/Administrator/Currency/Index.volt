{% set paginate = paginator.paginate() %}
<form id="admin-list-form" action="{{ uri.getActive() }}" method="post">
    {{ searchTools }}
    <table class="uk-table uk-table-small uk-table-striped uk-table-hover uk-table-middle uk-table-divider">
        <thead>
        <tr>
            <th class="uk-table-shrink uk-text-nowrap">
                <input class="uk-checkbox check-all" type="checkbox"/>
            </th>
            <th class="uk-table-shrink uk-text-nowrap">
                {{ partial('Grid/Sort', ['text': _('state'), 'column': 'state', 'activeOrder' : activeOrder]) }}
            </th>
            <th class="uk-table-expand uk-text-nowrap">
                {{ partial('Grid/Sort', ['text': _('name'), 'column': 'name', 'activeOrder' : activeOrder]) }}
            </th>
            <th class="uk-table-shrink uk-text-center uk-text-nowrap">
                {{ partial('Grid/Sort', ['text': _('currency-rate'), 'column': 'rate', 'activeOrder' : activeOrder]) }}
            </th>
            <th class="uk-table-shrink uk-text-center uk-text-nowrap">
                {{ partial('Grid/Sort', ['text': _('currency-code'), 'column': 'code', 'activeOrder' : activeOrder]) }}
            </th>
            <th class="uk-table-shrink uk-text-nowrap uk-visible@s">
                {{ partial('Grid/Sort', ['text': _('created-at'), 'column': 'createdAt', 'activeOrder' : activeOrder]) }}
            </th>
            <th class="uk-text-nowrap head-table-id uk-visible@s">
                {{ partial('Grid/Sort', ['text': _('ID'), 'column': 'id', 'activeOrder' : activeOrder]) }}
            </th>
        </tr>
        </thead>
        <tbody>
        {% for item in paginate.items %}
            <tr>
                <td>
                    <input class="uk-checkbox" type="checkbox" name="cid[]" value="{{ item.id }}"/>
                </td>
                <td class="uk-text-nowrap">
                    {{ partial('Grid/Status', ['item': item]) }}
                </td>
                <td>
                    {% if item.isCheckedIn() %}
                        {{ partial('Grid/CheckedIn', ['item': item, 'title': item.name]) }}
                    {% else %}
                        <a class="uk-link-reset" href="{{ uri.routeTo('edit/' ~ item.id) }}">
                            {{ item.name }}
                        </a>
                    {% endif %}
                    <span class="uk-text-meta">{{ item.format(1000) }}</span>
                </td>
                <td class="uk-text-nowrap uk-text-center">
                    {{ item.rate }}
                </td>
                <td class="uk-text-nowrap uk-text-center">
                    {{ item.code }}
                </td>
                <td class="uk-text-nowrap uk-visible@s">
                    {{ helper('Date::relative', item.createdAt) }}
                </td>
                <td class="uk-text-nowrap uk-visible@s">
                    {{ item.id }}
                </td>
            </tr>
        {% endfor %}
        </tbody>
    </table>
    {{ partial('Pagination/Pagination') }}
    <input type="hidden" name="postAction"/>
    <input type="hidden" name="entityId"/>
    {{ csrfInput() }}
</form>
