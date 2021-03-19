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
                {{ partial('Grid/Sort', ['text': _('lang-code'), 'column': 'code', 'activeOrder' : activeOrder]) }}
            </th>
            <th class="uk-table-shrink uk-text-center uk-text-nowrap">
                {{ partial('Grid/Sort', ['text': _('lang-iso'), 'column': 'iso', 'activeOrder' : activeOrder]) }}
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
                    {% if item.yes('protected') %}
                        <div class="item-status uk-flex uk-flex-nowrap">
                            <a class="p uk-disabled active" uk-icon="icon: check"></a>
                            <a class="uk-disabled uk-text-muted" uk-icon="icon: close"></a>
                            <a class="uk-disabled uk-text-muted" uk-icon="icon: trash"></a>
                        </div>
                    {% else %}
                        {{ partial('Grid/Status', ['item': item]) }}
                    {% endif %}
                </td>
                <td>
                    {{ helper('Utility::getCountryFlagEmoji', isoCodes[item.iso]) }}
                    {% if item.isCheckedIn() %}
                        {{ partial('Grid/CheckedIn', ['item': item, 'title': item.name]) }}
                    {% else %}
                        <a class="uk-link-reset" href="{{ uri.routeTo('edit/' ~ item.id) }}">
                            {{ item.name }}
                        </a>
                    {% endif %}
                </td>
                <td class="uk-text-nowrap uk-text-center">
                    {{ item.code }}
                </td>
                <td class="uk-text-nowrap uk-text-center">
                    {{ item.iso }}
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
    {{ csrfInput() }}
</form>
