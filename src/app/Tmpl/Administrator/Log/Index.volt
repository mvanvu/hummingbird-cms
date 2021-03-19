{% set paginate = paginator.paginate() %}
<form id="admin-list-form" action="{{ uri.getActive() }}" method="post">
    {{ searchTools }}
    <table class="uk-table uk-table-small uk-table-striped uk-table-hover uk-table-middle uk-table-divider">
        <thead>
        <tr>
            <th class="uk-table-shrink uk-text-nowrap">
                <input class="uk-checkbox check-all" type="checkbox"/>
            </th>
            <th class="uk-table-expand uk-text-nowrap">
                {{ partial('Grid/Sort', ['text': _('content'), 'column': 'message', 'activeOrder' : activeOrder]) }}
            </th>
            <th class="uk-table-shrink uk-text-center uk-text-nowrap">
                {{ partial('Grid/Sort', ['text': _('author'), 'column': 'userId', 'activeOrder' : activeOrder]) }}
            </th>
            <th class="uk-table-shrink uk-text-center uk-text-nowrap">
                {{ partial('Grid/Sort', ['text': _('ip-address'), 'column': 'ip', 'activeOrder' : activeOrder]) }}
            </th>
            <th class="uk-table-shrink uk-text-nowrap uk-visible@s">
                {{ partial('Grid/Sort', ['text': _('created-at'), 'column': 'createdAt', 'activeOrder' : activeOrder]) }}
            </th>
        </tr>
        </thead>
        <tbody>
        {% for item in paginate.items %}
            <tr>
                <td>
                    <input class="uk-checkbox" type="checkbox" name="cid[]" value="{{ item.id }}"/>
                </td>
                <td>
                    <div title="{{ item.message | escape_attr }}" uk-tooltip>
                        {{ helper('StringHelper::truncate', item.message) }}
                    </div>
                </td>
                <td class="uk-text-nowrap uk-text-center">
                    {{ item.user ? item.user.name : 'N/A' }}
                </td>
                <td class="uk-text-nowrap uk-text-center">
                    {{ item.ip }}
                </td>
                <td class="uk-text-nowrap uk-visible@s">
                    {{ helper('Date::relative', item.createdAt) }}
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
