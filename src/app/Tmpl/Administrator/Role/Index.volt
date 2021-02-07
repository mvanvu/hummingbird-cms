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
                {{ partial('Grid/Sort', ['text': _('title'), 'column': 'name', 'activeOrder' : activeOrder]) }}
            </th>
            <th class="uk-table-shrink uk-text-nowrap">
                {{ partial('Grid/Sort', ['text': _('role-admin-login'), 'column': 'type', 'activeOrder' : activeOrder]) }}
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
                    {% if item.isProtected() AND !item.isCheckedIn() %}
                        {{ helper('IconSvg::render', 'lock') }}
                    {% else %}
                        <input class="uk-checkbox" type="checkbox" name="cid[]" value="{{ item.id }}"/>
                    {% endif %}
                </td>
                <td>
                    {% if item.isCheckedIn() %}
                        {{ partial('Grid/CheckedIn', ['item': item, 'title': item.name]) }}
                    {% else %}
                        <a class="uk-link-reset" href="{{ uri.routeTo('edit/' ~ item.id) }}">
                            {{ item.name }}
                        </a>
                    {% endif %}

                    {% if item.type === 'R' AND item.isProtected() %}
                        {{ helper('IconSvg::render', 'user') }}
                    {% endif %}

                    {% if item.description | length %}
                        <small class="uk-text-muted">{{ item.description }}</small>
                    {% endif %}
                </td>
                <td class="uk-text-nowrap uk-text-center">
                    {% if item.type == 'R' %}
                        <span class="uk-text-danger">
                        {{ _('no') ~ '<span uk-icon="close"></span>' }}
                    </span>
                    {% else %}
                        <span class="uk-text-success">
                            {{ _('yes') ~ '<span uk-icon="check"></span>' }}
                        </span>
                    {% endif %}
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
