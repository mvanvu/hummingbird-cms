{% set paginate = paginator.paginate() %}
<form id="admin-list-form" action="{{ uri.getActive() }}" method="post">
    {{ searchTools }}
    <table class="uk-table uk-table-small uk-table-hover uk-table-middle uk-table-divider">
        <thead>
        <tr>
            <th class="uk-table-shrink uk-text-nowrap">
                <input class="uk-checkbox check-all" type="checkbox"/>
            </th>
            <th class="uk-width-expand uk-text-nowrap">
                {{ partial('Grid/Sort', ['text': _('title'), 'column': 'title', 'activeOrder' : activeOrder]) }}
            </th>
            <th class="uk-table-shrink uk-text-nowrap uk-visible@m">
                {{ partial('Grid/Sort', ['text': _('created-at'), 'column': 'createdAt', 'activeOrder' : activeOrder]) }}
            </th>
            <th class="uk-text-nowrap uk-table-shrink uk-visible@m">
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
                <td>
                    {% if item.isCheckedIn() %}
                        {{ partial('Grid/CheckedIn', ['item': item, 'title': item.title]) }}
                    {% else %}
                        <a class="uk-link-reset" href="{{ uri.routeTo('edit/' ~ item.id) }}">
                            {{ item.title }}
                        </a>
                    {% endif %}

                    <small>{{ item.slug }}</small>
                </td>
                <td class="uk-text-nowrap uk-visible@m">
                    {{ helper('Date::relative', item.createdAt) }}
                </td>
                <td class="uk-text-nowrap uk-visible@m">
                    {{ item.id }}
                </td>
            </tr>
        {% endfor %}
        </tbody>
    </table>
    {{ partial('Pagination/Pagination') }}
    <input type="hidden" name="postAction"/>
    <input type="hidden" name="entityId"/>
    {{ helper('Form::tokenInput') }}
</form>
